<?php

declare(strict_types=1);

namespace App\Stream\Presentation\Controller;

use FFMpeg\FFMpeg;
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
//        $video = $ffmpeg->open('');
//        dump($video);

        return new Response('asd');
//        return $this->render('security/login.html.twig', [
//        ]);
    }
}
