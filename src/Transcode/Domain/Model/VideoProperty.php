<?php

declare(strict_types=1);

namespace App\Transcode\Domain\Model;

class VideoProperty
{
    public string $streamNumber;
    public string $streamName;

    public function __construct(string $streamNumber, string $streamName)
    {
        $this->streamNumber = $streamNumber;
        $this->streamName = $streamName;
    }
}
