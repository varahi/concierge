<?php

namespace App\Form\Invoice;

use App\Entity\Invoice;
use App\Entity\InvoiceContain;
use App\Entity\Services;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceContainFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userId = $options['userId'];
        $builder
            ->add('number', TextType::class, [
                'required' => true,
                'attr' => [
                    //'rows' => 5,
                    'class' => 'input-select',
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Number :'
            ])

            /*
            ->add(
                'quantity',
                IntegerType::class,
                [
                    'required' => true,
                    'label' => 'Quantity',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('service', EntityType::class, [
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
            */

            /*
            ->add('invoice', HiddenType::class, [
                'required' => false,
                'attr' => [
                    'value' => $invoiceId,
                ]
            ])
            */
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
        $resolver->setRequired([
            'userId',
        ]);
    }
}
