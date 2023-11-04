<?php

declare(strict_types=1);

namespace App\Transcode\Application\CommandHandler;

use App\Transcode\Application\Command\Create;
use App\Transcode\Application\Service\TranscodeService;
use App\Transcode\Domain\Model\Transcode;
use App\Transcode\Domain\Repository\TranscodeRepository;

readonly class CreateHandler
{
    public function __construct(
        private TranscodeRepository $transcodeRepository,
        private TranscodeService    $transcodeService,
    )
    {
    }

    public function __invoke(Create $command): void
    {
        $file = $command->file;
        $currentUser = $command->currentUser;
        $transcode = new Transcode($file->fileName, $file->filePath, $currentUser, $command->format, $command->representation);

        $videoPropertyAudio = $command->videoPropertyAudio;
        if ($videoPropertyAudio !== null) {
            $audioTrackNumber = (int) $videoPropertyAudio->streamNumber;
            --$audioTrackNumber;
            $transcode->setAudioTrackNumber($audioTrackNumber);
            $transcode->setAudioTrackNumberTitle($videoPropertyAudio->streamName);
        }

        $videoPropertySubtitle = $command->videoPropertySubtitle;
        if ($videoPropertySubtitle !== null) {
            $subtitleNumber = (int) $videoPropertySubtitle->streamNumber;
            --$subtitleNumber;
            $transcode->setSubtitleNumber($subtitleNumber);
            $transcode->setSubtitleNumberTitle($videoPropertySubtitle->streamName);
        }

        $this->transcodeRepository->save($transcode);

        $command->transcode = $transcode;

        $this->transcodeService->transcode($transcode);
    }
}
