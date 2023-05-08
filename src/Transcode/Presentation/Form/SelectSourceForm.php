<?php

declare(strict_types=1);

namespace App\Transcode\Presentation\Form;

use App\Transcode\Application\Service\TranscodeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class SelectSourceForm extends AbstractType
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
    }
}
