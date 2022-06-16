<?php

namespace App\Controller\Admin;

use App\Entity\Packs;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class PacksCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Packs::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Packs')
            ->setEntityLabelInPlural('Packs')
            ->setSearchFields(['name', 'price'])
            ->setDefaultSort(['id' => 'ASC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('contain'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id')->setFormTypeOption('disabled', 'disabled');
        yield TextField::new('name');
        //yield AssociationField::new('contain');
        yield AssociationField::new('params');
        yield TextField::new('total');
        yield AssociationField::new('housings');
        yield AssociationField::new('invoiceContains')
            ->setFormTypeOptions([
                'by_reference' => false,
            ]);
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
