<?php

declare(strict_types=1);

namespace App\Transcode\Domain\Enum;

enum AudioFormat: string
{
    case AAC = 'AAC';
    case OPUS = 'OPUS';
    case MP3 = 'MP3';

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
