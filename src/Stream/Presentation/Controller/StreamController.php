<?php

declare(strict_types=1);

namespace App\Stream\Presentation\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class StreamController extends AbstractController
{
    #[Route(path: '/stream', name: 'stream', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render('security/login.html.twig', [
        ]);
    }
}
