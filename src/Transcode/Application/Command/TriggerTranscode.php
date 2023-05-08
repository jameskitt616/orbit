<?php

declare(strict_types=1);

namespace App\Transcode\Application\Command;

use App\Kernel\Application\Command;
use App\Security\Domain\Model\User;
use App\Transcode\Domain\Enum\Format;
use App\Transcode\Domain\Model\File;
use App\Transcode\Domain\Model\Transcode;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class TriggerTranscode implements Command
{
    public string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
