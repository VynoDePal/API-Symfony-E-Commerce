# API-Symfony-E-Commerce
========================

API-Symfony-E-Commerce est un projet de création d'une API pour un e-commerce avec Symfony. Le projet consiste à créer une API qui permet de gérer les produits, les utilisateurs, les commandes et les paiements.

L'API est construite avec les technologies suivantes :

* Symfony (6.4.7)
* Doctrine (ORM)
* Twig (3.3)
* Stripe (pour les paiements)
* PHP (version 8.1)
* PostgreSQL ( version 15.5 pour la base de données )

Les routeurs suivants sont implémentés :

* /api/products (GET, POST, PUT, DELETE) : gestion des produits
* /api/products/{id} (GET, PUT, DELETE) : gestion d'un produit spécifique
* /api/users (GET, POST, PUT, DELETE) : gestion des utilisateurs
* /users/{id} (GET, PUT, DELETE) : gestion d'un utilisateur spécifique
* /api/orders (GET, POST, PUT, DELETE) : gestion des commandes
* /api/orders/{id} (GET, PUT, DELETE) : gestion d'une commande spécifique
* /stripe/checkout-sessions (GET, POST) : création d'une session de paiement Stripe
* /stripe/success (GET) : traitement d'une session de paiement Stripe

Les requêtes et les réponses sont formatées en JSON.

La sécurité est assurée par l'authentification en fonction des rôles (utilisateurs, administrateurs). Les utilisateurs peuvent créer, lire, mettre à jour et supprimer leurs propres informations. Les administrateurs ont accès à toutes les fonctionnalités de l'API.

## Avant de Commencer

### Pre-requisites

* [Composer](https://getcomposer.org/)
* [Node.js](https://nodejs.org/en/download/) et [npm](https://www.npmjs.com/get-npm)
* [Symfony CLI](https://symfony.com/download)
* Docker (facultatif, mais recommander en production)

## Prise en main

### Installation des packages

Ouvrez un terminal et  cloner le dépôt

cd app
composer install
npm install

### Création de la base de donnée

Créez une base de donnée et un utilisateur. Puis configurez là au niveau de votre fichier .env
