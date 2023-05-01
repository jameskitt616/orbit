<?php

declare(strict_types=1);

namespace App\Transcode\Application\CommandHandler;

use App\Transcode\Application\Command\CreateTranscode;
use App\Transcode\Application\Service\TranscodeService;
use App\Transcode\Domain\Entity\Transcode;
use App\Transcode\Domain\Repository\TranscodeRepository;

readonly class CreateTranscodeHandler
{
    public function __construct(
        private TranscodeRepository $transcodeRepository,
        private TranscodeService    $transcodeService,
    )
    {
    }

    public function __invoke(CreateTranscode $command): void
    {
        $file = $command->file;
        $currentUser = $command->currentUser;
        $transcode = new Transcode($file->fileName, $file->filePath, $currentUser);

        $this->transcodeRepository->save($transcode);

        $this->transcodeService->transcode($transcode);

        $command->transcode = $transcode;
    }
}
