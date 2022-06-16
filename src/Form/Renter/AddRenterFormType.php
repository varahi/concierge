<?php

namespace App\Form\Renter;

use App\Entity\Materials;
use App\Entity\Renter;
use App\Entity\Housing;
use App\Entity\Services;
use App\Entity\Prestation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class AddRenterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'lastName',
                null,
                [
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Name of Tenant'
                    ],
                    'label' => 'Name of Tenant',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'firstName',
                null,
                [
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'insert information'
                    ],
                    'label' => 'First Name of Tenant',
                ]
            )
            ->add(
                'address',
                TextType::class,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Billing address'
                    ],
                    'label' => 'Billing address',
                ]
            )
            ->add('zip', TextType::class, [
                'required' => false,
                'attr' => [
                   // 'class' => 'input-cp',
                    'placeholder' => 'Code postal'
                ],
                'label' => 'Code postal'
            ])
            ->add(
                'city',
                null,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'City'
                    ],
                    'label' => 'City',
                ]
            )
            ->add(
                'telephone',
                null,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Phone',
                        'class' => 'inputmask mask-phone',
                    ],
                    'label' => 'Phone',
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'form.email'
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
                    'attr' => [
                        //'placeholder' => 'form.numAdults',
                        //'class' => 'int-field'
                    ],
                    'label' => 'form.numAdults',
                ]
            )
            ->add(
                'numChildren',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'form.numChildren',
                ]
            )
            ->add(
                'numLittleChildren',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'form.numLittleChildren',
                ]
            )

            /*
            ->add('note', TextareaType::class, [
                'required' => false,
                'attr' => [
                    //'placeholder' => 'form.note',
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
            ->add('services', EntityType::class, [
                'required' => false,
                'class' => Services::class,
                'choice_label' => 'selector',
                //'label'     => 'form.addService',
                'label'     => 'Services',
                'expanded'  => false,
                'multiple'  => true,
                'attr' => [
                    'class' => 'input-prestation'
                ],
            ])
            ->add('prestations', EntityType::class, [
                'required' => false,
                'class' => Prestation::class,
                'choice_label' => 'selector',
                'label'     => 'Prestations',
                //'label' => false,
                'expanded'  => false,
                'multiple'  => true,
                'attr' => [
                    'class' => 'input-prestation'
                ],
            ])
            */

            /*
            ->add('startHour', TimeType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Arriving time'
            ])
            ->add('endHour', TimeType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Departure time'
            ])
            */

            /*
            ->add('materials', EntityType::class, [
                'required' => false,
                'class' => Materials::class,
                'by_reference' => false,
                'choice_label' => 'selector',
                'label'     => 'Materials',
                'expanded'  => false,
                'multiple'  => true,
                'attr' => [
                    'class' => 'input-prestation'
                ],
            ])
            ->add('startDate', DateType::class, [
                'label'     => 'Arrival date',
                'required' => true,
                'widget' => 'single_text',
                //'format' => 'MM/DD/yyyy',
                'format' => 'yyyy-MM-dd',
                'html5' => false,
                //'input'  => 'datetime_immutable',
                'attr' => [
                    'class' => 'date-start'
                ]
            ])
            ->add('endDate', DateType::class, [
                'label'     => 'Date of deparure',
                'required' => true,
                'widget' => 'single_text',
                //'format' => 'MM/DD/yyyy',
                'format' => 'yyyy-MM-dd',
                'html5' => false,
                //'input'  => 'datetime_immutable',
                'attr' => [
                    'class' => 'date-end'
                ]
            ])
            ->add('startHour', TimeType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Arriving time'
            ])
            ->add('endHour', TimeType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Departure time'
            ])
            */
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Renter::class,
        ]);
    }
}
