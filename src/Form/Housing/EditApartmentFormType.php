<?php

namespace App\Form\Housing;

use App\Entity\Housing;
use App\Entity\Renter;
use App\Entity\Room;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;

class EditApartmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /*
            ->add('name', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'input-select',
                    //'disabled' => true,
                    'readOnly' => true
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Housing title'
            ])
            */
            ->add(
                'address',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'Address',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('zip', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'input-cp',
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Code postal'
            ])
            ->add(
                'city',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'City',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('user', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :roles')
                        ->andWhere('u.isVerified = 1')
                        ->setParameter('roles', '%"'.'ROLE_OWNER'.'"%')
                        ->orderBy('u.username', 'ASC');
                },
                //'choice_label' => 'username',
                'multiple'  => false,
            ])
            ->add('agency', EntityType::class, [
                'required' => false,
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :roles')
                        ->andWhere('u.isVerified = 1')
                        ->setParameter('roles', '%"'.'ROLE_AGENCY'.'"%')
                        ->orderBy('u.username', 'ASC');
                },
                'multiple'  => true,
            ])
            ->add('note', TextareaType::class, [
                'required' => false,
                'label' => 'Note',
                'attr' => [
                    'class' => 'input-select',
                    'maxlength' => 500
                ],
                'constraints' => [
                    new Length([
                        'max' => 500,
                        'maxMessage' => 'Your notification can only be {{ limit }} characters long.'
                    ])
                ]
            ])
            ->add('employer', EntityType::class, [
                'class' => User::class,
                'multiple'  => false,
                'expanded'  => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :roles')
                        ->andWhere('u.isVerified = 1')
                        ->setParameter('roles', '%"'.'ROLE_EMPLOYER'.'"%')
                        ->orderBy('u.username', 'ASC');
                },
                'label' => 'Employer',
                //'choice_label' => 'contactPerson',
            ])
            ->add(
                'residence',
                null,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Residence'
                    ],
                    'label' => false,
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'apartment',
                null,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Apartment'
                    ],
                    'label' => false,
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'logement',
                null,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Logement'
                    ],
                    'label' => false,
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'numRooms',
                IntegerType::class,
                [
                    'required' => false,
                    'attr' => [
                        // 'placeholder' => 'Quantity'
                    ],
                    'label' => 'Number of rooms',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'numBeds',
                IntegerType::class,
                [
                    'required' => false,
                    'label' => 'Number of beds',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('file', FileType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Image(['maxSize' => '1024k'])
                ],
                'label' => 'PDF',
                'translation_domain' => 'messages',
            ])

            /*
             ->add(
                'state',
                ChoiceType::class,
                [
                    'required' => true,
                    'label' => 'State',
                    'translation_domain' => 'messages',
                    'choices'  => [
                        'Please select' => null,
                        'Libérer' => 'Libérer',
                        'Occupé' => 'Occupé',
                    ],
                ]
            )
             */
            /*
            ->add('renter', EntityType::class, [
                'class' => Renter::class,
                'choice_label' => 'selector',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.isArchived = 0')
                        ->orderBy('u.id', 'ASC');
                },
                //'choice_label' => 'username',
                'multiple'  => false,
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
            'data_class' => Housing::class,
        ]);
    }
}
