<?php

declare(strict_types=1);

namespace App\Transcode\Application\Service;

use App\Transcode\Domain\Enum\AudioFormat;
use App\Transcode\Domain\Enum\VideoFormat;
use App\Transcode\Domain\Model\Representation;
use App\Transcode\Domain\Model\Transcode;
use App\Transcode\Domain\Model\VideoProperty;

final readonly class TranscodeService
{
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
        $randSubTargetPath = $transcode->getRandSubTargetPath();
        $audioCodec = $this->getAudioCodec('AAC'); //TODO: make selectable in form
        $audioChannels = $this->getAudioChannels($transcode);
        $audioTrackNumber = $transcode->getAudioTrackNumber();
        $videoCodec = $this->getVideoCodec($transcode->getTranscodeFormat());
        $representation = $transcode->getRepresentation();

        $inputFile = escapeshellarg($transcode->getFilePath());
        $publishUrl = escapeshellarg("rtsp://rtsp_server:8554/$randSubTargetPath");

        //TODO: subtitles can also be none
        //TODO: add subtitle functionality
        //$subtitleTrackNumber = $transcode->getSubtitleNumber();
        //--$subtitleTrackNumber;
        //$subtitleTrack = "-map 0:a:$subtitleTrackNumber";

        $representationCommand = $this->createRepresentationCommand($representation);

        $command = "ffmpeg -re -i $inputFile";
        $command .= " -c:v $videoCodec";
        $command .= " $representationCommand";
        $command .= " -preset veryfast";
        $command .= " -map 0:a:$audioTrackNumber -c:a $audioCodec -ac $audioChannels";
        $command .= " -f rtsp -rtsp_transport tcp $publishUrl";

        $this->executeCommand($command, $transcode);
    }

    private function createRepresentationCommand(?Representation $representation): string
    {
        if ($representation === null) {
            return '-map 0:v:0';
        }

        $resolution = $representation->getResolution();
        $resolutionColon = $representation->getResolutionColon();
        $bitrate = $representation->getKiloBiteRate() . 'k';

        $fixAspectRatio = "SRC -vf 'scale=$resolutionColon:force_original_aspect_ratio=decrease,pad=$resolutionColon:(ow-iw)/2:(oh-ih)/2,setsar=1' DEST";

        //TODO: this is flawed, it ignores the original aspect ratio
        return "-map 0:v:0 -s:v:0 $resolution -b:v:0 $bitrate";
        //return "-s:v:0 $resolution -b:v:0 $bitrate" . "k $fixAspectRatio";
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

    private function getAudioChannels(Transcode $transcode): string
    {
        $filePath = escapeshellarg($transcode->getFilePath());
        $audioStreamNumber = $transcode->getAudioTrackNumber();

        $command = "ffprobe -i $filePath -show_entries stream=channels -select_streams a:$audioStreamNumber -of compact=p=0:nk=1 -v 0";
        $channels = shell_exec($command);

        return preg_replace('/[^0-9.]+/', '', $channels);
    }

    private function getAudioCodec(string $format): string
    {
        return match ($format) {
            AudioFormat::OPUS->value => 'opus',
            AudioFormat::AAC->value => 'aac',
            default => 'libmp3lame',
        };
    }

    private function getVideoCodec(string $format): string
    {
        return match ($format) {
            VideoFormat::HEVC->value => 'libx265',
            default => 'libx264',
        };
    }

    public function getAvailableTracksByFilePathAndProperty(string $filePath, string $property): array
    {
        $filePath = escapeshellarg($filePath);
        $output = shell_exec("ffmpeg -i $filePath 2>&1");
        $lines = explode("\n", $output);
        $streams = [];

        foreach ($lines as $line) {
            if (str_contains($line, ": $property:")) {
                $languageCode = '';
                preg_match('/\((\w+)\)/', $line, $matches);
                if (isset($matches[1])) {
                    $description = $matches[1];
                    $languageCode = strtoupper($description) . ' - ';
                }

                $attributes = explode(": $property: ", $line)[1];
                $streamName = $languageCode . $attributes;

                preg_match('/Stream #\d+:(\d+)/', $line, $streamIds);
                $streamNumber = $streamIds[1];
                $streams[] = new VideoProperty($streamNumber, $streamName);
            }
        }

        return $streams;
    }
}
