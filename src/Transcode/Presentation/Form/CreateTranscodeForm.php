<?php

declare(strict_types=1);

namespace App\Transcode\Presentation\Form;

use App\Transcode\Application\Command\CreateTranscode;
use App\Transcode\Application\Service\TranscodeService;
use App\Transcode\Domain\Enum\Format;
use App\Transcode\Domain\Enum\VideoProperty;
use App\Transcode\Domain\Model\File;
use App\Transcode\Domain\Model\Representation;
use App\Transcode\Domain\Repository\RepresentationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CreateTranscodeForm extends AbstractType
{
    public function __construct(
        private readonly RepresentationRepository $representationRepository,
        private readonly TranscodeService         $transcodeService,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var File $file */
        $file = $options['file'];;
        $audioTracks = $this->transcodeService->getAvailableTracksByFilePathAndVideoProperty($file->filePath, VideoProperty::AUDIO->value);
        $subtitles = $this->transcodeService->getAvailableTracksByFilePathAndVideoProperty($file->filePath, VideoProperty::SUBTITLE->value);

        $builder->add('streamNumber', ChoiceType::class, [
            'label' => false,
            'choices' => $audioTracks,
            'multiple' => false,
            'expanded' => true,
            'choice_attr' => function ($choice, $key, $value) {
                return ['class' => 'ml-4 mr-1'];
            },
        ]);

        $builder->add('subtitleNumber', ChoiceType::class, [
            'label' => false,
            'choices' => $subtitles,
            'multiple' => false,
            'expanded' => true,
            'choice_attr' => function ($choice, $key, $value) {
                return ['class' => 'ml-4 mr-1'];
            },
        ]);

        $builder->add('format', ChoiceType::class, [
            'label' => false,
            'choices' => Format::getFormats(),
            'multiple' => false,
            'expanded' => true,
            'choice_attr' => function ($choice, $key, $value) {
                return ['class' => 'ml-4 mr-1'];
            },
        ]);

        $builder->add('representations', EntityType::class, [
            'label' => false,
            'class' => Representation::class,
            'choice_label' => 'name',
            'choices' => $this->representationRepository->findAll(),
            'multiple' => true,
            'expanded' => true,
            //            'choice_attr' => function ($choice, $key, $value) {
            //                return ['style' => ''];
            ////                return ['class' => 'ml-4 mr-1'];
            //            },
            //            'row_attr' => ['class' => 'asd'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateTranscode::class,
            'file' => 'file'
        ]);
    }
}
