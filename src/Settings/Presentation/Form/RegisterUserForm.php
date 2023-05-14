<?php

declare(strict_types=1);

namespace App\Settings\Presentation\Form;

use App\Security\Application\Command\RegisterUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RegisterUserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('username', TextType::class, [
            'label' => false,
            'required' => true,
        ]);

        $builder->add('password', PasswordType::class, [
            'label' => false,
            'required' => true,
        ]);

        $builder->add('isAdmin', CheckboxType::class, [
            'label' => false,
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegisterUser::class,
        ]);
    }
}
