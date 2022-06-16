<?php

namespace App\Form\Room;

use App\Entity\Housing;
use App\Entity\Room;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteRoomFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$apartmentId = $options['apartmentId'];
        $builder
            /*
            ->add('rooms', EntityType::class, [
                'class' => Room::class,
                'multiple'  => true,
                'expanded'  => false,
                'by_reference' => false,
                'query_builder' => function (EntityRepository $er) use ($apartmentId) {
                    return $er->createQueryBuilder('r')
                        ->where('r.apartment =' . $apartmentId)
                        ->orderBy('r.id', 'ASC');
                },
                'label' => 'Choose room',
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
        $resolver->setRequired([
            'apartmentId',
        ]);
    }
}
