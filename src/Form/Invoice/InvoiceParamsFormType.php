<?php

namespace App\Form\Invoice;

use App\Entity\Invoice;
use App\Entity\Materials;
use App\Entity\Packs;
use App\Entity\Prestation;
use App\Entity\Services;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class InvoiceParamsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('services', EntityType::class, [
                'required' => false,
                'class' => Services::class,
                'choice_label' => 'selector',
                'label'     => 'Services',
                'expanded'  => false,
                'multiple'  => true,
                'attr' => [
                    'class' => 'input-prestation input-one'
                ],
            ])
            ->add(
                'services_qty',
                IntegerType::class,
                [
                    'required' => false,
                    'attr' => [
                        'class'=>'input-two'
                        // 'placeholder' => 'Quantity'
                    ],
                    'label' => 'Services_Quantity',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('prestations', EntityType::class, [
                'required' => false,
                'class' => Prestation::class,
                'choice_label' => 'selector',
                'label'     => 'Prestations',
                'expanded'  => false,
                'multiple'  => true,
                'attr' => [
                    'class' => 'input-prestation input-one'
                ],
            ])
            ->add(
                'prestations_qty',
                IntegerType::class,
                [
                    'required' => false,
                    'attr' => [
                        'class'=>'input-two'
                    ],
                    'label' => 'Prestations_Quantity',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('packs', EntityType::class, [
                'required' => false,
                'class' => Packs::class,
                'choice_label' => 'name',
                'label'     => 'Packs',
                'expanded'  => false,
                'multiple'  => true,
                'attr' => [
                    'class' => 'input-prestation'
                ],
            ])
            ->add(
                'packs_qty',
                IntegerType::class,
                [
                    'required' => false,
                    'label' => 'Packs_Quantity',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('materials', EntityType::class, [
                'required' => false,
                'class' => Materials::class,
                'choice_label' => 'selector',
                'label'     => 'Materials',
                'expanded'  => false,
                'multiple'  => true,
                'attr' => [
                    'class' => 'input-prestation'
                ],
            ])
            ->add(
                'materials_qty',
                IntegerType::class,
                [
                    'required' => false,
                    'label' => 'Materials_Quantity',
                    'translation_domain' => 'messages',
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
    }
}
