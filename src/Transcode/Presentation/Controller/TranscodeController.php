<?php

declare(strict_types=1);

namespace App\Transcode\Presentation\Controller;

use App\Kernel\Application\CommandBus;
use App\Security\Application\Service\SecurityService;
use App\Transcode\Application\Command\CreateTranscode;
use App\Transcode\Application\Service\TranscodeService;
use App\Transcode\Domain\Entity\Transcode;
use App\Transcode\Domain\Repository\TranscodeRepository;
use App\Transcode\Presentation\Form\CreateTranscodeForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/transcode')]
final class TranscodeController extends AbstractController
{
    public function __construct(
        private readonly TranscodeService    $transcodeService,
        private readonly CommandBus          $commandBus,
        private readonly SecurityService     $securityService,
        private readonly TranscodeRepository $transcodeRepository,
    )
    {
    }

    #[Route(path: '/list', name: 'transcode_list', methods: ['GET'])]
    public function list(): Response
    {
        $transcodes = $this->transcodeRepository->findAllByUser($this->securityService->getCurrentUser());

        return $this->render('transcode/list.html.twig', [
            'transcodes' => $transcodes,
        ]);
    }

    #[Route(path: '/show/{transcode}', name: 'transcode_show', methods: ['GET'])]
    public function show(Transcode $transcode): Response
    {
        return $this->render('transcode/show.html.twig', [
            'transcode' => $transcode,
        ]);
    }

    #[Route(path: '/create', name: 'transcode_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $command = new CreateTranscode($this->securityService->getCurrentUser());
        $url = $this->generateUrl('transcode_create');
        $form = $this->createForm(CreateTranscodeForm::class, $command, [
            'action' => $url,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($form->getData());

            return $this->redirectToRoute('transcode_show', [
                'transcode' => $command->transcode->getId(),
            ]);
        }

        return $this->render('transcode/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
