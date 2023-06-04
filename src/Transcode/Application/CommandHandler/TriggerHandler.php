<?php

declare(strict_types=1);

namespace App\Transcode\Application\CommandHandler;

use App\Transcode\Application\Command\Trigger;
use App\Transcode\Application\Service\TranscodeService;
use App\Transcode\Domain\Repository\TranscodeRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

//class TriggerHandler extends AsMessageHandler
readonly class TriggerHandler
{
    public function __construct(
        private TranscodeRepository $transcodeRepository,
        private TranscodeService    $transcodeService,
    )
    {
//        parent::__construct();
    }

    public function __invoke(Trigger $command): void
    {
        $transcode = $this->transcodeRepository->findById($command->id);
        $this->transcodeService->hlsTranscode($transcode);

        $transcode->setTranscodingProgress(100);
        $this->transcodeRepository->save($transcode);
    }
}
