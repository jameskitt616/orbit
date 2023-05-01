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
        //TODO: expose config?
        $config = [
            'ffmpeg.binaries' => '/usr/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/bin/ffprobe',
            'timeout' => 3600, //TODO: probably increase timeout to max length of movie. 5h?
            'ffmpeg.threads' => 4, //TODO: maybe configure in admin system settings?
        ];

        $ffmpeg = FFMpeg::create($config);
        $sFfmpeg = new SFFMpeg($ffmpeg);
        //        $video = $sFfmpeg->open('/video_data/files/beat.mkv');
        $video = $sFfmpeg->open($transcode->getFilePath());
        //        $video = $sFfmpeg->open($_ENV['VIDEO_PATH'] . '/Backup.mp4');
        //        $video = $sFfmpeg->open('/video_data/files/biscuits.mp4');

        //        $loadedFileName = substr(strrchr($video->baseMedia()->getPathfile(), '/'),1);
        $fileName = 'stream';
        $saveLocation = $_ENV['TRANSCODE_PATH'] . '/' . $transcode->getRandSubTargetPath() . '/' . $fileName;
        //        dump($saveLocation);

        $r_720p = (new Representation)->setKiloBitrate(2048)->setResize(1280, 720);
        $r_1080p = (new Representation)->setKiloBitrate(4096)->setResize(1920, 1080);
        $r_4k = (new Representation)->setKiloBitrate(17408)->setResize(3840, 2160);

        dump(Format::HEVC->value);
        $format = $this->getFormat(Format::HEVC->value);
        $format->on('progress', function ($video, $format, $percentage, $transcode) {
            $percentage = (int) round($percentage);
            //            dump($percentage);
            //            dump(sprintf("\rTranscoding...(%s%%) [%s%s]", $percentage, str_repeat('#', $percentage), str_repeat('-', (100 - $percentage))));
        });

        $representations = $this->getRepresentations($transcode);

        $video->hls()
            ->setFormat($format)
            ->addRepresentations([$r_1080p])
            //->addRepresentations($representations)
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
