<?php

declare(strict_types=1);

namespace App\Transcode\Domain\Enum;

enum Format: string
{
    case X264 = 'x264';
    case HEVC = 'HEVC';
    case VP9 = 'VP9';
}
