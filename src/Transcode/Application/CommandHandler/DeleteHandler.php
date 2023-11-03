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
        $sessionName = 'orbit_live_' . $transcode->getRandSubTargetPath();

        $createDetachedSession = "sudo tmux kill-session -t $sessionName";
        shell_exec($createDetachedSession);

        $this->transcodeRepository->delete($transcode);
    }
}
