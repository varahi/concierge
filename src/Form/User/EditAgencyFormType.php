<?php

namespace App\Form\User;

use App\Entity\Housing;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class EditAgencyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'company',
                null,
                [
                    'required' => false,
                    'label' => 'form.company',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'address',
                null,
                [
                    'required' => false,
                    'label' => 'Billing address',
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
                ]
            )
            ->add(
                'city',
                null,
                [
                    'required' => false,
                    'label' => 'form.city',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => true,
                    'attr' => [
                        //'placeholder' => 'form.placeholder.email'
                    ],
                    'label' => 'form.email',
                    'constraints' => [
                        new Email(['message' => 'Please enter a valid email address.'])
                    ]
                ]
            )
            ->add('agencyApartments', EntityType::class, [
                'class' => Housing::class,
                'placeholder' => '',
                'choice_label' => 'name',
                'label'     => 'Apartment',
                'expanded'  => false,
                'multiple'  => true,
                'by_reference' => false,
                'attr' => [
                    'class' => 'input-select-multiple'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('appt')
                        ->orderBy('appt.name', 'ASC');
                },
            ])
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
            ->add('telephoneFullNumber', HiddenType::class, [
                'required' => false,
            ])
            ->add('telephone2FullNumber', HiddenType::class, [
                'required' => false,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
