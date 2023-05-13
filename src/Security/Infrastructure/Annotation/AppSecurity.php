<?php

declare(strict_types=1);

namespace App\Security\Infrastructure\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class AppSecurity
{
    private ?string $permission;

    public function __construct(array $values)
    {
        $this->permission = $values['permission'] ?? null;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }
}
