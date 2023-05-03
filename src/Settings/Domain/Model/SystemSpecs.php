<?php

declare(strict_types=1);

namespace App\Settings\Domain\Model;

class SystemSpecs
{

    public string $name;
    public int $threads;
    public string $sysLoadAvg;
    public string $totalRam;
    public string $usedRam;
    public string $usedRamPercentage;
    public string $freeRam;
    private int $totalRamBytes;
    private int $usedRamBytes;
    private int $freeRamBytes;

    public function __construct(string $name, int $threads, string $sysLoadAvg, int $totalRamBytes, int $usedRamBytes)
    {
        $this->name = $name;
        $this->threads = $threads;
        $this->sysLoadAvg = $sysLoadAvg;
        $this->totalRamBytes = $totalRamBytes;
        $this->usedRamBytes = $usedRamBytes;
        $this->freeRamBytes = $totalRamBytes - $usedRamBytes;

        $this->calculate();
    }

    private function calculate(): void
    {
        $this->totalRam = $this->getHumanReadableSizeByBytes($this->totalRamBytes);
        $this->usedRam = $this->getHumanReadableSizeByBytes($this->usedRamBytes);
        $this->usedRamPercentage = (string) (100 - round((($this->freeRamBytes / $this->totalRamBytes) * 100), 1));
        $this->freeRam = $this->getHumanReadableSizeByBytes($this->freeRamBytes);
    }

    private function getHumanReadableSizeByBytes(float $bytes): string
    {
        $dec = 2;
        $bytes = (string) $bytes;
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor == 0)
            $dec = 0;

        return sprintf("%.{$dec}f %s", $bytes / (1024 ** $factor), $size[$factor]);
    }
}
