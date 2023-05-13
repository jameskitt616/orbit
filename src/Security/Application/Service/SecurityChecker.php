<?php

declare(strict_types=1);

namespace App\Security\Application\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class SecurityChecker
{

    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    )
    {
    }

    public function hasPermission(string $permission): bool
    {
        return $this->authorizationChecker->isGranted($permission);
    }

    public function denyAccessWithoutPermission(string $permission): void
    {
        if (!$this->hasPermission($permission)) {
            throw new AccessDeniedException('Access denied (Role "' . $permission . '").');
        }
    }
}
