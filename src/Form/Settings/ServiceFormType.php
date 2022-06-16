<?php

namespace App\Form\Settings;

use App\Entity\Services;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'input-select',
                    'placeholder' => 'Laverie 15kg'
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Service name'
            ])
            ->add('price', MoneyType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'input-select',
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Price'
            ])
            ->add(
                'isRenter',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'Renter',
                ]
            )
            ->add(
                'isOwner',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'Owner',
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Services::class,
        ]);
    }
}
