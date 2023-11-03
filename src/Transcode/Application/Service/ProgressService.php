<?php

declare(strict_types=1);

namespace App\Transcode\Application\Service;

use App\Security\Application\Service\SecurityService;
use App\Transcode\Domain\Model\Transcode;
use App\Transcode\Domain\Repository\TranscodeRepository;

final readonly class ProgressService
{
    public function __construct(
        private TranscodeRepository $transcodeRepository,
        private SecurityService     $securityService,
    )
    {
    }

    public function updateProgressForAll(): void
    {
        $transcodes = $this->transcodeRepository->findAllByUser($this->securityService->getCurrentUser());

        foreach ($transcodes as $transcode) {
            $this->updateProgress($transcode);
        }
    }

    public function updateProgress(Transcode $transcode): void
    {
        if ($transcode->getTranscodingProgress() === 100) {
            return;
        }

        $sessionName = 'orbit_live_' . $transcode->getRandSubTargetPath();

        $createDetachedSession = "sudo tmux capture-pane -t $sessionName -pS -";
        $shellOutput = shell_exec($createDetachedSession);

        if ($shellOutput === null) {
            $transcode->setTranscodingProgress(100);
            $this->transcodeRepository->save($transcode);

            return;
        }

        $currentSeconds = null;
        $pattern = '/time=(\d{2}:\d{2}:\d{2})/';
        if (preg_match($pattern, $shellOutput, $matches)) {
            $timeParts = explode(':', $matches[1]);
            $currentSeconds = ((int) $timeParts[0] * 3600) + ((int) $timeParts[1] * 60) + (int) $timeParts[2];
        }

        if ($currentSeconds !== null) {
            $filePath = escapeshellarg($transcode->getFilePath());
            $videoTotalLengthSeconds = shell_exec("ffprobe -i $filePath -show_entries format=duration -v quiet -of csv='p=0'");

            $percentage = (int) round(($currentSeconds / $videoTotalLengthSeconds) * 100);
            $transcode->setTranscodingProgress($percentage);
            $this->transcodeRepository->save($transcode);
        }
    }
}
