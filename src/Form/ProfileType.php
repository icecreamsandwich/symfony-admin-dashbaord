<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'disabled' => true,
            ])
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Current password',
                'mapped' => false,
                'required' => false,
                'empty_data' => '',
                'help' => 'Enter this only when you want to change the password.',
            ])
            ->add('newPassword', PasswordType::class, [
                'label' => 'New password',
                'mapped' => false,
                'required' => false,
                'empty_data' => '',
                'help' => 'Leave blank to keep your current password.',
                'constraints' => [
                    new Length(min: 8, minMessage: 'Password must be at least {{ limit }} characters long.'),
                ],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Confirm new password',
                'mapped' => false,
                'required' => false,
                'empty_data' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
