<?php

namespace App\Controller\Admin;

use App\Entity\Task;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Validator\Constraints\Date;

class TaskCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Task::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Task')
            ->setEntityLabelInPlural('Task')
            ->setSearchFields(['title', 'description'])
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('users'))
            ->add(EntityFilter::new('housing'))
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id')->setFormTypeOption('disabled', 'disabled');
        yield TextField::new('title');
        yield TextareaField::new('description')->hideOnIndex();
        //yield IntegerField::new('state')->hideOnIndex();
        yield IntegerField::new('notification')->hideOnIndex();
        yield AssociationField::new('users')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnIndex();
        yield ImageField::new('photo')
            ->setBasePath('/uploads/files/')
            ->setLabel('Image')
            ->onlyOnIndex()
        ;
        yield ColorField::new('color')->hideOnIndex();
        yield BooleanField::new('isEntry');
        yield BooleanField::new('isSingle');
        yield TextareaField::new('note')->hideOnIndex();
        yield TextareaField::new('renterNote')->hideOnIndex();
        yield TextareaField::new('ownerNote')->hideOnIndex();
        yield AssociationField::new('housing');
        yield AssociationField::new('calendar');
        yield DateField::new('startDate');
        yield DateField::new('endDate');
        yield TimeField::new('startHour')->hideOnIndex();
        yield TimeField::new('endHour')->hideOnIndex();
        yield BooleanField::new('isArchived');
        yield BooleanField::new('isEvent')->hideOnIndex();
        yield BooleanField::new('isPrestation')->hideOnIndex();
        yield AssociationField::new('renter')->hideOnIndex();
        yield AssociationField::new('invoice');
        yield AssociationField::new('prestations')->hideOnIndex();
    }
}
