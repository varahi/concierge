<?php

namespace App\Controller\Admin;

use App\Entity\Housing;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class HousingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Housing::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('adminpanel.apartment')
            ->setEntityLabelInPlural('adminpanel.apartment')
            ->setSearchFields(['number', 'name', 'address', 'zip', 'city'])
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('task'))
            ->add(EntityFilter::new('packs'))
            ->add(EntityFilter::new('user'))
            ->add(EntityFilter::new('rooms'))
            ->add(EntityFilter::new('invoices'))
            ->add(EntityFilter::new('reservations'))
            ;
    }


    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id')->setFormTypeOption('disabled', 'disabled');
        yield TextField::new('name');
        yield TextareaField::new('address');
        yield TextField::new('zip');
        yield TextField::new('city');
        yield BooleanField::new('isHidden');
        yield TextField::new('logement');
        yield AssociationField::new('task')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])
        ->hideOnIndex();
        yield AssociationField::new('user')->setLabel('Owner');
        yield AssociationField::new('employer')->setLabel('Employer');
        yield AssociationField::new('agency')->setLabel('Agency')->hideOnIndex();
        //yield AssociationField::new('renter')->setLabel('Renter');
        //yield AssociationField::new('employer')->setLabel('Employer');
        //yield AssociationField::new('archivedRenter')
        //    ->setFormTypeOptions([
        //        'by_reference' => false,
        //    ])->hideOnIndex()
        //;
        yield AssociationField::new('packs');
        // State -0 is free, 1 -is busy
        // yield IntegerField::new('state');
        yield TextField::new('state')->hideOnDetail();
        yield ChoiceField::new('state')->setChoices(
            [
                'Please select' => null,
                'Libérer' => 'Libérer',
                'Occupé' => 'Occupé',
            ]
        )->hideOnIndex();
        yield AssociationField::new('rooms')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnIndex();
        yield AssociationField::new('element');

        //yield ImageField::new('file')
        //    ->setBasePath('/uploads/files/')
        //    ->setLabel('File')
        //    ->onlyOnIndex();
        yield AssociationField::new('reservations')->hideOnIndex();
    }
}
