<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure\Security\Hasher;

use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\PasswordHasher\Hasher\CheckPasswordLengthTrait;
use Symfony\Component\PasswordHasher\LegacyPasswordHasherInterface;

final class UserPasswordHasher implements LegacyPasswordHasherInterface
{
    use CheckPasswordLengthTrait;

    public function hash(string $plainPassword, string $salt = null): string
    {
        // The implementations of hash() and verify() must validate that the password length is no longer than 4096 characters.
        // This is for security reasons (see CVE-2013-5750).
        if ($this->isPasswordTooLong($plainPassword)) {
            throw new InvalidPasswordException();
        }

        return hash('sha512', $plainPassword . '{' . $salt . '}');
    }

    public function verify(string $hashedPassword, string $plainPassword, string $salt = null): bool
    {
        if ('' === $plainPassword || $this->isPasswordTooLong($plainPassword)) {
            return false;
        }

        return $hashedPassword === hash('sha512', $plainPassword . '{' . $salt . '}');
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return false;
    }
}
