<?php

declare(strict_types=1);

namespace App\Transcode\Application\Command;

use App\Kernel\Application\Command;
use App\Transcode\Domain\Model\Transcode;

class Delete implements Command
{
    public Transcode $transcode;

    public function __construct(Transcode $transcode)
    {
        $this->transcode = $transcode;
    }
}
