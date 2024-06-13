# API-Symfony-E-Commerce
========================

API-Symfony-E-Commerce est une API pour gérer un site de commerce électronique, utilisant le framework Symfony. Cette API permet la gestion des produits, des utilisateurs, des commandes et des paiements.

## Technologies Utilisées

* Symfony (7.1.0)
* Doctrine (ORM)
* Twig (v3.3)
* Stripe (paiements)
* PHP (v8.3)
* PostgreSQL (v16)
* Swagger/OpenAPI (documentation)

## Routes de l'API

* Produits
    * `/api/products` (GET, POST, PUT, DELETE)
    * `/api/products/{id}` (GET, PUT, DELETE)
* Utilisateurs
    * `/api/users` (GET, POST, PUT, DELETE)
    * `/api/users` (GET, PUT, DELETE)
* Commandes
    * `/api/orders` (GET, POST, PUT, DELETE)
    * `/api/orders/{id}` (GET, PUT, DELETE)
* Paiements Stripe
    * `/stripe/checkout-sessions` (GET, POST)
    * `/stripe/success` (GET)

Les requêtes et réponses sont format JSON.

## Sécurité

L'authentification est basée sur des rôles (utilisateurs, administrateurs). Les utilisateurs peuvent gérer leurs informations personnelles tandis que les administrateurs ont accès à toutes les fonctionnalités.

## Avant de Commencer

### Pre-requisites

* [Composer](https://getcomposer.org/)
* [Node.js](https://nodejs.org/en/download/) et [npm](https://www.npmjs.com/get-npm)
* [Symfony CLI](https://symfony.com/download)
* Docker (optionnel,recommandé pour la production)

## Prise en main

### 1. Installation

Cloner le dépôt et installer les dépendances

* git clone https://github.com/VynoDePal/API-Symfony-E-Commerce
* cd app
* composer install
* npm install

### 2. Configuration de la Base de Données

Créez une base de données et un utilisateur, puis configurez le fichier `.env` en conséquence.

### 3. Exécuter les Migrations

* php bin/console doctrine:migrations:migrate --no-interaction
* php bin/console doctrine:migrations:diff --no-interaction
* php bin/console doctrine:migrations:migrate --no-interaction

### Lancer le Serveur

* symfony serve
