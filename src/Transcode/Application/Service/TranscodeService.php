<?php

declare(strict_types=1);

namespace App\Transcode\Application\Service;

use App\Transcode\Domain\Enum\Format;
use App\Transcode\Domain\Model\Representation;
use App\Transcode\Domain\Model\Transcode;
use App\Transcode\Domain\Model\VideoProperty;
use App\Transcode\Domain\Repository\TranscodeRepository;
use DateTime;
use Streaming\Format\HEVC;
use Streaming\Format\StreamFormat;
use Streaming\Format\VP9;
use Streaming\Format\X264;

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

    //TODO: refactor and cleanup this function after getting it working properly
    public function transcode(Transcode $transcode): void
    {
        $randSubTargetPath = $transcode->getRandSubTargetPath();
        $audioCodec = 'libmp3lame';
        $videoCodec = $this->getVideoCodec($transcode->getTranscodeFormat());
        $representation = $transcode->getRepresentation();
        //TODO: if null -> extract resolution from ffmpeg command directly -> also extract video file length
        $representationWidth = $representation !== null ? $representation->getResolutionWidth() : 'original';

        //TODO: create ffmpeg driver for ffmpeg logic
        $inputFile = escapeshellarg($transcode->getFilePath());
        $publishUrl = escapeshellarg("rtsp://rtsp_server:8554/$randSubTargetPath");

        $audioTrackNumber = $transcode->getAudioTrackNumber();
        --$audioTrackNumber;
        $audioTrack = "-map 0:a:$audioTrackNumber";

        $representationCommand = $this->createRepresentationCommand($representation);

        $command = "ffmpeg -re -i $inputFile";
        $command .= " -c:v $videoCodec -c:a $audioCodec";
        $command .= " -preset veryfast";
        $command .= " $audioTrack";
        $command .= " $representationCommand";
        $command .= " -f rtsp -rtsp_transport tcp $publishUrl";

        $this->executeCommand($command, $transcode);
    }

//    public function hlsTranscode(Transcode $transcode): void
//    {
//        $randSubTargetPath = $transcode->getRandSubTargetPath();
//        shell_exec('mkdir ' . $_ENV['TRANSCODE_PATH'] . '/' . $randSubTargetPath);
//
//        $saveLocation = $_ENV['TRANSCODE_PATH'] . '/' . $randSubTargetPath . '/' . $_ENV['STREAM_FILENAME'];
//        $progressLocation = $_ENV['TRANSCODE_PATH'] . '/' . $randSubTargetPath . '/transcode_progress.txt';
//
//        //TODO: make configurable
//        $cpuThreads = '8';
//        $defaultAudioCodec = 'libmp3lame';
//
//        $videoCodec = $this->getVideoCodec($transcode->getTranscodeFormat());
//
//        $representation = $transcode->getRepresentation();
//        //TODO: if null -> extract resolution from ffmpeg command directly -> also extract video file length
//        $representationWidth = $representation !== null ? $representation->getResolutionWidth() : 'original';
//
//        //TODO: create ffmpeg driver for this hls logic
//        $inputFile = escapeshellarg($transcode->getFilePath());
//        $progressLocation = escapeshellarg($progressLocation);
//        $indexFileName = escapeshellarg($_ENV['STREAM_FILENAME'] . '.m3u8');
//        $m3u8IndexFileLocation = escapeshellarg($saveLocation . "_$representationWidth" . 'p.m3u8');
//        $tsFileLocation = escapeshellarg($saveLocation . "_$representationWidth" . 'p_%04d.ts');
//        $hlsMp4InitName = escapeshellarg($saveLocation . "_$representationWidth" . 'p_init.mp4');
//
//        $audioTrackNumber = $transcode->getAudioTrackNumber();
//        --$audioTrackNumber;
//        $audioTrack = "-map 0:a:$audioTrackNumber";
//
//        $representationCommand = $this->createRepresentationCommand($representation);
//
//        $command = "ffmpeg -y -i $inputFile -c:v $videoCodec -c:a $defaultAudioCodec -keyint_min 25 -g 250 -sc_threshold 40 -hls_list_size 0 -hls_time 10 -hls_allow_cache 1 -hls_segment_type mpegts -hls_fmp4_init_filename $hlsMp4InitName -hls_segment_filename $tsFileLocation -master_pl_name $indexFileName -map 0:v:0 $representationCommand -f hls $audioTrack -threads $cpuThreads $m3u8IndexFileLocation -progress $progressLocation -f null -";
//
//        $this->executeCommand($command, $transcode);
//    }

    private function createRepresentationCommand(?Representation $representation): string
    {
        if ($representation === null) {
            return '-map 0:v:0';
//            return '-c copy'; //TODO: copies all the original audio/video over, we dont want that.
        }

        $resolution = $representation->getResolution();
        $resolutionColon = $representation->getResolutionColon();
        $bitrate = $representation->getKiloBiteRate();

        $fixAspectRatio = "SRC -vf 'scale=$resolutionColon:force_original_aspect_ratio=decrease,pad=$resolutionColon:(ow-iw)/2:(oh-ih)/2,setsar=1' DEST";

        //TODO: this is flawed, it ignores the original aspect ratio
        return "-map 0:v:0 -s:v:0 $resolution -b:v:0 $bitrate" . 'k';
//        return "-s:v:0 $resolution -b:v:0 $bitrate" . "k $fixAspectRatio";
    }

    private function executeCommand(string $command, Transcode $transcode): void
    {
        $sessionName = 'orbit_live_' . $transcode->getRandSubTargetPath();
        $killSessionOnSuccess = "tmux kill-session -t $sessionName";
        $command = escapeshellarg("$command && $killSessionOnSuccess");

        $createDetachedSession = "sudo tmux new-session -t $sessionName -d";
        shell_exec($createDetachedSession);

        $execCommand = "sudo tmux send -t $sessionName $command ENTER";
        shell_exec($execCommand);
    }

