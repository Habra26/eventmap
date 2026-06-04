# EventMap

Plateforme de découverte d'évènements géolocalisés, développée dans le cadre d'un projet scolaire à l'EAFC Fléron.

🌐 **Application en ligne** : [eventmap-dun.vercel.app](https://eventmap-dun.vercel.app)

## Présentation

EventMap permet de découvrir des évènements (concerts, festivals, expositions...) sur une carte interactive, à partir des données de l'API Ticketmaster. Les utilisateur·rice·s connecté·e·s peuvent sauvegarder leurs évènements préférés dans une liste de favoris.

## Fonctionnalités

- 🗺️ Carte interactive avec marqueurs par zone géographique
- 🔍 Recherche par mot-clé, ville, catégorie et dates
- 📋 Affichage en liste et sur carte, synchronisés
- ❤️ Ajout et suppression de favoris
- 👤 Inscription, connexion, gestion du profil
- 📱 Interface responsive (mobile et desktop)

## Stack technique

| Couche | Technologie |
|--------|------------|
| Frontend | Vue.js 3, Pinia, Vue Router, Tailwind CSS |
| Backend | Laravel 11, Sanctum |
| Base de données | MySQL |
| Carte | Leaflet.js |
| API externe | Ticketmaster Discovery API |
| Déploiement | Vercel (frontend) + Railway (backend) |

## Architecture

Le frontend communique uniquement avec le backend via une API REST. Les évènements Ticketmaster ne sont sauvegardés en base que lors d'un ajout en favori (snapshot).

## Installation locale

### Prérequis

- PHP 8.2+
- Composer
- Node.js 20+
- MySQL

### Backend

```bash
cd backend
composer install
cp .env.example .env
# Remplir les variables dans .env (DB, Ticketmaster API key)
php artisan key:generate
php artisan migrate
php artisan serve
```

### Frontend

```bash
cd frontend
npm install
cp .env.example .env
# Remplir VITE_API_URL=http://127.0.0.1:8000
npm run dev
```

## Variables d'environnement

### Backend (`.env`)

| Variable | Description |
|----------|-------------|
| `APP_KEY` | Clé d'application Laravel |
| `DB_HOST` | Hôte MySQL |
| `DB_DATABASE` | Nom de la base de données |
| `DB_USERNAME` | Utilisateur MySQL |
| `DB_PASSWORD` | Mot de passe MySQL |
| `TICKETMASTER_API_KEY` | Clé API Ticketmaster |
| `FRONTEND_URL` | URL du frontend (CORS) |

### Frontend (`.env`)

| Variable | Description |
|----------|-------------|
| `VITE_API_URL` | URL du backend Laravel |

## Déploiement

- **Frontend** : Vercel — déploiement automatique depuis la branche `main`
- **Backend** : Railway — déploiement automatique depuis la branche `main`
- **Base de données** : MySQL hébergé sur Railway

## Auteur

Arnaud Habraken — EAFC Fléron — 2026

## Données

Les évènements proviennent de l'[API Ticketmaster](https://developer.ticketmaster.com/). Ce projet est réalisé dans un cadre strictement éducatif et non commercial.

## Roadmap

Fonctionnalités envisagées pour les prochaines itérations :

- 📄 Pagination des résultats
- 🔑 Réinitialisation du mot de passe (mot de passe oublié)
- 📍 Géolocalisation de l'utilisateur
- 🗂️ Clustering des marqueurs sur la carte
- 🎟️ Création d'évènements par les utilisateur·rice·s
- 🖼️ Photo de profil
- ⚡ Mise en cache des requêtes API Ticketmaster
- 📅 Historique des recherches