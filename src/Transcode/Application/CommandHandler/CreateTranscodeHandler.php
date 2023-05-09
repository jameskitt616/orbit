<?php

declare(strict_types=1);

namespace App\Transcode\Application\CommandHandler;

use App\Transcode\Application\Command\CreateTranscode;
use App\Transcode\Application\Command\TriggerTranscode;
use App\Transcode\Domain\Model\Transcode;
use App\Transcode\Domain\Repository\TranscodeRepository;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class CreateTranscodeHandler
{
    public function __construct(
        private TranscodeRepository $transcodeRepository,
        private MessageBusInterface $messageBus
    )
    {
    }

    public function __invoke(CreateTranscode $command): void
    {
        $file = $command->file;
        $currentUser = $command->currentUser;
        $transcode = new Transcode($file->fileName, $file->filePath, $currentUser, $command->format, $command->representations);

        $videoPropertyAudio = $command->videoPropertyAudio;
        if ($videoPropertyAudio !== null) {
            $transcode->setAudioTrackNumber((int) $videoPropertyAudio->streamNumber);
            $transcode->setAudioTrackNumberTitle($videoPropertyAudio->streamName);
        }

        $videoPropertySubtitle = $command->videoPropertySubtitle;
        if ($videoPropertySubtitle !== null) {
            $transcode->setSubtitleNumber((int) $videoPropertySubtitle->streamNumber);
            $transcode->setSubtitleNumberTitle($videoPropertySubtitle->streamName);
        }

        $this->transcodeRepository->save($transcode);

        $command->transcode = $transcode;

        $this->messageBus->dispatch(new TriggerTranscode($transcode->getId()));
    }
}
