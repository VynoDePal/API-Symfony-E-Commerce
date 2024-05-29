<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\StripeService;
use Stripe\Exception\ApiErrorException;

class ProductCrudController extends AbstractCrudController
{
    /**
     * Injecte le service Stripe dans le contrôleur.
     */
    public function __construct(private StripeService $stripeService) {}

    /**
     * Renvoie le nom de classe entièrement qualifié de l'entité Produit.
     */
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    /**
     * Configure les champs à afficher dans l'interface d'administration du produit.
     */
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name')
            ->setRequired(true);

        yield TextareaField::new('description')
            ->setRequired(true);
        
        /**
         * Affiche le champ de la photo du produit, qui n'est visible que sur les formulaires.
         */
        yield Field::new('photo', 'Photo')
            ->setFormType(VichFileType::class)
            ->onlyOnForms();

        yield MoneyField::new('Price')
            ->setCurrency('XOF')
            ->setRequired(true);
        
        yield BooleanField::new('isAvailable');

        /**
         * Affiche le champ d'identification du produit Stripe, qui n'est visible que lors de l'édition.
         */
        yield TextField::new('stripeProductId', 'Identifiant Produit Stripe')
            ->hideWhenCreating();
        
        /**
         * Affiche le champ Stripe price ID, qui n'est visible que lors de la modification.
         */
        yield TextField::new('stripePriceId', 'Identifiant Prix Stripe')
            ->hideWhenCreating();
    }

    /**
     * Persiste une nouvelle entité Produit dans la base de données et crée les produits et les prix associés dans Stripe.
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Product $product */
        $product = $entityInstance;

        $stripeProduct = $this->stripeService->createProduct($product);

        $product->setStripeProductId($stripeProduct->id);

        $stripePrice = $this->stripeService->createPrice($product);

        $product->setStripePriceId($stripePrice->id);

        /**
         * Persiste l'entité Produit dans la base de données à l'aide de la méthode parent.
         */
        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @throws ApiErrorException
     */

    /**
     * Met à jour une entité Produit existante dans la base de données et dans Stripe en utilisant le service Stripe.
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Product $product */
        $product = $entityInstance;

        $this->stripeService->updateProduct($product);

        /**
         * Met à jour l'entité Produit dans la base de données à l'aide de la méthode parent.
         */
        parent::updateEntity($entityManager, $entityInstance);
    }
}
