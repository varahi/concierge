<?php

namespace App\Form\Task;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class TaskNoteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('note', TextareaType::class, [
                'required' => false,
                'label' => false,
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
