<?php

declare(strict_types=1);

namespace App\Security\Application\Command;

use App\Kernel\Application\Command;
use App\Security\Domain\Model\User;

class AccountUpdate implements Command
{
    public string $username;
    public ?string $password;
    public User $currentUser;

    public function __construct(User $currentUser)
    {
        $this->currentUser = $currentUser;
        $this->username = $currentUser->getUsername();
        $this->password = null;
    }
}
