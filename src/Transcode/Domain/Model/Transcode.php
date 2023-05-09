<?php

declare(strict_types=1);

namespace App\Transcode\Domain\Model;

use App\Security\Domain\Model\User;
use DateTime;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(name: 'transcodeFormat', type: 'string', nullable: false)]
    private string $transcodeFormat;

    #[ORM\Column(name: 'transcodingProgress', type: 'integer', nullable: false)]
    private int $transcodingProgress;

    #[ORM\ManyToMany(targetEntity: Representation::class, cascade: ['persist'])]
    private Collection $representations;

    #[ORM\Column(name: 'createdAt', type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(name: 'audioTrackNumber', type: 'integer', nullable: true)]
    private ?int $audioTrackNumber;

    #[ORM\Column(name: 'audioTrackNumberTitle', type: 'string', nullable: true)]
    private ?string $audioTrackNumberTitle;

    #[ORM\Column(name: 'subtitleNumber', type: 'integer', nullable: true)]
    private ?int $subtitleNumber;

    #[ORM\Column(name: 'subtitleNumberTitle', type: 'string', nullable: true)]
    private ?string $subtitleNumberTitle;

    public function __construct(string $fileName, string $filePath, User $ownedBy, string $transcodeFormat, Collection $representations)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTime();
        $this->fileName = $fileName;
        $this->filePath = $filePath;
        $this->randSubTargetPath = rand();
        $this->ownedBy = $ownedBy;
        $this->transcodingProgress = 0;
        $this->transcodeFormat = $transcodeFormat;
        $this->representations = $representations;
        $this->audioTrackNumber = null;
        $this->audioTrackNumberTitle = null;
        $this->subtitleNumber = null;
        $this->subtitleNumberTitle = null;
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

    public function getTranscodingProgress(): int
    {
        return $this->transcodingProgress;
    }

    public function setTranscodingProgress(int $transcodingProgress): void
    {
        $this->transcodingProgress = $transcodingProgress;
    }

    public function getTranscodeFormat(): string
    {
        return $this->transcodeFormat;
    }

    /** @return Representation[] */
    public function getRepresentations(): array
    {
        return $this->representations->toArray();
    }

    public function getAudioTrackNumber(): ?int
    {
        return $this->audioTrackNumber;
    }

    public function getSubtitleNumber(): ?int
    {
        return $this->subtitleNumber;
    }

    public function getAudioTrackNumberTitle(): ?string
    {
        return $this->audioTrackNumberTitle;
    }

    public function getSubtitleNumberTitle(): ?string
    {
        return $this->subtitleNumberTitle;
    }

    public function setAudioTrackNumber(?int $audioTrackNumber): void
    {
        $this->audioTrackNumber = $audioTrackNumber;
    }

    public function setAudioTrackNumberTitle(?string $audioTrackNumberTitle): void
    {
        $this->audioTrackNumberTitle = $audioTrackNumberTitle;
    }

    public function setSubtitleNumber(?int $subtitleNumber): void
    {
        $this->subtitleNumber = $subtitleNumber;
    }

    public function setSubtitleNumberTitle(?string $subtitleNumberTitle): void
    {
        $this->subtitleNumberTitle = $subtitleNumberTitle;
    }
}
