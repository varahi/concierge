<?php

namespace App\Controller\Admin;

use App\Entity\Renter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class RenterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Renter::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Renter')
            ->setEntityLabelInPlural('Locataire - Renter')
            ->setSearchFields(['firstName', 'lastName', 'address', 'zip', 'city', 'telephone', 'email'])
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('calendar'))
            ->add(EntityFilter::new('tasks'))
            ->add(EntityFilter::new('invoices'))
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id')->setFormTypeOption('disabled', 'disabled');
        yield TextField::new('firstName');
        yield TextField::new('lastName');
        yield TextareaField::new('address');
        yield TextField::new('zip')->hideOnIndex();
        yield TextField::new('city')->hideOnIndex();
        yield TextField::new('telephone');
        yield TextField::new('email');
        yield IntegerField::new('numChildren')->hideOnIndex();
        yield IntegerField::new('numLittleChildren')->hideOnIndex();
        yield IntegerField::new('numAdults')->hideOnIndex();
        yield BooleanField::new('isArchived');
        yield TextEditorField::new('note')->hideOnIndex();
        yield AssociationField::new('services')->hideOnIndex();
        yield AssociationField::new('completedServices')->hideOnIndex();
        yield AssociationField::new('prestations')->hideOnIndex();
        yield AssociationField::new('materials')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnIndex();
        yield AssociationField::new('tasks')->hideOnIndex();
        yield AssociationField::new('invoices')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnIndex();

        //yield DateField::new('startDate');
        //yield DateField::new('endDate');
        //yield TimeField::new('startHour')->hideOnIndex();
        //yield TimeField::new('endHour')->hideOnIndex();
        //yield AssociationField::new('housing')->hideOnIndex();
        //yield AssociationField::new('archivedHousing')->hideOnIndex();

        //yield AssociationField::new('calendar')
        //    ->setFormTypeOptions([
        //        'by_reference' => false,
        //    ])->hideOnIndex();
        //yield AssociationField::new('services')->hideOnIndex();
        //yield AssociationField::new('completedServices')->hideOnIndex();
    }
}
