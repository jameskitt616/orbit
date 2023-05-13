<?php

declare(strict_types=1);

namespace App\Settings\Presentation\Controller;

use App\Kernel\Application\CommandBus;
use App\Security\Application\Command\AccountUpdate;
use App\Security\Application\Service\SecurityService;
use App\Settings\Application\Service\SystemInformationService;
use App\Settings\Presentation\Form\AccountUpdateForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/settings')]
class SettingsController extends AbstractController
{
    public function __construct(
        private readonly SystemInformationService $systemInformationService,
        private readonly SecurityService          $securityService,
        private readonly CommandBus               $commandBus
    )
    {
    }

    #[Route(path: '/system/information', name: 'settings_system_information', methods: ['GET'])]
    public function systemInformation(): Response
    {
        $systemSpecs = $this->systemInformationService->getSystemSpecs();
        $storagePaths = $this->systemInformationService->getStoragePaths();

        return $this->render('settings/system_information.html.twig', [
            'nav' => 'system_information',
            'systemSpecs' => $systemSpecs,
            'storagePaths' => $storagePaths,
            'currentUser' => $this->securityService->getCurrentUser(),
        ]);
    }

    #[Route(path: '/account', name: 'settings_account', methods: ['GET', 'POST'])]
    public function account(Request $request): Response
    {
        $currentUser = $this->securityService->getCurrentUser();
        $command = new AccountUpdate($currentUser);
        $url = $this->generateUrl('settings_account');
        $form = $this->createForm(AccountUpdateForm::class, $command ,[
            'action' => $url,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);

            return $this->redirectToRoute('settings_account');
        }

        return $this->render('settings/account_update.html.twig', [
            'form' => $form->createView(),
            'currentUser' => $currentUser,
        ]);
    }
}
