<?php

declare(strict_types=1);

namespace App\Stream\Presentation\Controller;

use App\Stream\Application\Service\StreamService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class StreamController extends AbstractController
{
    public function __construct(
        private readonly StreamService $streamService
    )
    {
    }

    #[Route(path: '/stream', name: 'stream', methods: ['GET'])]
    public function show(): Response
    {
        $this->streamService->stream();

        return new Response('Totally a test controller!');
    }
}
