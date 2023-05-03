<?php

declare(strict_types=1);

namespace App\Settings\Application\Service;

use App\Settings\Domain\Model\SystemSpecs;
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

    public function getSystemSpecs(): ?SystemSpecs
    {
        if(!is_file('/proc/cpuinfo')) {
            return null;
        }

        $cpuDescription = str_replace("model name\t: ", '', file('/proc/cpuinfo')[4]);

        $cpuInfo = file_get_contents('/proc/cpuinfo');
        preg_match_all('/^processor/m', $cpuInfo, $matches);
        $threads = count($matches[0]);

        $sysLoadAvg = sys_getloadavg();
        $sysLoad = round($sysLoadAvg[0],2) . ' ' . round($sysLoadAvg[1],2) . ' ' . round($sysLoadAvg[2],2);

//        $data = explode("\n", file_get_contents("/proc/meminfo"));
//        $meminfo = array();
//        foreach ($data as $line) {
//            list($key, $val) = explode(":", $line);
//            $meminfo[$key] = trim($val);
//        }

        return new SystemSpecs($cpuDescription, $threads, $sysLoad);
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
