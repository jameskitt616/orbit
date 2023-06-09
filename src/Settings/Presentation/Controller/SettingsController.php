<?php

declare(strict_types=1);

namespace App\Settings\Presentation\Controller;

use App\Kernel\Application\CommandBus;
use App\Security\Application\Command\AccountUpdate;
use App\Security\Application\Command\DeleteUser;
use App\Security\Application\Command\RegisterUser;
use App\Security\Application\Service\SecurityService;
use App\Security\Domain\Model\User;
use App\Security\Domain\Repository\UserRepository;
use App\Settings\Application\Service\SystemInformationService;
use App\Settings\Presentation\Form\AccountUpdateForm;
use App\Settings\Presentation\Form\RegisterUserForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route(path: '/settings')]
class SettingsController extends AbstractController
{
    public function __construct(
        private readonly SystemInformationService $systemInformationService,
        private readonly SecurityService          $securityService,
        private readonly CommandBus               $commandBus,
        private readonly UserRepository           $userRepository,
        private readonly AuthenticationUtils      $authenticationUtils,
    )
    {
    }

    #[Route(path: '/system/information', name: 'settings_system_information', methods: ['GET'])]
    public function systemInformation(): Response
    {
        $systemSpecs = $this->systemInformationService->getSystemSpecs();
        $storagePaths = $this->systemInformationService->getStoragePaths();

        return $this->render('settings/system_information.html.twig', [
            'systemSpecs' => $systemSpecs,
            'storagePaths' => $storagePaths,
        ]);
    }

    #[Route(path: '/users/list', name: 'settings_users_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function users(): Response
    {
        return $this->render('settings/user_list.html.twig', [
            'users' => $this->userRepository->findAll(),
        ]);
    }

    #[Route(path: '/users/{user}/delete', name: 'settings_user_delete', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(User $user): Response
    {
        $command = new DeleteUser($user, $this->securityService->getCurrentUser());
        $this->commandBus->handle($command);

        return $this->redirectToRoute('settings_users_list');
    }

    #[Route(path: '/account', name: 'settings_account', methods: ['GET', 'POST'])]
    public function account(Request $request): Response
    {
        $command = new AccountUpdate($this->securityService->getCurrentUser());
        $url = $this->generateUrl('settings_account');
        $form = $this->createForm(AccountUpdateForm::class, $command, [
            'action' => $url,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);

            return $this->redirectToRoute('settings_account');
        }

        return $this->render('settings/account_update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/users/create', name: 'settings_user_create', methods: ['GET', 'POST'])]
    public function createUser(Request $request): Response
    {
        $command = new RegisterUser();
        $url = $this->generateUrl('settings_user_create');
        $form = $this->createForm(RegisterUserForm::class, $command, [
            'action' => $url,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($form->getData());

            return $this->redirectToRoute('settings_users_list');
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();

        return $this->render('security/register_user.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }
}
