<?php

namespace App\Form\Objet;

use App\Entity\Objet;
use App\Form\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\File;

class EditEquipmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'Names',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'state',
                ChoiceType::class,
                [
                    'required' => true,
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
                    //'attr' => [
                        //'placeholder' => 'Description'
                    //],
                    'label' => 'Description',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('documents', CollectionType::class, array(
                'entry_type'   		=> DocumentType::class,
                'prototype'			=> true,
                'allow_add'			=> true,
                'allow_delete'		=> true,
                'by_reference' 		=> false,
                'required'			=> false,
                'label'				=> false,
            ));
        /*
        ->add('filename', FileType::class, [
            'label' => 'Image',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'image/gif',
                        'image/jpeg',
                        'image/pjpeg',
                        'image/png',
                        'image/webp',
                        'image/vnd.wap.wbmp'
                    ],
                    'mimeTypesMessage' => 'Please upload a valid image document',
                ])
            ],
        ]);
        */
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Objet::class,
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
