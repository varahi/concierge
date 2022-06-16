<?php

namespace App\Form\Invoice;

use App\Entity\Housing;
use App\Entity\Invoice;
use App\Entity\Room;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class InvoicePageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userId = $options['userId'];
        $builder
            //->add('created')
            //->add('number')
            //->add('date')
            //->add('price')
            //->add('total')
            //->add('renter')
            //->add('owner')
            ->add('number', TextType::class, [
                'required' => true,
                'attr' => [
                    //'rows' => 5,
                    'class' => 'input-select',
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Number :'
            ])
            ->add('date', DateType::class, [
                'label'     => 'Date :',
                'required' => true,
                'widget' => 'single_text',
                //'format' => 'MM/DD/yyyy',
                'format' => 'yyyy-MM-dd',
                'html5' => false,
                //'input'  => 'datetime_immutable',
                'attr' => [
                    'class' => 'date'
                ]
            ])
            ->add('apartment', EntityType::class, [
                'class' => Housing::class,
                'multiple'  => false,
                'expanded'  => false,
                'by_reference' => false,
                'query_builder' => function (EntityRepository $er) use ($userId) {
                    return $er->createQueryBuilder('u')
                        ->where('u.user =' . $userId)
                        ->orderBy('u.id', 'ASC');
                },
                'label' => 'Choose apartment',
                'choice_label' => 'selector',
            ])
            ->add('total', MoneyType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'input-select',
                ],
                'label_attr' => [
                    'class' => 'label-class'
                ],
                'label' => 'Total'
            ])
            /*
            ->add('owner', HiddenType::class, [
                'required' => false,
                'attr' => [
                    'value' => $userId,
                ]
            ])
            */
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
        $resolver->setRequired([
            'userId',
        ]);
    }
}
