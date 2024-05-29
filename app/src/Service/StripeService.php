<?php

namespace App\Service;

use App\Entity\Product;
use App\Model\ShoppingCart;
// use App\Model\ShoppingCartItem;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\StripeClient;
use Stripe\Checkout\Session;

class StripeService
{
    /**
     * Variable d'instance pour contenir le client Stripe
     */
    private StripeClient $stripe;

    /**
     * Crée un nouveau produit sur Stripe.
     * 
     * @param Product $product Le produit à créer sur Stripe.
     * @return \Stripe\Product Le produit Stripe créé.
     * @throws ApiErrorException En cas d'erreur lors de la communication avec l'API Stripe.
     */
     public function createProduct(Product $product): \Stripe\Product
     {
        return $this->getStripe()->products->create([
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'active' => $product->isAvailable(),
        ]);
     }
    
    /**
     * Crée un nouveau prix pour un produit sur Stripe.
     * 
     * @param Product $product Le produit pour lequel un nouveau prix doit être créé.
     * @return Price Le prix Stripe créé.
     * @throws ApiErrorException En cas d'erreur lors de la communication avec l'API Stripe.
     */
     public function createPrice(Product $product): Price
     {
        return $this->getStripe()->prices->create([
            'unit_amount' => $product->getPrice(),
            'currency' => 'XOF',
            'product' => $product->getStripeProductId(),
        ]);
     }

    /**
     * Mise à jour d'un produit existant sur Stripe.
     * 
     * @param Product $product Le produit à mettre à jour sur Stripe.
     * @return \Stripe\Product Le produit Stripe mis à jour.
     * @throws ApiErrorException En cas d'erreur lors de la communication avec l'API Stripe.
     */
      public function updateProduct(Product $product): \Stripe\Product
      {
        return $this->getStripe()->products->update(
            $product->getStripeProductId(),
            [
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'active' => $product->isAvailable(),
            ]
        );
      }

      public function createCheckoutSession(ShoppingCart $shoppingCart): Session
      {
        $lineItems = [];

        foreach ($shoppingCart->items as $item) {
            $lineItems[] = [
                'price' => $item->product->getStripePriceId(),
                'quantity' => $item->quantity
            ];
        }

        return $this->getStripe()->checkout->sessions->create([
            'currency' => 'XOF',
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => 'http://localhost:8000/success?session_id={CHECKOUT_SESSION_ID}',
        ]);
      }

      public function getCheckoutSession(string $sessionId): Session
      {
        return $this->getStripe()->checkout->sessions->retrieve($sessionId);
      }

      /**
       * Obtient le client Stripe, en le créant s'il n'existe pas déjà.
       * 
       * @return StripeClient Le client Stripe.
       */
     private function getStripe()
     {
        return $this->stripe ??= new StripeClient($_ENV['STRIPE_API_SECRET']);
     }
}