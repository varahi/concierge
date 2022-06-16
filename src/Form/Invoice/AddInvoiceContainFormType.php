<?php

namespace App\Form\Invoice;

use App\Entity\Housing;
use App\Entity\InvoiceContain;
use App\Entity\Prestation;
use App\Entity\Services;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddInvoiceContainFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$invoiceId = $options['invoiceId'];
        $builder

            /*
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Prestation',
                'translation_domain' => 'messages',
            ])
            */

            ->add('prestation', EntityType::class, [
                'class' => Prestation::class,
                'multiple'  => false,
                'expanded'  => false,
                //'by_reference' => false,
                'choice_label' => 'selector',
                'label' => 'Prestation',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            /*
            ->add('service', EntityType::class, [
                'class' => Prestation::class,
                'multiple'  => false,
                'expanded'  => false,
                //'by_reference' => false,
                'choice_label' => 'selector',
                'label' => 'Service',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            */
            ->add(
                'quantity',
                IntegerType::class,
                [
                    'required' => false,
                    'label' => 'Quantity',
                    //'empty_data' => '1',
                    'translation_domain' => 'messages',
                ]
            )
            /*
            ->add('price', MoneyType::class, [
                'required' => true,
                'label' => 'Prix',
                'translation_domain' => 'messages',
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
            'data_class' => InvoiceContain::class,
        ]);
        //$resolver->setRequired([
        //    'invoiceId',
        //]);
    }
}
