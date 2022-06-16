<?php

namespace App\Form\User;

use App\Entity\Housing;
use App\Entity\User;
use App\Entity\Task;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class EditEmployerFormType extends AbstractType
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
                        'placeholder' => 'form.lastName'
                    ],
                    'label' => 'form.lastName',
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
                    'label' => 'form.firstName',
                ]
            )
            /*
            ->add(
                'email',
                EmailType::class,
                [
                'required' => false,
                'attr' => [
                    'placeholder' => 'form.email',
                    'disabled' => true,
                    'readOnly' => true
                ],
                'label' => false,
                'constraints' => [
                    new Email(['message' => 'Please enter a valid email address.'])
                ]
            ])
            */
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
            ->add('employerAppts', EntityType::class, [
                'required' => false,
                'class' => Housing::class,
                'choice_label' => 'name',
                'label'     => false,
                'expanded'  => false,
                'multiple'  => true,
                'by_reference' => false,
                'attr' => array('class' => 'hidden'),
            ])
            /*
            ->add('task', EntityType::class, [
                'class' => Task::class,
                'choice_label' => 'selector',
                'label'     => false,
                'expanded'  => false,
                'multiple'  => true,
                'attr' => array('class' => 'hidden'),
            ])
            */
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'required' => false,
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
            ->add('color', ColorType::class, [
                'required' => false,
                'html5' => true,
                'label' => 'Choose Color',
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
