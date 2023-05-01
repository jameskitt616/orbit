<?php

declare(strict_types=1);

namespace App\Transcode\Presentation\Controller;

use App\Transcode\Application\Service\TranscodeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/transcode')]
final class TranscodeController extends AbstractController
{
    public function __construct(
        private readonly TranscodeService $transcodeService
    )
    {
    }

    #[Route(path: '/select', name: 'transcode_select', methods: ['GET'])]
    public function select(): Response
    {
        $videos = $this->transcodeService->listAvailableVideos();

        return $this->render('transcode/select.html.twig', [
            'videos' => $videos,
        ]);
    }

    #[Route(path: '/list', name: 'transcode_list', methods: ['GET'])]
    public function list(): Response
    {
        //        $this->streamService->stream();

        return $this->render('transcode/select.html.twig');
    }
}
