<?php

declare(strict_types=1);

namespace App\Transcode\Application\Command;

use App\Kernel\Application\Command;

class TriggerTranscode implements Command
{
    public string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
