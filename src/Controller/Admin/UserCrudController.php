<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AvatarField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Validator\Constraints\Date;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('task'))
            ->add(EntityFilter::new('calendar'))
            ->add(EntityFilter::new('apartments'))
            ->add(EntityFilter::new('invoices'))
            ->add(EntityFilter::new('reservations'))
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('adminpanel.user')
            ->setEntityLabelInPlural('adminpanel.user')
            ->setSearchFields(['firstName', 'lastName', 'email'])
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id')->setFormTypeOption('disabled', 'disabled');
        yield TextField::new('username');
        yield TextField::new('email');
        yield TextField::new('firstName');
        yield TextField::new('lastName');
        //yield AvatarField::new('avatar');
        yield DateField::new('startDate')->hideOnIndex();
        yield DateField::new('endDate')->hideOnIndex();
        yield TextField::new('zip')->hideOnIndex();
        yield TextField::new('city')->hideOnIndex();
        yield TextField::new('address')->hideOnIndex();
        yield TextField::new('telephone');
        yield TextField::new('telephoneFullNumber')->hideOnIndex();

        yield TextField::new('telephone2');
        yield TextField::new('telephone2FullNumber')->hideOnIndex();

        yield TextField::new('company');
        //yield TextField::new('contactPerson');
        yield TextareaField::new('note')->hideOnIndex();
        yield BooleanField::new('isVerified');
        //yield ArrayField::new('roles')->setFormTypeOption('disabled', 'disabled');
        yield ArrayField::new('roles');
        yield AssociationField::new('task')->hideOnIndex();
        yield ColorField::new('color')->hideOnIndex();
        yield AssociationField::new('apartments')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnIndex()
            //->autocomplete()
        ;
        yield AssociationField::new('agencyApartments')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnIndex()
            //->autocomplete()
        ;
        yield AssociationField::new('employerAppts')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnIndex();
        yield AssociationField::new('invoices')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnIndex();
        yield AssociationField::new('reservations')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnIndex();
    }
}
