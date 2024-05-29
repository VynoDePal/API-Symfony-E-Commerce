<?php

namespace App\Model;

use App\Entity\Product;

/**
 * Représente un article dans un panier d'achat, avec un produit et sa quantité.
 */
class ShoppingCartItem
{
    /**
     * Initialise une nouvelle instance de ShoppingCartItem.
     * 
     * @param Product $product le produit associé à cet article.
     * @param int $quantity la quantité du produit.
     */
    public function __construct(
        public Product $product,
        public int $quantity,
    ) {
    }
}