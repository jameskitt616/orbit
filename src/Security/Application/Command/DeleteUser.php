<?php

declare(strict_types=1);

namespace App\Security\Application\Command;

use App\Kernel\Application\Command;
use App\Security\Domain\Model\User;

class DeleteUser implements Command
{

    public User $user;
    public User $currentUser;

    public function __construct(User $user, User $currentUser)
    {
        $this->user = $user;
        $this->currentUser = $currentUser;
    }
}
