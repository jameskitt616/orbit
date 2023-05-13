<?php

declare(strict_types=1);

namespace App\Security\Application\CommandHandler;

use App\Security\Application\Command\AccountUpdate;
use App\Security\Domain\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class AccountUpdateHandler
{
    public function __construct(
        private UserRepository              $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public function __invoke(AccountUpdate $command): void
    {
        $user = $command->currentUser;

        $user->setUsername($command->username);

        if ($command->password !== null) {
            $password = $this->userPasswordHasher->hashPassword($user, $command->password);
            $user->setPassword($password);
        }

        $this->userRepository->save($user);
    }
}
