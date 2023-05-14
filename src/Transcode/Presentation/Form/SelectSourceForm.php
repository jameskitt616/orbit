<?php

declare(strict_types=1);

namespace App\Transcode\Presentation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class SelectSourceForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('filePath', TextType::class, [
            'label' => false,
            'required' => false,
        ]);
    }
}
