<?php

declare(strict_types=1);

namespace App\Kernel\Application\Driver;

use App\Transcode\Domain\Enum\Format;
use App\Transcode\Domain\Model\Transcode;
use App\Transcode\Domain\Model\VideoProperty;
use App\Transcode\Domain\Repository\TranscodeRepository;
use FFMpeg\FFMpeg;
use Streaming\FFMpeg as SFFMpeg;
use Streaming\Format\HEVC;
use Streaming\Format\StreamFormat;
use Streaming\Format\VP9;
use Streaming\Format\X264;
use Streaming\Representation;

final readonly class FfmpegDriver
{
    public function transcode(Transcode $transcode): void
    {
//        $config = [
//            'ffmpeg.binaries' => '/usr/bin/ffmpeg',
//            'ffprobe.binaries' => '/usr/bin/ffprobe',
//            'timeout' => 0,
//            'ffmpeg.threads' => 12, //TODO: make configurable in admin system settings
//        ];
//
//        $ffmpeg = FFMpeg::create($config);
//        $sFfmpeg = new SFFMpeg($ffmpeg);
//        $video = $sFfmpeg->open($transcode->getFilePath());
//
//        $saveLocation = $_ENV['TRANSCODE_PATH'] . '/' . $transcode->getRandSubTargetPath() . '/' . $_ENV['STREAM_FILENAME'];
//
//        $format = $this->getFormat(Format::x264->value);
//        $format->on('progress', function ($video, $format, $percentage) use ($transcode) {
//            $percentage = (int) round($percentage);
//            $transcode->setTranscodingProgress($percentage);
//            $this->transcodeRepository->save($transcode);
//        });
//
//        $representations = $this->getRepresentations($transcode);

        //TODO: create ffmpeg driver
        //$inputFile = escapeshellarg($transcode->getFilePath());
        //$masterName = $_ENV['STREAM_FILENAME'] . '.m3u8';
        //$saveLocation1 = $saveLocation . '_%v_1080p.m3u8';
        //$saveLocation2 = $saveLocation . '_%v_1080p_%04d.ts';
        //$saveLocation = escapeshellarg($saveLocation);
        //$command = "ffmpeg -y -i $inputFile -c:v libx264 -c:a mp3 -keyint_min 25 -g 250 -sc_threshold 40 -hls_list_size 0 -hls_time 10 -hls_allow_cache 1 -hls_segment_type mpegts -hls_fmp4_init_filename stream_%v_1080p_init.mp4 -hls_segment_filename $saveLocation2 -master_pl_name $masterName -s:v:0 1920x1080 -b:v:0 4096k -f hls -strict -2 -threads 12 $saveLocation1";
        //$command = "ffmpeg -i input_file.mp4 -c:v libx264 -preset slow -crf 22 -c:a copy $saveLocation >/dev/null 2>&1 & echo $!";
        //shell_exec('mkdir ' . $_ENV['TRANSCODE_PATH'] . '/' . $transcode->getRandSubTargetPath());
        //$output = shell_exec($command);
        //dump($output);
        //$pid = (int)$output;
        //
        //do {
        //    $status = shell_exec("ps aux | grep $pid");
        //    $status = explode("\n", $status);
        //} while(count($status) > 2);
    }

    private function getRepresentations(Transcode $transcode): array
    {
        $representations = [];
        foreach ($transcode->getRepresentation() as $representation) {
            $representations[] = (new Representation)->setKiloBitrate($representation->getKiloBiteRate())
                ->setResize($representation->getResolutionWidth(), $representation->getResolutionHeight());
        }

        return $representations;
    }

    private function getFormat(string $format): StreamFormat
    {
        //The default option is libmp3lame since the majority, if not all, VRChat video players are only compatible with mp3 files.
        $defaultAudioCodec = 'libmp3lame';

        return match ($format) {
            Format::HEVC->value => new HEVC('libx265', $defaultAudioCodec),
            Format::VP9->value => new VP9('libvpx-vp9', $defaultAudioCodec),
            default => new X264('libx264', $defaultAudioCodec),
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
