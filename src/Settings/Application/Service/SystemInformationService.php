<?php

declare(strict_types=1);

namespace App\Settings\Application\Service;

use App\Settings\Domain\Model\StoragePath;

readonly class SystemInformationService
{
    public function getStoragePaths(): array
    {
        $paths = [$_ENV['VIDEO_PATH'], $_ENV['TRANSCODE_PATH']];
        $storagePaths = [];

        foreach ($paths as $path) {
            $storagePaths[] = new StoragePath($path);
        }

        return $storagePaths;
    }

    public function getCPUCores(): int
    {
        $cores = 1;

        if(is_file('/proc/cpuinfo')) {
            $cpuInfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuInfo, $matches);
            $cores = count($matches[0]);
        }

        return $cores;
    }
}
