<?php

namespace App\Form\Housing;

use App\Entity\Housing;
use App\Entity\Packs;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Image;

class AddApartmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'multiple'  => false,
                'expanded'  => false,
                'by_reference' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :roles')
                        ->andWhere('u.isVerified = true')
                        ->setParameter('roles', '%"'.'ROLE_OWNER'.'"%')
                        ->orderBy('u.lastName', 'ASC');
                },
                'label' => 'form.company',
                //'choice_label' => 'contactPerson',
            ])
            ->add('employer', EntityType::class, [
                'class' => User::class,
                'multiple'  => false,
                'expanded'  => false,
                'by_reference' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :roles')
                        ->andWhere('u.isVerified = 1')
                        ->setParameter('roles', '%"'.'ROLE_EMPLOYER'.'"%')
                        ->orderBy('u.username', 'ASC');
                },
                'label' => 'Employer assigned',
                //'choice_label' => 'contactPerson',
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

            /*
            ->add(
                'renter',
                null,
                [
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'form.company'
                    ],
                    'label' => 'form.company',
                    'translation_domain' => 'messages',
                ]
            )
            */

            /*
            ->add(
                'name',
                null,
                [
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Housing title',
                        'disabled' => true,
                        'readOnly' => true
                    ],
                    'label' => 'Housing title',
                    'translation_domain' => 'messages',
                ]
            )
            */
            ->add(
                'address',
                TextType::class,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Address'
                    ],
                    'label' => 'Address',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'zip',
                TextType::class,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Code postal'
                    ],
                    'label' => 'Code postal',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'city',
                null,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'City'
                    ],
                    'label' => 'City',
                    'translation_domain' => 'messages',
                ]
            )
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
                    'required' => true,
                    'attr' => [
                        // 'placeholder' => 'Quantity'
                    ],
                    'label' => 'Number of rooms',
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Housing::class,
        ]);
    }
}
