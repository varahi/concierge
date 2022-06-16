<?php

namespace App\Form\Objet;

use App\Entity\Objet;
use App\Entity\Room;
use App\Form\DocumentType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddEquipmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $apartmentId = $options['apartmentId'];
        $builder
            ->add(
                'name',
                null,
                [
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Sofa'
                    ],
                    'label' => 'Furniture name',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'state',
                ChoiceType::class,
                [
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'State'
                    ],
                    'label' => 'State',
                    'translation_domain' => 'messages',
                    'choices'  => [
                        'Please select' => null,
                        'Neuf' => 'Neuf',
                        'Parfait état' => 'Parfait état',
                        'Bon état' => 'Bon état',
                        'Usé' => 'Usé'
                    ],
                ]
            )
            ->add(
                'quantity',
                IntegerType::class,
                [
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Quantity'
                    ],
                    'label' => 'Quantity',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Description'
                    ],
                    'label' => 'Description',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('room', EntityType::class, [
                'class' => Room::class,
                'multiple'  => false,
                'expanded'  => false,
                'by_reference' => false,
                'query_builder' => function (EntityRepository $er) use ($apartmentId) {
                    return $er->createQueryBuilder('r')
                        ->where('r.apartment =' . $apartmentId)
                        ->orderBy('r.id', 'ASC');
                },
                'label' => 'Choose room',
                'choice_label' => 'name',
            ])
            ->add('documents', CollectionType::class, array(
                'entry_type'   		=> DocumentType::class,
                'prototype'			=> true,
                'allow_add'			=> true,
                'allow_delete'		=> true,
                'by_reference' 		=> false,
                'required'			=> false,
                'label'				=> false,
            ));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Objet::class,
        ]);
        $resolver->setRequired([
            'apartmentId',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'folder';
    }
}
