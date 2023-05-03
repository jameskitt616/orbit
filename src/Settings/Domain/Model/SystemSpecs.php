<?php

declare(strict_types=1);

namespace App\Settings\Domain\Model;

class SystemSpecs
{

    public string $name;
    public int $threads;
    public string $sysLoadAvg;

    public function __construct(string $name, int $threads, string $sysLoadAvg)
    {
        $this->name = $name;
        $this->threads = $threads;
        $this->sysLoadAvg = $sysLoadAvg;
    }
}
