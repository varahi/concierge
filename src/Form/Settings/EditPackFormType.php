<?php

namespace App\Form\Settings;

use App\Entity\Packs;
use App\Entity\Prestation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditPackFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'input-select',
                    'placeholder' => 'Pack name'
                ],
                'label' => false
            ])
            ->add(
                'total',
                TextType::class,
                [
                    'required' => false,
                    'attr' => [
                        'disabled' => true,
                    ],
                ]
            )
            /*
            ->add('contain', EntityType::class, [
                'class' => Prestation::class,
                'multiple'  => true,
                'expanded'  => false,
                'required' => true,
                //'by_reference' => false,
                'label' => 'List of housing',
            ])
            */
            /*
            ->add('price', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'input-select',
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Price'
            ])
            */
            /*
            ->add('quantity', IntegerType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'input-select',
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Quantity'
            ])
            */
            /*
            ->add('submit', SubmitType::class, [
                'label' => 'form.submit',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'input-submit',
                ],
            ])
            */
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Packs::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pack_form';
    }
}
