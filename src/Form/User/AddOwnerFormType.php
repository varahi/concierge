<?php

namespace App\Form\User;

use App\Entity\Task;
use App\Entity\Packs;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddOwnerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'company',
                null,
                [
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'form.company'
                    ],
                    'label' => false,
                    //'label' => 'form.lastName',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'lastName',
                null,
                [
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'form.lastName'
                    ],
                    'label' => false,
                    //'label' => 'form.lastName',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'firstName',
                null,
                [
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'form.firstName'
                    ],
                    'label' => false,
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
                    'label' => false,
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'city',
                null,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'form.city'
                    ],
                    'label' => false,
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
                    'label' => false,
                ]
            )
            ->add(
                'telephone',
                null,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'form.placeholder.telephone',
                        'class' => 'inputmask mask-phone',
                    ],
                    'label' => 'Phone',
                ]
            )
            ->add(
                'telephone2',
                null,
                [
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'form.placeholder.telephone',
                        'class' => 'inputmask mask-phone',
                    ],
                    'label' => 'Phone2',
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'form.placeholder.email'
                    ],
                    'label' => 'form.email',
                    'constraints' => [
                        new Email(['message' => 'Please enter a valid email address.'])
                    ]
                ]
            )
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'type' => PasswordType::class,
                'mapped' => false,
                'attr' => [
                    //'autocomplete' => 'new-password',
                    'placeholder' => 'form.password'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Confirm Password'],
                'invalid_message' => 'Your password does not match the confirmation.'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
