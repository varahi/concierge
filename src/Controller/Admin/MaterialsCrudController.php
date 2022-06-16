<?php

namespace App\Controller\Admin;

use App\Entity\Materials;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CurrencyField;

class MaterialsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Materials::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id')->setFormTypeOption('disabled', 'disabled');
        yield TextField::new('name');
        //yield MoneyField::new('dayPrice')->setCurrency('EUR')->setNumDecimals('2');
        //yield MoneyField::new('weekPrice')->setCurrency('EUR');
        //yield TextField::new('price');
        yield TextField::new('dayPrice');
        yield TextField::new('weekPrice');
        yield IntegerField::new('quantity');
        yield AssociationField::new('invoiceContains')
            ->setFormTypeOptions([
                'by_reference' => false,
            ]);
        yield BooleanField::new('isRenter');
        yield BooleanField::new('isOwner');
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
