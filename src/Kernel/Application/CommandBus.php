<?php

declare(strict_types=1);

namespace App\Kernel\Application;

interface CommandBus
{
    public function handle(Command $command): void;
}
