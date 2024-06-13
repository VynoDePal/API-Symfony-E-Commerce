# API-Symfony-E-Commerce
========================

API-Symfony-E-Commerce est une API pour gérer un site de commerce électronique, utilisant le framework Symfony. Cette API permet la gestion des produits, des utilisateurs, des commandes et des paiements.

# Technologies Utilisées

* Symfony (7.1.0)
* Doctrine (ORM)
* Twig (v3.3)
* Stripe (paiements)
* PHP (v8.3)
* PostgreSQL (v16)
* Swagger/OpenAPI (documentation)

# Routes de l'API

* Produits
    * `/api/products` (GET, POST, PUT, DELETE)
* /api/products/{id} (GET, PUT, DELETE) : gestion d'un produit spécifique
* /api/users (GET, POST, PUT, DELETE) : gestion des utilisateurs
* /api/users (GET, PUT, DELETE) : gestion d'un utilisateur spécifique
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

* cd app
* composer install
* npm install

### Création de la base de donnée

Créez une base de donnée et un utilisateur. Puis configurez là au niveau de votre fichier .env

### Exécutez les migrations de l'application

* php bin/console doctrine:migrations:migrate --no-interaction
* php bin/console doctrine:migrations:diff --no-interaction
* php bin/console doctrine:migrations:migrate --no-interaction

### Lancer l'API

Dans votre terminal, taper la commande suivante pour le lancer le server de l'API :

* symfony serve
