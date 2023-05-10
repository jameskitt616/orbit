<?php

declare(strict_types=1);

namespace App\Transcode\Presentation\Controller;

use App\Kernel\Application\CommandBus;
use App\Security\Application\Service\SecurityService;
use App\Transcode\Application\Command\Create;
use App\Transcode\Application\Command\Delete;
use App\Transcode\Domain\Model\File;
use App\Transcode\Domain\Model\Transcode;
use App\Transcode\Domain\Repository\TranscodeRepository;
use App\Transcode\Presentation\Form\CreateTranscodeForm;
use App\Transcode\Presentation\Form\SelectSourceForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/transcode')]
final class TranscodeController extends AbstractController
{
    public function __construct(
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
            'streamFilename' => $_ENV['STREAM_FILENAME'],
        ]);
    }

    #[Route(path: '/select/source', name: 'transcode_select_source', methods: ['GET', 'POST'])]
    public function selectSource(Request $request): Response
    {
        $url = $this->generateUrl('transcode_select_source');
        $form = $this->createForm(SelectSourceForm::class, [
            'action' => $url,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $session = $request->getSession();
            $session->set('source_file', $form->getData()['file']);

            return $this->redirectToRoute('transcode_create');
        }

        return $this->render('transcode/select_source.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/create', name: 'transcode_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $session = $request->getSession();
        /** @var File $file */
        $file = $session->get('source_file');

        if ($file === null) {
            return $this->redirectToRoute('transcode_select_source');
        }

        $command = new Create($this->securityService->getCurrentUser(), $file);
        $url = $this->generateUrl('transcode_create');
        $form = $this->createForm(CreateTranscodeForm::class, $command, [
            'action' => $url,
            'file' => $file,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($form->getData());
            $session->remove('source_file');

            return $this->redirectToRoute('transcode_show', [
                'transcode' => $command->transcode->getId(),
            ]);
        }

        return $this->render('transcode/create.html.twig', [
            'form' => $form->createView(),
            'file' => $file,
        ]);
    }

    #[Route(path: '/{transcode}/delete', name: 'transcode_delete', methods: ['GET'])]
    public function delete(Transcode $transcode): Response
    {
        $command = new Delete($transcode);
        $this->commandBus->handle($command);

        return $this->redirectToRoute('transcode_list');
    }
}
