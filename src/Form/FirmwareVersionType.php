<?php

namespace App\Form;

use App\Entity\FirmwareVersion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FirmwareVersionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'help' => 'Examples: MMI Prime CIC, MMI Prime NBT, LCI NBT, LCI EVO.',
            ])
            ->add('systemVersion', null, [
                'label' => 'System version',
                'help' => 'Keep the original firmware string, usually starting with "v".',
            ])
            ->add('systemVersionAlt', null, [
                'label' => 'System version alt',
                'help' => 'Version used for matching customer input. If blank, it is derived from the system version.',
                'required' => false,
            ])
            ->add('link', null, [
                'label' => 'General Google Drive link',
            ])
            ->add('st', null, [
                'label' => 'ST firmware link',
                'required' => false,
            ])
            ->add('gd', null, [
                'label' => 'GD firmware link',
                'required' => false,
            ])
            ->add('latest', CheckboxType::class, [
                'required' => false,
                'help' => 'When enabled, other versions with the same name are automatically marked as not latest.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FirmwareVersion::class,
        ]);
    }
}
