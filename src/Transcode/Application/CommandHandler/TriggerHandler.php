<?php

declare(strict_types=1);

namespace App\Transcode\Application\CommandHandler;

use App\Transcode\Application\Command\Trigger;
use App\Transcode\Application\Service\TranscodeService;
use App\Transcode\Domain\Repository\TranscodeRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class TriggerHandler extends AsMessageHandler
{
    public function __construct(
        private readonly TranscodeRepository $transcodeRepository,
        private readonly TranscodeService    $transcodeService,
    )
    {
        parent::__construct();
    }

    public function __invoke(Trigger $command): void
    {
        $transcode = $this->transcodeRepository->findById($command->id);
        $this->transcodeService->transcode($transcode);
    }
}
