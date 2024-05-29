<?php

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;

class ShoppingCart
{
    /**
     * Le constructeur initialise l'objet ShoppingCart avec une collection d'articles vide.
     */
    public function __construct(
        public ArrayCollection $items = new ArrayCollection()
    ) {
    }
}