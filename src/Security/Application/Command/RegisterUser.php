<?php

declare(strict_types=1);

namespace App\Security\Application\Command;

use App\Kernel\Application\Command;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterUser implements Command
{
    #[Assert\NotNull]
    public string $username;
    #[Assert\NotNull]
    public string $password;
    public bool $isAdmin;

    public function __construct(bool $isAdmin)
    {
        $this->isAdmin = $isAdmin;
    }
}
