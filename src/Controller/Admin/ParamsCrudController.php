<?php

namespace App\Controller\Admin;

use App\Entity\Params;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ParamsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Params::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->setFormTypeOption('disabled', 'disabled');
        yield IntegerField::new('quantity');
        yield TextField::new('price');
        yield TextField::new('discount');
        yield AssociationField::new('packs');
        yield AssociationField::new('prestation');
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
