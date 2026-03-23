<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $requirePassword = (bool) $options['require_password'];

        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => $requirePassword ? 'Password' : 'New password',
                'mapped' => false,
                'required' => $requirePassword,
                'empty_data' => '',
                'help' => $requirePassword ? 'Set the initial password for this user.' : 'Leave blank to keep the current password.',
                'constraints' => array_filter([
                    $requirePassword ? new NotBlank() : null,
                    new Length(min: 8, minMessage: 'Password must be at least {{ limit }} characters long.'),
                ]),
            ])
            ->add('isAdmin', CheckboxType::class, [
                'label' => 'Administrator',
                'mapped' => false,
                'required' => false,
                'help' => 'Administrators can access the dashboard and manage users and firmware.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'require_password' => true,
        ]);

        $resolver->setAllowedTypes('require_password', 'bool');
    }
}
