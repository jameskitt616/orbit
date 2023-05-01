<?php

declare(strict_types=1);

namespace App\Security\Application\CommandHandler;

use App\Security\Application\Command\RegisterUser;
use App\Security\Domain\Model\User;
use App\Security\Domain\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class RegisterUserHandler
{
    public function __construct(
        private UserRepository              $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public function __invoke(RegisterUser $command): void
    {
        $user = new User($command->username, $command->isAdmin);
        $password = $this->userPasswordHasher->hashPassword($user, $command->password);
        $user->setPassword($password);
        $this->userRepository->save($user);
    }
}
