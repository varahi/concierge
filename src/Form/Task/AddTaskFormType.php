<?php

namespace App\Form\Task;

use App\Entity\Housing;
use App\Entity\Prestation;
use App\Entity\Renter;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;

class AddTaskFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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
            ->add('users', EntityType::class, [
                'class' => User::class,
                'multiple'  => true,
                'expanded'  => false,
                'by_reference' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :roles')
                        ->andWhere('u.isVerified = 1')
                        ->setParameter('roles', '%"'.'ROLE_EMPLOYER'.'"%')
                        ->orderBy('u.username', 'ASC');
                },
                'label' => 'form.assigned_whom',
                'required' => false,
            ])
            ->add('housing', EntityType::class, [
                'class' => Housing::class,
                'multiple'  => false,
                'expanded'  => false,
                'label' => 'List of housing',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('appt')
                        ->orderBy('appt.name', 'ASC');
                },
            ])
            ->add('color', ColorType::class, [
                'required' => false,
                'html5' => true,
                'label' => 'Choose Color',
            ])
            ->add(
                'isEvent',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'Calendar',
                ]
            )
            /*
            ->add('prestations', EntityType::class, [
                'required' => false,
                'class' => Prestation::class,
                'choice_label' => 'selector',
                'label'     => 'Prestations',
                'expanded'  => false,
                'multiple'  => true,
                'attr' => [
                    'class' => 'input-prestation'
                ],
            ])
            */
            /*
            ->add('renter', EntityType::class, [
                'class' => Renter::class,
                'multiple'  => false,
                'expanded'  => false,
                'required' => true,
                //'by_reference' => false,
                'label' => 'Renter',
            ])
            */
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => false,
                'constraints' => [
                    //new NotBlank([
                    //    'message' => 'Your notification may not be blank.'
                    //]),
                    new Length([
                        'max' => 200,
                        'maxMessage' => 'Your notification can only be {{ limit }} characters long.'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
