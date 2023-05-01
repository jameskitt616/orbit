<?php

declare(strict_types=1);

namespace App\Transcode\Domain\Entity;

use App\Security\Domain\Model\User;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'transcode')]
class Transcode
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'string')]
    private string $id;

    #[ORM\Column(name: 'fileName', type: 'string', nullable: false)]
    private string $fileName;

    #[ORM\Column(name: 'filePath', type: 'string', nullable: false)]
    private string $filePath;

    #[ORM\Column(name: 'randSubTargetPath', type: 'integer', nullable: false)]
    private int $randSubTargetPath;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], inversedBy: 'transcodes')]
    #[ORM\JoinColumn(name: 'ownedBy_id', referencedColumnName: 'id', nullable: false)]
    private User $ownedBy;

    public function __construct(string $fileName, string $filePath, User $ownedBy)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->fileName = $fileName;
        $this->filePath = $filePath;
        $this->randSubTargetPath = rand();
        $this->ownedBy = $ownedBy;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getRandSubTargetPath(): int
    {
        return $this->randSubTargetPath;
    }

    public function getOwnedBy(): User
    {
        return $this->ownedBy;
    }
}
