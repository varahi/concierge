<?php

namespace App\Form\Renter;

use App\Entity\Renter;
use App\Form\Invoice\InvoiceParamsFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class CalendarFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $invoice = $options['invoice'];
        $builder
            ->add(
                'lastName',
                null,
                [
                    'required' => false,
                    'label' => 'form.lastName',
                    'translation_domain' => 'messages',
                    'empty_data' => '',
                    'disabled' => true,
                    //'readonly' => true,
                ]
            )
            ->add(
                'firstName',
                null,
                [
                    'required' => false,
                    'label' => 'form.firstName',
                    'empty_data' => '',
                    'disabled' => true,
                ]
            )
            ->add(
                'address',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'Address',
                ]
            )
            ->add('zip', TextType::class, [
                'required' => false,
                'attr' => [
                    //'class' => 'input-cp',
                    'placeholder' => 'Code postal'
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Code postal'
            ])
            ->add(
                'city',
                null,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'form.city'
                    ],
                    'label' => 'form.city',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'telephone',
                TelType::class,
                [
                    'required' => false,
                    'attr' => [
                        'class' => 'inputmask mask-phone',
                    ],
                    'label' => 'Phone',
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => false,
                    'disabled' => true,
                    'attr' => [
                        'placeholder' => 'form.placeholder.email'
                    ],
                    'label' => 'form.email',
                    'constraints' => [
                        new Email(['message' => 'Please enter a valid email address.'])
                    ]
                ]
            )
            ->add(
                'numAdults',
                TextType::class,
                [
                    'required' => false,
                    'label' => false,
                ]
            )
            ->add(
                'numChildren',
                TextType::class,
                [
                    'required' => false,
                    'label' => false,
                ]
            )
            ->add(
                'numLittleChildren',
                TextType::class,
                [
                    'required' => false,
                    'label' => false,
                ]
            )
            /*
            ->add('note', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'form.note',
                    'class' => 'description'
                ],
                'label' => 'form.note',
                'constraints' => [
                    new Length([
                        'max' => 200,
                        'maxMessage' => 'Your notification can only be {{ limit }} characters long.'
                    ])
                ]
            ])
            */
            /*
            ->add('invoices', CollectionType::class, [
                'entry_type' => InvoiceParamsFormType::class,
                'entry_options' => [
                    'label' => false,
                    'required' => false,
                ],
                'required' => false,
                'label' => false,
            ])
            */
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Renter::class,
        ]);
        $resolver->setRequired([
            'invoice',
        ]);
    }
}
