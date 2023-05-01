<?php

declare(strict_types=1);

namespace App\Transcode\Application\Service;

use App\Transcode\Domain\Entity\File;
use App\Transcode\Domain\Entity\Transcode;
use FFMpeg\FFMpeg;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Streaming\FFMpeg as SFFMpeg;
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
        dump($saveLocation);

        $r_720p = (new Representation)->setKiloBitrate(2048)->setResize(1280, 720);
        $r_1080p = (new Representation)->setKiloBitrate(4096)->setResize(1920, 1080);
        $r_4k = (new Representation)->setKiloBitrate(17408)->setResize(3840, 2160);

        $video->hls()
            ->x264()
            //            ->autoGenerateRepresentations()
            //            ->autoGenerateRepresentations([720, 1080]) // leave out for default conversion
            ->addRepresentations([$r_1080p])
            ->save($saveLocation);
    }
}
