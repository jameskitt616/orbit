<?php

declare(strict_types=1);

namespace App\Transcode\Presentation\Form;

use App\Transcode\Application\Command\Create;
use App\Transcode\Application\Service\TranscodeService;
use App\Transcode\Domain\Enum\VideoFormat;
use App\Transcode\Domain\Enum\VideoProperty;
use App\Transcode\Domain\Model\File;
use App\Transcode\Domain\Model\Representation;
use App\Transcode\Domain\Repository\RepresentationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CreateTranscodeForm extends AbstractType
{
    public function __construct(
        private readonly RepresentationRepository $representationRepository,
        private readonly TranscodeService         $transcodeService,
        private readonly TranslatorInterface      $translator
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var File $file */
        $file = $options['file'];;
        $audioTracks = $this->transcodeService->getAvailableTracksByFilePathAndProperty($file->filePath, VideoProperty::AUDIO->value);
        $subtitles = $this->transcodeService->getAvailableTracksByFilePathAndProperty($file->filePath, VideoProperty::SUBTITLE->value);

        $builder->add('videoPropertyAudio', ChoiceType::class, [
            'label' => false,
            'choices' => $audioTracks,
            'choice_label' => 'streamName',
            'choice_value' => 'streamNumber',
        ]);

        $builder->add('videoPropertySubtitle', ChoiceType::class, [
            'label' => false,
            'required' => false,
            'choices' => $subtitles,
            'choice_label' => 'streamName',
            'choice_value' => 'streamNumber',
            'placeholder' => $this->translator->trans('live.stream.no_subtitles'),
        ]);

        $builder->add('format', ChoiceType::class, [
            'label' => false,
            'choices' => VideoFormat::getFormats(),
        ]);

        $builder->add('representation', EntityType::class, [
            'label' => false,
            'required' => false,
            'class' => Representation::class,
            'choice_label' => 'name',
            'choices' => $this->representationRepository->findAll(),
            'placeholder' => $this->translator->trans('live.stream.keep_original_resolution'),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Create::class,
            'file' => 'file',
        ]);
    }
}
