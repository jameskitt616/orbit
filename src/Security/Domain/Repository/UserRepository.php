<?php

declare(strict_types=1);

namespace App\Security\Domain\Repository;

use App\Security\Domain\Model\User;

interface UserRepository
{
    /**
     * @param int $id
     *
     * @return User
     */
    public function findById(int $id): User;

    /**
     * @param string $username
     *
     * @return User|null
     */
    public function findByUsername(string $username): ?User;


    /**
     * @param User $user
     */
    public function save(User $user): void;
}