//    private function executeCommand(string $command, Transcode $transcode): void
//    {
//        $descriptors = [
//            0 => ['pipe', 'r'],
//            1 => ['pipe', 'w'],
//            2 => ['pipe', 'w'],
//        ];
//
//        $process = proc_open($command, $descriptors, $pipes);
//
//        if (is_resource($process)) {
//            fclose($pipes[0]);
//
//            stream_set_blocking($pipes[1], false);
//            stream_set_blocking($pipes[2], false);
//
//            while (true) {
//                $output = stream_get_contents($pipes[1]);
//                $error = stream_get_contents($pipes[2]);
//
//                if (feof($pipes[1]) && feof($pipes[2])) {
//                    break;
//                }
//
//                if (!empty($error)) {
////                    dump($error);
//                    //TODO: parse and show in UI. maybe in terminal like looking window
//                }
//
//                $this->updateTranscodeStatus($transcode);
//
//                usleep(100000);
//            }
//
//            fclose($pipes[1]);
//            fclose($pipes[2]);
//
//            // Process the exit code if needed
//            //$exitCode = proc_close($process);
//        }
//    }

    private function updateTranscodeStatus(Transcode $transcode): void
    {
        $progressLocation = '/orbit/transcode/' . $transcode->getRandSubTargetPath() . '/transcode_progress.txt';
        $progress = shell_exec("tail $progressLocation");

        if (preg_match('/out_time_ms=(\d+)/', $progress, $matches)) {
            $outTimeMs = $matches[1];
        } else {
            $outTimeMs = null;
        }

        if (preg_match('/speed=(.*?)x\n/', $progress, $matches)) {
            $speed = $matches[1];
        } else {
            $speed = null;
        }

        if ($outTimeMs !== null) {
            $currentTimeSeconds = $outTimeMs / 1000000;
            $videoTotalLengthSeconds = 1149; //TODO: get actual video length

            $percentage = (int) round(($currentTimeSeconds / $videoTotalLengthSeconds) * 100);
            $transcode->setTranscodingProgress($percentage);
            $this->transcodeRepository->save($transcode);
        }

        //TODO: set transcoding speed e.g. speed=1.62x
    }

    //    private function getRepresentations(Transcode $transcode): array
    //    {
    //        $representations = [];
    //        foreach ($transcode->getRepresentations() as $representation) {
    //            $representations[] = (new Representation)->setKiloBitrate($representation->getKiloBiteRate())
    //                ->setResize($representation->getResolutionWidth(), $representation->getResolutionHeight());
    //        }
    //
    //        return $representations;
    //    }

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
        //The default option is libmp3lame since the majority, if not all, VRChat video players are only compatible with mp3 codec.
        $defaultAudioCodec = 'libmp3lame';

        return match ($format) {
            Format::HEVC->value => new HEVC('libx265', $defaultAudioCodec),
            Format::VP9->value => new VP9('libvpx-vp9', $defaultAudioCodec),
            default => new X264('libx264', $defaultAudioCodec),
        };
    }

    public function getAvailableTracksByFilePathAndVideoProperty(string $filePath, string $property): array
    {
        $filePath = escapeshellarg($filePath);
        $output = shell_exec("ffmpeg -i $filePath 2>&1");
        $lines = explode("\n", $output);
        $streams = [];

        foreach ($lines as $line) {
            if (str_contains($line, "$property:")) {
                preg_match('/\((\w+)\)/', $line, $matches);
                $languageCode = strtoupper($matches[1]);
                $attributes = explode("$property: ", $line)[1];
                $streamName = $languageCode . ' - ' . $attributes;
                preg_match('/Stream #\d+:(\d+)/', $line, $matches);
                $streamNumber = $matches[1];
                $streams[] = new VideoProperty($streamNumber, $streamName);
            }
        }

        return $streams;
    }
}
