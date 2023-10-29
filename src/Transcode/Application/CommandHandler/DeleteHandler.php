<?php

declare(strict_types=1);

namespace App\Transcode\Application\CommandHandler;

use App\Transcode\Application\Command\Delete;
use App\Transcode\Domain\Repository\TranscodeRepository;

readonly class DeleteHandler
{
    public function __construct(
        private TranscodeRepository $transcodeRepository,
    )
    {
    }

    public function __invoke(Delete $command): void
    {
        $transcode = $command->transcode;

        $randSubTargetPath = $transcode->getRandSubTargetPath();
        if (!empty($randSubTargetPath)) {
            $path = $_ENV['TRANSCODE_PATH'] . '/' . $randSubTargetPath;
            shell_exec("rm -rf $path");
        }

        $this->transcodeRepository->delete($transcode);
    }
}
