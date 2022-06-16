<?php

namespace App\Form\Housing;

use App\Entity\Elements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ApartmentElementsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('garage')
            ->add('parking')
            ->add('basement')
            ->add('garden')
            ->add('basementParking')
            ->add('parentalSuite')
            ->add('furniture')
            ->add('poll')
            ->add('teracce')
            ->add('mezzanine')
            ->add('storage')
            ->add('veranda')
            ->add('unfurnished')
            ->add('bathroom')
            ->add('separateWc')
            ->add('wifi')
            ->add('elevator')
            ->add(
                'other',
                null,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Other'
                    ],
                    'label' => false,
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'note',
                TextType::class,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Enter a note'
                    ],
                    'label' => 'Note',
                    'translation_domain' => 'messages',
                ]
            )
            //->add('submit', SubmitType::class, [
            //    'label' => 'form.submit'
            //])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Elements::class,
        ]);
    }
}
