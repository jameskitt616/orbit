<?php

declare(strict_types=1);

namespace App\Stream\Application\Service;

use FFMpeg\FFMpeg;
use Streaming\FFMpeg as SFFMpeg;

final class StreamService
{
    public function stream(): void
    {
        //TODO: expose config?
        $config = [
            'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/bin/ffprobe',
            'timeout'          => 3600,
            'ffmpeg.threads'   => 12,
        ];

        $ffmpeg = FFMpeg::create($config);
        $sFfmpeg = new SFFMpeg($ffmpeg);
        $video = $sFfmpeg->open('/video_data/biscuits.mp4');

        //        $format = new x264();
        //        $format->on('progress', function ($video, $format, $percentage){
        //            // You can update a field in your database or can log it to a file
        //            // You can also create a socket connection and show a progress bar to users
        //            dump(sprintf("\rTranscoding...(%s%%) [%s%s]", $percentage, str_repeat('#', $percentage), str_repeat('-', (100 - $percentage))));
        //        });
//        dump(getcwd());
//        $sysUser = exec('whoami');
//        dump($sysUser);
//        $fileName = substr(strrchr($video->baseMedia()->getPathfile(), '/'),1);
        $fileName = 'live';
        $path = rand();
        $saveLocation = '/video_data/live/' . $path . '/' . $fileName;

        $video->hls()
            ->x264()
            ->autoGenerateRepresentations()
//            ->autoGenerateRepresentations([720, 1080]) // leave out for default conversion
            ->save($saveLocation);
    }
}
