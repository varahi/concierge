<?php

namespace App\Form\Settings;

use App\Entity\Materials;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MaterialFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'input-select',
                    'placeholder' => 'Chaise'
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Contain'
            ])
            ->add(
                'quantity',
                IntegerType::class,
                [
                    'required' => true,
                    'attr' => [
                       // 'placeholder' => 'Quantity'
                    ],
                    'label' => 'Quantity',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('price', MoneyType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'input-select',
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Day price'
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

            /*
            ->add('dayPrice', MoneyType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'input-select',
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Day price'
            ])
            ->add('weekPrice', MoneyType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'input-select',
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Week price'
            ])
            */

            /*
            ->add('stock', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'input-select',
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Stock'
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
            'data_class' => Materials::class,
        ]);
    }
}
