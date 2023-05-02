<?php

declare(strict_types=1);

namespace App\Transcode\Domain\Model;

class File
{
    public string $fileName;
    public string $filePath;
    public int $sizeBytes;

    public function __construct(string $fileName, string $filePath, int $sizeBytes)
    {
        $this->fileName = $fileName;
        $this->filePath = $filePath;
        $this->sizeBytes = $sizeBytes;
    }

    public function getFileSize(): string
    {
        $dec = 2;
        $bytes = (string) $this->sizeBytes;
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor == 0)
            $dec = 0;

        return sprintf("%.{$dec}f %s", $bytes / (1024 ** $factor), $size[$factor]);
    }

    public function getDisplayFilePath(): string
    {
        return str_replace($_ENV['VIDEO_PATH'], '', $this->filePath);
    }
}
