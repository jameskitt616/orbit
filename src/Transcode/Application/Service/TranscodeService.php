<?php

declare(strict_types=1);

namespace App\Transcode\Application\Service;

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

final readonly class TranscodeService
{
    public function __construct(
        private TranscodeRepository $transcodeRepository,
    )
    {
    }

    public function loadSourceFiles($directory): array
    {
        $files = [];
        $items = scandir($directory);
        //$videoExtensions = ['mp4', 'mov', 'avi', 'wmv', 'mkv', 'flv', 'webm'];

        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $files[] = [
                    'id' => uniqid(),
                    'text' => " $item",
                    'type' => 'folder',
                    'children' => $this->loadSourceFiles($path),
                    'icon' => 'fas fa-folder-open',
                    'li_attr' => [
                        'class' => 'bg-indigo-700 hover:bg-indigo-600 rounded cursor-pointer my-1 p-1 pl-2',
                    ],
                    'a_attr' => [
                        'class' => 'text-white',
                    ],
                ];
            } else {
                //$extension = pathinfo($item, PATHINFO_EXTENSION);
                //$isVideo = in_array(strtolower($extension), $videoExtensions);
                $files[] = [
                    'id' => uniqid(),
                    'text' => " $item",
                    'icon' => 'fas fa-file',
                    'data' => [
                        'file_path' => "$path",
                    ],
                    'li_attr' => [
                        'class' => 'selectableFile bg-indigo-500 hover:bg-indigo-400 rounded cursor-pointer my-1 p-1 pl-2',
                    ],
                    'a_attr' => [
                        'class' => 'text-white',
                    ],
                ];
            }
        }

        return $files;
    }

    public function transcode(Transcode $transcode): void
    {
        $config = [
            'ffmpeg.binaries' => '/usr/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/bin/ffprobe',
            'timeout' => 0,
            'ffmpeg.threads' => 7, //TODO: maybe configure in admin system settings? -> not sure if this has any impact
        ];

        $ffmpeg = FFMpeg::create($config);
        $sFfmpeg = new SFFMpeg($ffmpeg);
        $video = $sFfmpeg->open($transcode->getFilePath());

        $saveLocation = $_ENV['TRANSCODE_PATH'] . '/' . $transcode->getRandSubTargetPath() . '/' . $_ENV['STREAM_FILENAME'];

        $format = $this->getFormat(Format::x264->value);
        $format->on('progress', function ($video, $format, $percentage) use ($transcode) {
            $percentage = (int) round($percentage);
            $transcode->setTranscodingProgress($percentage);
            $this->transcodeRepository->save($transcode);
        });

        $representations = $this->getRepresentations($transcode);

        $video->hls()
            ->setFormat($format)
            ->addRepresentations($representations)
            ->save($saveLocation);

        //$inputFile = escapeshellarg($transcode->getFilePath());
        //$saveLocation1 = $saveLocation . '_%v_1080p.m3u8';
        //$saveLocation2 = $saveLocation . '_%v_1080p_%04d.ts';
        //$saveLocation = escapeshellarg($saveLocation);
        //$command = "ffmpeg -y -i $inputFile -c:v libx264 -c:a mp3 -keyint_min 25 -g 250 -sc_threshold 40 -hls_list_size 0 -hls_time 10 -hls_allow_cache 1 -hls_segment_type mpegts -hls_fmp4_init_filename stream_%v_1080p_init.mp4 -hls_segment_filename $saveLocation2 -master_pl_name master.m3u8 -s:v:0 1920x1080 -b:v:0 4096k -f hls -strict -2 -threads 12 $saveLocation1";
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
        foreach ($transcode->getRepresentations() as $representation) {
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
