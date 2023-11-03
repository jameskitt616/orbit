<?php

declare(strict_types=1);

namespace App\Security\Application\Service;

use App\Security\Domain\Model\User;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

readonly class SecurityService
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
    )
    {
    }

    private function getToken(): TokenInterface
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            throw new RuntimeException('try to get user from token without user context');
        }

        return $token;
    }

    public function getCurrentUser(): User
    {
        /** @var User $user */
        $user = $this->getToken()->getUser();

        return $user;
    }
}
