<?php

declare(strict_types=1);

namespace App\Transcode\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
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

    public function __construct(string $fileName, string $filePath)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->fileName = $fileName;
        $this->filePath = $filePath;
        $this->randSubTargetPath = rand();
    }
}
