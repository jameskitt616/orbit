<?php

declare(strict_types=1);

namespace App\Security\Domain\Enum;

final class Roles
{
    public function getRoleArray(): array
    {
        return [
            "ROLE_USER",
        ];
    }
}
