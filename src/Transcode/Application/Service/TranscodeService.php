<?php

declare(strict_types=1);

namespace App\Transcode\Application\Service;

use App\Transcode\Domain\Enum\Format;
use App\Transcode\Domain\Model\Transcode;
use App\Transcode\Domain\Model\VideoProperty;
use App\Transcode\Domain\Repository\TranscodeRepository;
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

    public function hlsTranscode(Transcode $transcode): void
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
        $saveLocation = $_ENV['TRANSCODE_PATH'] . '/' . $transcode->getRandSubTargetPath() . '/' . $_ENV['STREAM_FILENAME'];

        //        $format = $this->getFormat(Format::x264->value);
        //        $format->on('progress', function ($video, $format, $percentage) use ($transcode) {
        //            $percentage = (int) round($percentage);
        //            $transcode->setTranscodingProgress($percentage);
        //            $this->transcodeRepository->save($transcode);
        //        });

        //        $representations = $this->getRepresentations($transcode);

        //        $video->hls()
        //            ->setFormat($format)
        //            ->addRepresentations($representations)
        //            ->save($saveLocation);

        $cpuThreads = '4';
        $defaultAudioCodec = 'libmp3lame';
        //$defaultAudioCodec = 'mp3';

        $videoCodec = $this->getVideoCodec($transcode->getTranscodeFormat());

        //TODO: create ffmpeg driver for this hls logic
        $inputFile = escapeshellarg($transcode->getFilePath());
        $indexFileName = escapeshellarg($_ENV['STREAM_FILENAME'] . '.m3u8');
        //TODO: replace resolution with input representations
        $m3u8IndexFileLocation = escapeshellarg($saveLocation . '_1080p.m3u8');
        $tsFileLocation = escapeshellarg($saveLocation . '_1080p_%04d.ts');
        $hlsMp4InitName = escapeshellarg($saveLocation . '_1080p_init.mp4');
        //$hlsMp4InitName = escapeshellarg('stream_%v_1080p_init.mp4');

        //https://stackoverflow.com/a/75453664 -> try avi container?
        $command = "ffmpeg -y -i $inputFile -c:v $videoCodec -c:a $defaultAudioCodec -keyint_min 25 -g 250 -sc_threshold 40 -hls_list_size 0 -hls_time 10 -hls_allow_cache 1 -hls_segment_type mpegts -hls_fmp4_init_filename $hlsMp4InitName -hls_segment_filename $tsFileLocation -master_pl_name $indexFileName -s:v:0 1920x1080 -b:v:0 4096k -f hls -strict -2 -threads $cpuThreads $m3u8IndexFileLocation";
        shell_exec('mkdir ' . $_ENV['TRANSCODE_PATH'] . '/' . $transcode->getRandSubTargetPath());

        $this->executeCommand($command);
    }

    private function executeCommand($command): void
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes);

        if (is_resource($process)) {
            fclose($pipes[0]);

            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);

            while (true) {
                $output = stream_get_contents($pipes[1]);
                $error = stream_get_contents($pipes[2]);

                dump($pipes, $output, $error);

                if (feof($pipes[1]) && feof($pipes[2])) {
                    break;
                }

                if (!empty($output)) {
                    //                    echo $output;
                }

                if (!empty($error)) {
                    //                    echo $error;
                }

                //TODO: set transcoding process by getting total video length and using transcoded length
                //TODO: set transcoding speed e.g. speed=1.62x
                //$percentage = (int) round($percentage);
                //$transcode->setTranscodingProgress($percentage);
                //$this->transcodeRepository->save($transcode);

                usleep(100000);
            }

            fclose($pipes[1]);
            fclose($pipes[2]);

            // Process the exit code if needed
            //$exitCode = proc_close($process);
        }
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

    private function getVideoCodec(string $format): string
    {
        return match ($format) {
            Format::HEVC->value => 'libx265',
            Format::VP9->value => 'libvpx-vp9',
            default => 'libx264',
        };
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
