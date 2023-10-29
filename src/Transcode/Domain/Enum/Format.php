<?php

declare(strict_types=1);

namespace App\Transcode\Domain\Enum;

enum Format: string
{
    case x264 = 'x264';
    case HEVC = 'HEVC (x265)';
    case VP9 = 'VP9';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    public static function getFormats(): array
    {
        return array_combine(self::values(), self::names());
    }
}
