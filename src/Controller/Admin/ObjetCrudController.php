<?php

namespace App\Controller\Admin;

use App\Entity\Objet;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class ObjetCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Objet::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Room Objet')
            ->setEntityLabelInPlural('Room Objet')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id')->setFormTypeOption('disabled', 'disabled');
        yield TextField::new('name');
        yield TextField::new('state')->hideOnDetail();
        yield ChoiceField::new('state')->setChoices(
            [
                'Please select' => null,
                'Neuf' => 'Neuf',
                'Parfait état' => 'Parfait état',
                'Bon état' => 'Bon état',
                'Usé' => 'Usé'
            ]
        )->hideOnIndex();
        yield IntegerField::new('quantity');
        yield TextareaField::new('description');
        yield AssociationField::new('room');
        //yield AssociationField::new('folder');
        yield AssociationField::new('documents')
            ->setFormTypeOptions([
                'by_reference' => false,
            ]);
    }
}
