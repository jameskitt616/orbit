<?php

declare(strict_types=1);

namespace App\Transcode\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'representation')]
class Representation
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'string')]
    private string $id;

    #[ORM\Column(name: 'name', type: 'string', nullable: false)]
    private string $name;

    #[ORM\Column(name: 'kiloBiteRate', type: 'integer', nullable: false)]
    private int $kiloBiteRate;

    #[ORM\Column(name: 'resolutionWidth', type: 'integer', nullable: false)]
    private int $resolutionWidth;

    #[ORM\Column(name: 'resolutionHeight', type: 'integer', nullable: false)]
    private int $resolutionHeight;

    public function __construct(string $name, int $kiloBiteRate, int $resolutionWidth, int $resolutionHeight)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->name = $name;
        $this->kiloBiteRate = $kiloBiteRate;
        $this->resolutionWidth = $resolutionWidth;
        $this->resolutionHeight = $resolutionHeight;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name . ' (' . $this->kiloBiteRate . ' kbps)';
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getKiloBiteRate(): int
    {
        return $this->kiloBiteRate;
    }

    public function setKiloBiteRate(int $kiloBiteRate): void
    {
        $this->kiloBiteRate = $kiloBiteRate;
    }

    public function getResolutionWidth(): int
    {
        return $this->resolutionWidth;
    }

    public function setResolutionWidth(int $resolutionWidth): void
    {
        $this->resolutionWidth = $resolutionWidth;
    }

    public function getResolutionHeight(): int
    {
        return $this->resolutionHeight;
    }

    public function setResolutionHeight(int $resolutionHeight): void
    {
        $this->resolutionHeight = $resolutionHeight;
    }
}
