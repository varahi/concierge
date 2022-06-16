<?php

namespace App\Controller\Admin;

use App\Entity\Invoice;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class InvoiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Invoice::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('renter'))
            ->add(EntityFilter::new('owner'))
            ->add(EntityFilter::new('apartment'))
            ->add(EntityFilter::new('contain'))
            ->add(EntityFilter::new('services'))
            ->add(EntityFilter::new('prestations'))
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Invoice')
            ->setEntityLabelInPlural('Invoice')
            ->setSearchFields(['number', 'price', 'date'])
            ->setDefaultSort(['created' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id')->setFormTypeOption('disabled', 'disabled');
        yield TextField::new('number');
        yield TextField::new('price');
        yield DateTimeField::new('date');
        yield TextField::new('total');
        yield AssociationField::new('contain');
        $created = DateTimeField::new('created')->setFormTypeOptions([
            'html5' => true,
            'years' => range(date('Y'), date('Y') + 5),
            'widget' => 'single_text',
        ]);
        if (Crud::PAGE_EDIT === $pageName) {
            yield $created->setFormTypeOption('disabled', true);
        } else {
            yield $created;
        }
        yield BooleanField::new('isOwner');
        yield AssociationField::new('owner');
        yield AssociationField::new('contain');
        yield AssociationField::new('apartment');
        yield BooleanField::new('isRenter');
        yield AssociationField::new('renter');
        yield AssociationField::new('contain')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnIndex();
        yield AssociationField::new('task')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnIndex();

        /*
        yield AssociationField::new('services')->hideOnIndex();
        yield IntegerField::new('services_qty')->hideOnIndex();
        yield AssociationField::new('prestations')->hideOnIndex();
        yield IntegerField::new('prestations_qty')->hideOnIndex();
        yield AssociationField::new('packs')->hideOnIndex();
        yield IntegerField::new('packs_qty')->hideOnIndex();
        yield AssociationField::new('materials')->hideOnIndex();
        yield IntegerField::new('materials_qty')->hideOnIndex();
        yield AssociationField::new('task')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnIndex();
        */
    }
}
