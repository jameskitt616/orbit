<?php

declare(strict_types=1);

namespace App\Security\Presentation\Controller;

use App\Kernel\Application\CommandBus;
use App\Security\Application\Command\RegisterUser;
use App\Security\Domain\Repository\UserRepository;
use App\Security\Presentation\Form\RegisterUserForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly AuthenticationUtils $authenticationUtils,
        private readonly CommandBus          $commandBus,
        private readonly UserRepository      $userRepository,
    )
    {
    }

    #[Route(path: '/', name: 'default_entry', methods: ['GET'])]
    public function defaultEntryRoute(): Response
    {
        return $this->redirectToRoute('transcode_list');
    }

    #[Route(path: '/register/admin', name: 'register_admin', methods: ['GET', 'POST'])]
    public function registerAdmin(Request $request): Response
    {
        $users = $this->userRepository->findAll();

        if (!empty($users)) {
            return $this->redirectToRoute('login');
        }

        $command = new RegisterUser();
        $command->isAdmin = true;
        $url = $this->generateUrl('register_admin');
        $form = $this->createForm(RegisterUserForm::class, $command, [
            'action' => $url,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($form->getData());

            return $this->redirectToRoute('default_entry');
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }

    #[Route(path: '/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(): Response
    {
        $users = $this->userRepository->findAll();

        if (empty($users)) {
            return $this->redirectToRoute('register_admin');
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername,
        ]);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
    }
}
