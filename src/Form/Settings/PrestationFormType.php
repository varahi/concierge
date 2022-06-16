<?php

namespace App\Form\Settings;

use App\Entity\Prestation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrestationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('name')
            //->add('price')
            ->add(
                'name',
                TextType::class,
                [
                    'required' => true,
                    'attr' => [
                        //'placeholder' => 'Housework'
                    ],
                    'label' => 'Name of the service',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'shortName',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'Short name service',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'price',
                MoneyType::class,
                [
                    'required' => true,
                    'attr' => [
                        'placeholder' => ''
                    ],
                    'label' => 'Price',
                ]
            )
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
            ->add(
                'isTask',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'Task',
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Prestation::class,
        ]);
    }
}
