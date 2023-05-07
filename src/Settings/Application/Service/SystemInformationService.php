<?php

declare(strict_types=1);

namespace App\Settings\Application\Service;

use App\Settings\Domain\Model\StoragePath;
use App\Settings\Domain\Model\SystemSpecs;

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
        if (!is_file('/proc/cpuinfo')) {
            return null;
        }

        $cpuDescription = str_replace("model name\t: ", '', file('/proc/cpuinfo')[4]);

        $cpuInfo = file_get_contents('/proc/cpuinfo');
        preg_match_all('/^processor/m', $cpuInfo, $matches);
        $threads = count($matches[0]);

        $sysLoadAvg = sys_getloadavg();
        $sysLoad = round($sysLoadAvg[0], 2) . ' ' . round($sysLoadAvg[1], 2) . ' ' . round($sysLoadAvg[2], 2);

        $ram = explode(' ', shell_exec('free -b | awk \'NR==2{print $2,$3,$4,$5,$6,$7}\''));

        return new SystemSpecs($cpuDescription, $threads, $sysLoad, (int) $ram[0], (int) $ram[1]);
    }
}
