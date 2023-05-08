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

class CreateTranscode implements Command
{
    #[Assert\NotNull]
    public File $file;
    public User $currentUser;
    #[Assert\NotNull]
    public string $format;
    public Collection $representations;
    public ?Transcode $transcode;
    public ?int $audioTrackNumber;
    public ?int $subtitleNumber;

    public function __construct(User $currentUser, File $file)
    {
        $this->currentUser = $currentUser;
        $this->file = $file;
        $this->format = Format::HEVC->name;
        $this->audioTrackNumber = null;
        $this->subtitleNumber = null;
    }
}
