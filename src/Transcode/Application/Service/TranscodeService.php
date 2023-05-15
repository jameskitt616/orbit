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
                    //'data' => $path, //TODO: add custom data
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
//                    'data' => ['file_path' => $path],
//                    'attr' => ['file_path' => $path],
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
        $format->on('progress', function ($video, $format, $percentage) use ($transcode) {
            $percentage = (int) round($percentage);
            $transcode->setTranscodingProgress($percentage);
            $this->transcodeRepository->save($transcode);
        });

        //        dump($video->filters()->custom('[0:v]'));
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

        //        $video
        //            ->map([$transcode->getAudioTrackNumber() . ':a'], new Aac(), $saveLocation)
        //            ->map(['resultv'], $format, $saveLocation)
        //            ->save();
        //        dump($video->getFinalCommand());

        //        $transcode->getAudioTrackNumber();
        //        $transcode->getSubtitleNumber();
        $video->hls()
            ->setFormat($format)
            ->addRepresentations($representations)
            ->save($saveLocation);

        //        $inputFile = escapeshellarg($transcode->getFilePath());
        //        $saveLocation = escapeshellarg($saveLocation);
        //        $command = "ffmpeg -i /orbit/videos/biscuits.mp4 -map 0:0 -map 0:1 -c:v h264 -c:a mp3 /orbit/transcode/1832637644/out.mp4 >/dev/null 2>&1 & echo $!";
        //        $command = "ffmpeg -i $inputFile -c:v libx264 -c:a mp3 -map 0:v:0 -map 0:a:1 -hls_time 10 -hls_list_size 0 $saveLocation.m3u8";
        //        $command = "ffmpeg -i $inputFile -c:v libx264 -c:a aac -map 0:v:0 -map 0:a:1 -hls_time 10 -hls_list_size 0 $saveLocation.m3u8";
        //        $command = "ffmpeg -i input_file.mp4 -c:v libx264 -preset slow -crf 22 -c:a copy $saveLocation >/dev/null 2>&1 & echo $!";
        //        shell_exec('mkdir ' . $_ENV['TRANSCODE_PATH'] . '/' . $transcode->getRandSubTargetPath());
        //        $output = shell_exec($command);
        //        $pid = (int)$output;
        //
        //        do {
        //            $status = shell_exec("ps aux | grep $pid");
        //            $status = explode("\n", $status);
        //        } while(count($status) > 2);
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
