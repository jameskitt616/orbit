<?php

declare(strict_types=1);

namespace App\Transcode\Presentation\Form;

use App\Transcode\Application\Command\CreateTranscode;
use App\Transcode\Application\Service\TranscodeService;
use App\Transcode\Domain\Model\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CreateTranscodeForm extends AbstractType
{
    public function __construct(
        private readonly TranscodeService $transcodeService,
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

//        $builder->add('resolution', ChoiceType::class, [
//            'choices' => [
//                'Main Statuses' => [
//                    'Yes' => 'stock_yes',
//                    'No' => 'stock_no',
//                ],
//                'Out of Stock Statuses' => [
//                    'Backordered' => 'stock_backordered',
//                    'Discontinued' => 'stock_discontinued',
//                ],
//            ],
//        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateTranscode::class,
        ]);
    }
}
