<?php

declare(strict_types=1);

namespace App\Settings\Domain\Model;

class StoragePath
{

    public string $path;
    public string $freeSpace;
    public string $usedSpace;
    public string $totalSpace;
    public string $usedSpacePercentage;
    private float $freeDiskSpaceBytes;
    private float $totalDiskSpaceBytes;
    private float $usedDiskSpaceBytes;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->freeDiskSpaceBytes = disk_free_space($path);
        $this->totalDiskSpaceBytes = disk_total_space($path);
        $this->usedDiskSpaceBytes = $this->totalDiskSpaceBytes - $this->freeDiskSpaceBytes;

        $this->calculate();
    }

    private function calculate(): void
    {
        $this->freeSpace = $this->getHumanReadableSizeByBytes($this->freeDiskSpaceBytes);
        $this->usedSpace = $this->getHumanReadableSizeByBytes($this->usedDiskSpaceBytes);
        $this->totalSpace = $this->getHumanReadableSizeByBytes($this->totalDiskSpaceBytes);
        $this->usedSpacePercentage = (string) (100 - round(((disk_free_space($_ENV['VIDEO_PATH']) / disk_total_space($_ENV['VIDEO_PATH'])) * 100), 1));
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
