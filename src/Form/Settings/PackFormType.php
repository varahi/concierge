<?php

namespace App\Form\Settings;

use App\Entity\Housing;
use App\Entity\Packs;
use App\Entity\Prestation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            /*
            ->add('total', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'input-select',
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Total'
            ])
            */
            /*
            ->add('contain', EntityType::class, [
                'class' => Prestation::class,
                'multiple'  => true,
                'expanded'  => false,
                'required' => true,
                'label' => 'Prestation',
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
        return 'form-std';
    }
}
