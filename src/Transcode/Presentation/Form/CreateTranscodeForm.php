<?php

declare(strict_types=1);

namespace App\Transcode\Presentation\Form;

use App\Transcode\Application\Command\CreateTranscode;
use App\Transcode\Application\Service\TranscodeService;
use App\Transcode\Domain\Enum\Format;
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
        private readonly TranscodeService         $transcodeService,
        private readonly RepresentationRepository $representationRepository
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', ChoiceType::class, [
            'label' => false,
            'choice_label' => 'fileName',
            'choice_value' => 'filePath',
            'choices' => $this->transcodeService->listAvailableVideos(),
        ]);

        $builder->add('format', ChoiceType::class, [
            'label' => false,
            'choices' => Format::getFormats(),
        ]);

        $builder->add('representations', EntityType::class, [
            'label' => false,
            'class' => Representation::class,
            'choice_label' => 'name',
            'choices' => $this->representationRepository->findAll(),
            'multiple' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateTranscode::class,
        ]);
    }
}
