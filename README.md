# HorizonApp - Plateforme de Gestion Horizon Numérique

HorizonApp est une solution intégrée de gestion interne conçue pour **Horizon Numérique SARL**, couvrant trois pôles d'activité : Ventes & Stock, Impressions, et Formation professionnelle.

## 🚀 Fonctionnalités Implémentées

### Backend (Laravel API)
* **Authentification :** Gestion des rôles (Admin, Vendeur, Agent, Formateur) via Laravel Sanctum.
* **Ventes & Stock (B) :** Gestion complète du catalogue produit, point de vente (POS) avec génération de factures PDF.
* **Impressions (C) :** Système de devis et commandes avec suivi de statut et tarification dynamique.
* **Formations (D) :** Gestion du catalogue, inscriptions, feuilles de présence et génération automatique d'attestations certifiées (sous condition de 70% de présence).
* **Rapports (E) :** Exports complets des données.

### Mobile (Flutter)
* Tableau de bord dynamique avec indicateurs en temps réel.
* Consultation des produits avec alertes de stock critique.
* Module de formation : Inscription apprenant, suivi des formations et téléchargement des documents (factures/attestations).
* Déconnexion sécurisée et gestion persistante de la session.

## 🛠️ Stack Technique
* **Backend :** PHP 8.x, Laravel 12.x, MySQL, Barryvdh DomPDF.
* **Mobile :** Flutter, Dart, http, shared_preferences.

## 📦 Installation

### Backend
1. `cd backend`
2. `composer install`
3. Copier `.env.example` vers `.env` et configurer la base de données.
4. `php artisan migrate --seed`
5. `php artisan serve`

### Mobile
1. `cd mobile`
2. `flutter pub get`
3. `flutter run`

## 🔐 Identifiants de Test
* **Admin :** `admin@horizon.com` / `password123`
* **Formateur :** `formateur@horizon.com` / `password123`