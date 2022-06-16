<?php

namespace App\Controller\Admin;

use App\Entity\InvoiceContain;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class InvoiceContainCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return InvoiceContain::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Iinvoice contain')
            ->setEntityLabelInPlural('Invoice contain')
            ->setSearchFields(['name', 'price', 'service', 'prestation', 'pack', 'material'])
            ->setDefaultSort(['id' => 'ASC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('service'))
            ->add(EntityFilter::new('prestation'))
            ->add(EntityFilter::new('pack'))
            ->add(EntityFilter::new('material'))
            ;
    }


    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id')->setFormTypeOption('disabled', 'disabled');
        yield TextField::new('name');
        yield IntegerField::new('quantity');
        yield TextField::new('price');
        yield TextField::new('total');
        yield AssociationField::new('invoice');
        yield AssociationField::new('service');
        yield AssociationField::new('prestation');
        yield AssociationField::new('pack');
        yield AssociationField::new('material');
    }
}
