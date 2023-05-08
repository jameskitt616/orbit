<?php

declare(strict_types=1);

namespace App\Transcode\Application\Service;

use App\Transcode\Domain\Enum\Format;
use App\Transcode\Domain\Model\File;
use App\Transcode\Domain\Model\Transcode;
use App\Transcode\Domain\Model\VideoProperty;
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
        //TODO: expose config? also check if it has any impact
        $config = [
            'ffmpeg.binaries' => '/usr/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/bin/ffprobe',
            'timeout' => 18000, //TODO: probably increase timeout to max length of movie. default 5h?
            'ffmpeg.threads' => 12, //TODO: maybe configure in admin system settings? -> not sure if this has any impact
        ];

        //TODO: throw exceptions on issues like file does not exist anymore
        //TODO: or try/catch so can display errors

        $ffmpeg = FFMpeg::create($config);
        $sFfmpeg = new SFFMpeg($ffmpeg);
        //        $video = $sFfmpeg->openAdvanced([$transcode->getFilePath()]);
        $video = $sFfmpeg->open($transcode->getFilePath());

        $saveLocation = $_ENV['TRANSCODE_PATH'] . '/' . $transcode->getRandSubTargetPath() . '/' . $_ENV['STREAM_FILENAME'];

        $format = $this->getFormat(Format::HEVC->value);
        $format->on('progress', function ($video, $format, $percentage) {
            $percentage = (int) round($percentage);
            //            dump($transcode);
            //dump(sprintf("\rTranscoding...(%s%%) [%s%s]", $percentage, str_repeat('#', $percentage), str_repeat('-', (100 - $percentage))));
        });

        $representations = $this->getRepresentations($transcode);

        //        shell_exec('screen -dmS $name_of_screen $command');

        //        $cmd = 'cp /orbit/videos/beat.mkv /orbit/videos/beat2.mkv';
        //        $outputfile = '/asd';

        //        exec(sprintf("%s > %s 2>&1 & echo $! >> %s", $cmd, $outputfile, $pidfile));
        //        exec(sprintf("%s > %s 2>&1 & echo $!", $cmd, $outputfile),$pidArr);
        //        dump($pidArr);

        //        $sFfmpeg->getFFProbe()->getMapper()

        //        SFFMpeg::create()
        //        $streams = FFMpeg::create()
        //            ->open($transcode->getFilePath());

        //        dump($streams->getStreams()->all());
        //        dump($streams->getFFProbe());
        //        $general = $streams->general();
        //        $video = $streams->videos()->first();
        //        $audio = $streams->audios()->first();

        //        $ffprobe = FFProbe::create();
        //        dump($ffprobe
        //            ->format($transcode->getFilePath())
        //            ->all());

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
        return match ($format) {
            Format::HEVC->value => new HEVC(),
            Format::VP9->value => new VP9(),
            default => new X264(),
        };
    }

    public function getAvailableTracksByFilePathAndVideoProperty(string $filePath, string $videoProperty): array
    {
        $filePath = escapeshellarg($filePath);
        $output = shell_exec("ffmpeg -i $filePath 2>&1");
        $lines = explode("\n", $output);
        $streams = [];

        foreach ($lines as $line) {
            if (str_contains($line, "$videoProperty:")) {
                preg_match('/\((\w+)\)/', $line, $matches);
                $languageCode = strtoupper($matches[1]);
                $attributes = explode("$videoProperty: ", $line)[1];
                $streamName = $languageCode . ' - ' . $attributes;
                preg_match('/Stream #\d+:(\d+)/', $line, $matches);
                $streamNumber = $matches[1];
                $streams[] = new VideoProperty($streamNumber, $streamName);
            }
        }

        return $streams;
    }
}
