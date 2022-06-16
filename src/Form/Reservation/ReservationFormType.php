<?php

namespace App\Form\Reservation;

use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ReservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'title',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'form.task_title',
                ]
            )
            ->add('housing')
            ->add('user', EntityType::class, [
                'class' => User::class,
                'multiple'  => false,
                'expanded'  => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :roles')
                        ->andWhere('u.isVerified = 1')
                        ->setParameter('roles', '%"'.'ROLE_OWNER'.'"%')
                        ->orderBy('u.username', 'ASC');
                },
                'label' => 'form.assigned_whom',
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
