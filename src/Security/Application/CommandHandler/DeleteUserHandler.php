<?php

declare(strict_types=1);

namespace App\Security\Application\CommandHandler;

use App\Security\Application\Command\DeleteUser;
use App\Security\Domain\Repository\UserRepository;

readonly class DeleteUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
    )
    {
    }

    public function __invoke(DeleteUser $command): void
    {
        $admins = $this->getAdmins();
        if ($command->currentUser !== $command->user || count($admins) > 1) {
            $this->userRepository->delete($command->user);
        }
    }

    private function getAdmins(): array
    {
        $users = $this->userRepository->findAll();
        $admins = [];

        foreach ($users as $user) {
            if ($user->isAdmin()) {
                $admins[] = $user;
            }
        }

        return $admins;
    }
}
