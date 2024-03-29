<?php

declare(strict_types=1);

namespace App\Transcode\Presentation\Controller;

use App\Kernel\Application\CommandBus;
use App\Security\Application\Service\SecurityService;
use App\Transcode\Application\Command\Create;
use App\Transcode\Application\Command\Delete;
use App\Transcode\Application\Service\ProgressService;
use App\Transcode\Application\Service\TranscodeService;
use App\Transcode\Domain\Model\File;
use App\Transcode\Domain\Model\Transcode;
use App\Transcode\Domain\Repository\TranscodeRepository;
use App\Transcode\Presentation\Form\CreateTranscodeForm;
use App\Transcode\Presentation\Form\SelectSourceForm;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        private readonly TranscodeService    $transcodeService,
        private readonly ProgressService     $progressService,
    )
    {
    }

    #[Route(path: '/list', name: 'transcode_list', methods: ['GET'])]
    public function list(): Response
    {
        $transcodes = $this->transcodeRepository->findAllByUser($this->securityService->getCurrentUser());
        $this->progressService->updateProgressForAll();

        return $this->render('transcode/list.html.twig', [
            'transcodes' => $transcodes,
        ]);
    }

    #[Route(path: '/show/{transcode}', name: 'transcode_show', methods: ['GET'])]
    public function show(Transcode $transcode): Response
    {
        $this->progressService->updateProgress($transcode);

        return $this->render('transcode/show.html.twig', [
            'transcode' => $transcode,
        ]);
    }

    #[Route(path: '/source/load', name: 'transcode_load_source_files', methods: ['GET'])]
    public function loadSourceFiles(): Response
    {
        return new JsonResponse($this->transcodeService->loadSourceFiles($_ENV['VIDEO_PATH']));
    }

    #[Route(path: '/source/select', name: 'transcode_select_source', methods: ['GET', 'POST'])]
    public function selectSource(Request $request): Response
    {
        $url = $this->generateUrl('transcode_select_source');
        $form = $this->createForm(SelectSourceForm::class, [
            'action' => $url,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $session = $request->getSession();
            $session->set('source_file', $form->getData()['filePath']);

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
        $filePath = $session->get('source_file');

        if ($filePath === null) {
            return $this->redirectToRoute('transcode_select_source');
        }

        $splFileInfo = new SplFileInfo($filePath);
        $file = new File($splFileInfo->getFilename(), $splFileInfo->getPathname(), $splFileInfo->getSize());

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
