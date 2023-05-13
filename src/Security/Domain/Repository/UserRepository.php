<?php

declare(strict_types=1);

namespace App\Security\Domain\Repository;

use App\Security\Domain\Model\User;

interface UserRepository
{
    public function findById(int $id): User;

    public function findByUsername(string $username): ?User;

    /** @return User[] */
    public function findAll(): array;

    public function save(User $user): void;

    public function delete(User $user): void;
}
