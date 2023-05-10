<?php

declare(strict_types=1);

namespace App\Transcode\Application\Command;

use App\Kernel\Application\Command;
use App\Security\Domain\Model\User;
use App\Transcode\Domain\Enum\Format;
use App\Transcode\Domain\Model\File;
use App\Transcode\Domain\Model\Transcode;
use App\Transcode\Domain\Model\VideoProperty;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class Create implements Command
{
    #[Assert\NotNull]
    public File $file;
    public User $currentUser;
    #[Assert\NotNull]
    public string $format;
    public Collection $representations;
    public ?Transcode $transcode;
    public ?VideoProperty $videoPropertyAudio;
    public ?VideoProperty $videoPropertySubtitle;

    public function __construct(User $currentUser, File $file)
    {
        $this->currentUser = $currentUser;
        $this->file = $file;
        $this->format = Format::HEVC->name;
        $this->videoPropertyAudio = null;
        $this->videoPropertySubtitle = null;
    }
}
