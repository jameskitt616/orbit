<?php

declare(strict_types=1);

namespace App\Transcode\Application\Command;

use App\Kernel\Application\Command;
use App\Security\Domain\Model\User;
use App\Transcode\Domain\Entity\File;
use App\Transcode\Domain\Entity\Transcode;
use Symfony\Component\Validator\Constraints as Assert;

class CreateTranscode implements Command
{
    #[Assert\NotNull]
    public File $file;
    public ?Transcode $transcode;
    public User $currentUser;

    public function __construct(User $currentUser)
    {
        $this->currentUser = $currentUser;
    }
}
