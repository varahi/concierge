<?php

namespace App\Controller\Admin;

use App\Entity\Calendar;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class CalendarCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Calendar::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Calendar')
            ->setEntityLabelInPlural('Calendar')
            ->setSearchFields(['title', 'user', 'task'])
            ->setDefaultSort(['id' => 'DESC']);
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('task'))
            ->add(EntityFilter::new('renter'))
            ->add(EntityFilter::new('reservations'))
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        //yield TextField::new('title');
        yield IntegerField::new('id')->setFormTypeOption('disabled', 'disabled');
        yield DateField::new('startDate');
        yield DateField::new('endDate');
        yield TimeField::new('startHour');
        yield TimeField::new('endHour');
        yield AssociationField::new('task')
            ->setFormTypeOptions([
                'by_reference' => false,
            ]);
        yield AssociationField::new('reservations')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnIndex();
        yield BooleanField::new('isArchived');
        yield BooleanField::new('isSingle');
        yield AssociationField::new('renter');
    }
}
