<?php

namespace App\Controller\Admin;

use App\Entity\Elements;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ElementsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Elements::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('adminpanel.elements')
            ->setEntityLabelInPlural('adminpanel.elements')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id')->setFormTypeOption('disabled', 'disabled');
        yield AssociationField::new('apartments')
            ->setFormTypeOptions([
                'by_reference' => false,
            ]);
        yield TextField::new('other');
        yield TextareaField::new('note');
        yield BooleanField::new('garage')->hideOnIndex();
        yield BooleanField::new('parking')->hideOnIndex();
        yield BooleanField::new('basement')->hideOnIndex();
        yield BooleanField::new('garden')->hideOnIndex();
        yield BooleanField::new('basementParking')->hideOnIndex();
        yield BooleanField::new('parentalSuite')->hideOnIndex();
        yield BooleanField::new('furniture')->hideOnIndex();
        yield BooleanField::new('poll')->hideOnIndex();
        yield BooleanField::new('teracce')->hideOnIndex();
        yield BooleanField::new('mezzanine')->hideOnIndex();
        yield BooleanField::new('storage')->hideOnIndex();
        yield BooleanField::new('veranda')->hideOnIndex();
        yield BooleanField::new('unfurnished')->hideOnIndex();
        yield BooleanField::new('bathroom')->hideOnIndex();
        yield BooleanField::new('separateWc')->hideOnIndex();
        yield BooleanField::new('wifi')->hideOnIndex();
        yield BooleanField::new('elevator')->hideOnIndex();
    }
}
