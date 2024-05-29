<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Product;
use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * L'AdminUrlGenerator est utilisé pour générer des URL pour le tableau de bord de l'administrateur.
     */
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        /**
         * Génère l'URL du tableau de bord de l'administrateur, en définissant les contrôleurs pour les produits, les utilisateurs et les commandes.
         */
        $url = $this->adminUrlGenerator
            ->setController(ProductCrudController::class)
            ->setController(UserCrudController::class)
            ->setController(OrderCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        /**
         * Créez un nouvel objet Tableau de bord et définissez le titre et les paramètres locaux.
         */
        return Dashboard::new()
            ->setTitle('Admin')
            ->setLocales(['fr', 'en']);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Products', 'fas fa-list', Product::class);
        yield MenuItem::section('Administration');
        yield MenuItem::linkToCrud('Users', 'fas fa-users', User::class);
        yield MenuItem::section('Orders');
        yield MenuItem::linkToCrud('Orders', 'fas fa-shopping-cart', Order::class);
    }
}