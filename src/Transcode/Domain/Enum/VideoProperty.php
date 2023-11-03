<?php

declare(strict_types=1);

namespace App\Transcode\Domain\Enum;

enum VideoProperty: string
{
    case AUDIO = 'Audio';
    case SUBTITLE = 'Subtitle';
}
