<?php

namespace App\Controller\Admin;

use App\Entity\Renter;
use App\Entity\User;
use App\Entity\Housing;
use App\Entity\Task;
use App\Entity\Reservation;
use App\Entity\Materials;
use App\Entity\Packs;
use App\Entity\Services;
use App\Entity\Prestation;
use App\Entity\Calendar;
use App\Entity\Elements;
use App\Entity\Room;
use App\Entity\Objet;
use App\Entity\Document;
use App\Entity\Params;
use App\Entity\Invoice;
use App\Entity\InvoiceContain;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Controller\Admin\TaskCrudController;
use App\Controller\Admin\UserCrudController;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/backend", name="app_backend")
     */
    public function index(): Response
    {
        //return parent::index();
        $routeBuilder = $this->get(AdminUrlGenerator::class);
        return $this->redirect($routeBuilder->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Concierge Admin Panel');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('adminpanel.back_to_site', 'fa fa-home', 'app_login');
        yield MenuItem::linkToLogout('Logout', 'fa fa-user-times');

        /*
        //yield MenuItem::linktoDashboard('Dashboard', 'fa fa-desktop');
        //yield MenuItem::linkToCrud('Taches', 'fa fa-tags', Task::class);
        //yield MenuItem::linkToCrud('Logement', 'fa fa-home', Housing::class);
        //yield MenuItem::linkToCrud('Locataire', 'fa fa-file-text', Renter::class);
        //yield MenuItem::section('Settings');
        //yield MenuItem::linkToCrud('Settings', 'fa fa-cog', Settings::class);
        //yield MenuItem::linkToCrud('Materials', 'fa fa-list', Materials::class);
        //yield MenuItem::linkToCrud('Packs', 'fa fa-group', Packs::class);
        //yield MenuItem::linkToCrud('Services', 'fa fa-wrench', Services::class);
        */

        yield MenuItem::section('Housing and renters');
        yield MenuItem::subMenu('Housing', 'fa fa-tags')->setSubItems([
            MenuItem::linkToCrud('Apartment', 'fa fa-home', Housing::class),
            MenuItem::linkToCrud('Locataire', 'fa fa-file-text', Renter::class),
            MenuItem::linkToCrud('Elements', 'fa fa-check-square', Elements::class),
        ]);

        yield MenuItem::section('Rooms');
        yield MenuItem::subMenu('Rooms', 'fa fa-braille')->setSubItems([
            MenuItem::linkToCrud('Room', 'fa fa-bath', Room::class),
            MenuItem::linkToCrud('Room Objet', 'fa fa-bed', Objet::class),
            MenuItem::linkToCrud('Files', 'fa fa-photo', Document::class),
            //MenuItem::linkToCrud('Folder', 'fa fa-folder', Folder::class),
        ]);

        yield MenuItem::section('Settings');
        yield MenuItem::subMenu('Settings', 'fa fa-cog')->setSubItems([
            MenuItem::linkToCrud('Prestation', 'fa fa-wrench', Prestation::class),
            MenuItem::linkToCrud('Materials', 'fa _fa-list _fa-barcode fa-th', Materials::class),
            MenuItem::linkToCrud('Packs', 'fa fa-group', Packs::class),
            MenuItem::linkToCrud('Services', 'fa fa-tags', Services::class),
            MenuItem::linkToCrud('Params', 'fa fa-tags', Params::class),

        ]);

        yield MenuItem::section('Users and Tasks');
        yield MenuItem::subMenu('User and Tasks', 'fa fa-cog')->setSubItems([
            MenuItem::linkToCrud('Taches', 'fa fa-tasks', Task::class),
            MenuItem::linkToCrud('Reservation', 'fa fa-building', Reservation::class),
            MenuItem::linkToCrud('Calendar', 'fa fa-calendar', Calendar::class),
            MenuItem::linkToCrud('User', 'fa fa-user', User::class),
        ]);

        yield MenuItem::section('Invoice');
        yield MenuItem::subMenu('Invoices', 'fa fa-files-o')->setSubItems([
            MenuItem::linkToCrud('Invoice', 'fa fa-file-text-o', Invoice::class),
            MenuItem::linkToCrud('Contain', 'fa fa-list', InvoiceContain::class),
        ]);
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        // Usually it's better to call the parent method because that gives you a
        // user menu with some menu items already created ("sign out", "exit impersonation", etc.)
        // if you prefer to create the user menu from scratch, use: return UserMenu::new()->...
        return parent::configureUserMenu($user)
            // use the given $user object to get the user name
            //->setName($user->getFullName())
            //->setName('Dima')
            // use this method if you don't want to display the name of the user
            ->displayUserName(true)

            // you can return an URL with the avatar image
            ->setAvatarUrl('https://sun9-58.userapi.com/s/v1/ig2/oVJeve9PSHuJD8vSj7c3kK25n25ijALknVvlvcHvEZoSEs4EnVfN8vYM92quqi27G27mk5uC87IvDw1rH7a1h5AJ.jpg?size=50x50&quality=95&crop=0,29,575,575&ava=1')
            //->setAvatarUrl($user->getProfileImageUrl())
            // use this method if you don't want to display the user image
            ->displayUserAvatar(true)
            // you can also pass an email address to use gravatar's service
            //->setGravatarEmail($user->getMainEmailAddress())
            ->setGravatarEmail('info@t3dev.ru')

            // you can use any type of menu item, except submenus
            ->addMenuItems([
                MenuItem::linkToRoute('My Profile', 'fa fa-id-card', '...', ['...' => '...']),
                MenuItem::linkToRoute('Settings', 'fa fa-user-cog', '...', ['...' => '...']),
                MenuItem::section(),
                MenuItem::linkToLogout('Logout', 'fa fa-sign-out'),
            ]);
    }
}
