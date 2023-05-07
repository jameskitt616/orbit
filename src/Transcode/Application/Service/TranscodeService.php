<?php

declare(strict_types=1);

namespace App\Transcode\Application\Service;

use App\Transcode\Domain\Enum\Format;
use App\Transcode\Domain\Model\File;
use App\Transcode\Domain\Model\Transcode;
use FFMpeg\FFMpeg;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Streaming\FFMpeg as SFFMpeg;
use Streaming\Format\HEVC;
use Streaming\Format\StreamFormat;
use Streaming\Format\VP9;
use Streaming\Format\X264;
use Streaming\Representation;

final class TranscodeService
{
    public function listAvailableVideos(): array
    {
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($_ENV['VIDEO_PATH']));
        $files = [];

        /** @var SplFileInfo $file */
        foreach ($rii as $file) {
            //TODO: add additional checks if file is not a video source
            if ($file->isDir()) {
                continue;
            }

            $files[] = new File($file->getFilename(), $file->getPathname(), $file->getSize());
        }

        return $files;
    }

    public function transcode(Transcode $transcode): void
    {
        //TODO: expose config
        $config = [
            'ffmpeg.binaries' => '/usr/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/bin/ffprobe',
            'timeout' => 18000, //TODO: probably increase timeout to max length of movie. default 5h?
            'ffmpeg.threads' => 12, //TODO: maybe configure in admin system settings? -> not sure if this has any impact
        ];

        $ffmpeg = FFMpeg::create($config);
        $sFfmpeg = new SFFMpeg($ffmpeg);
        $video = $sFfmpeg->open($transcode->getFilePath());

        $saveLocation = $_ENV['TRANSCODE_PATH'] . '/' . $transcode->getRandSubTargetPath() . '/' . $_ENV['STREAM_FILENAME'];

        $format = $this->getFormat(Format::HEVC->value);
        $format->on('progress', function ($video, $format, $percentage, $transcode) {
            $percentage = (int) round($percentage);
//            dump($transcode);
            //dump(sprintf("\rTranscoding...(%s%%) [%s%s]", $percentage, str_repeat('#', $percentage), str_repeat('-', (100 - $percentage))));
        });

        $representations = $this->getRepresentations($transcode);

        $video->hls()
            ->setFormat($format)
            ->addRepresentations($representations)
            ->save($saveLocation);
    }

    private function getRepresentations(Transcode $transcode): array
    {
        $representations = [];
        foreach ($transcode->getRepresentations() as $representation) {
            $representations[] = (new Representation)->setKiloBitrate($representation->getKiloBiteRate())
                ->setResize($representation->getResolutionWidth(), $representation->getResolutionHeight());
        }

        return $representations;
    }

    private function getFormat(string $format): StreamFormat
    {
        switch ($format) {
            case Format::HEVC->value:
                return new HEVC();
            case Format::VP9->value:
                return new VP9();
            default:
                return new X264();
        }
    }
}
