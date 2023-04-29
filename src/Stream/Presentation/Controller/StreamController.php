<?php

declare(strict_types=1);

namespace App\Stream\Presentation\Controller;

use FFMpeg\FFMpeg;
use Streaming\FFMpeg as SFFMpeg;
use Streaming\Format\X264;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class StreamController extends AbstractController
{
    #[Route(path: '/stream', name: 'stream', methods: ['GET'])]
    public function show(): Response
    {
        $config = [
            'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/bin/ffprobe',
            'timeout'          => 3600,
            'ffmpeg.threads'   => 12,
        ];

        $ffmpeg = FFMpeg::create($config);
        $sffmpeg = new SFFMpeg($ffmpeg);
        $video = $sffmpeg->open('/video_data/biscuits.mp4');

        $format = new x264();
        $format->on('progress', function ($video, $format, $percentage){
            // You can update a field in your database or can log it to a file
            // You can also create a socket connection and show a progress bar to users
            dump(sprintf("\rTranscoding...(%s%%) [%s%s]", $percentage, str_repeat('#', $percentage), str_repeat('-', (100 - $percentage))));
        });

        $video->dash()
            ->setFormat($format)
            ->autoGenerateRepresentations()
            ->save('/video_data/biscuits_new.mp4');

        return new Response('Totally a test controller!');
    }
}
