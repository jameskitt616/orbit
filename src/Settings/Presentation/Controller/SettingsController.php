<?php

declare(strict_types=1);

namespace App\Settings\Presentation\Controller;

use App\Settings\Application\Service\SystemInformationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/settings')]
class SettingsController extends AbstractController
{
    public function __construct(
        private readonly SystemInformationService $systemInformationService,
    )
    {
    }

    #[Route(path: '/system/information', name: 'settings_system_information', methods: ['GET'])]
    public function systemInformation(): Response
    {
        $cores = $this->systemInformationService->getCPUCores();
        $storagePaths = $this->systemInformationService->getStoragePaths();

        return $this->render('settings/system_information.html.twig', [
            'nav' => 'system_information',
            'cores' => $cores,
            'storagePaths' => $storagePaths,
        ]);
    }
}
