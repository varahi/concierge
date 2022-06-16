<?php

namespace App\Controller\Admin;

use App\Entity\Prestation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PrestationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Prestation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->setFormTypeOption('disabled', 'disabled');
        yield TextField::new('name');
        yield IntegerField::new('price');
        yield AssociationField::new('params');
        yield AssociationField::new('tasks')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnIndex();
        yield AssociationField::new('invoiceContains')
            ->setFormTypeOptions([
                'by_reference' => false,
            ]);
        yield BooleanField::new('isRenter');
        yield BooleanField::new('isOwner');
        yield BooleanField::new('isTask');
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
