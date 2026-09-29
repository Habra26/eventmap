# EventMap

Plateforme de découverte d'évènements géolocalisés, développée dans le cadre d'un projet scolaire à l'EAFC Fléron-Charlemagne.

🌐 **Application en ligne** : [eventmap-dun.vercel.app](https://eventmap-dun.vercel.app)

## Présentation

EventMap permet de découvrir des évènements (concerts, spectacles, sports...) sur une carte interactive, à partir des données de l'API Ticketmaster. Les utilisateur·rice·s connecté·e·s peuvent sauvegarder leurs évènements favoris, et proposer leurs propres évènements, qui apparaissent sur la carte aux côtés de ceux de Ticketmaster.

## Fonctionnalités

### Découverte

- 🗺️ Carte interactive : chargement des évènements selon la zone visible, regroupement des marqueurs (clustering)
- 📍 Géolocalisation de l'utilisateur·rice au chargement de la carte
- 🔍 Recherche par mot-clé, ville, catégorie et dates
- 📋 Liste paginée synchronisée avec la carte (sélection, passage à la bonne page, défilement automatique)
- 🎟️ Regroupement des différentes dates d'un même évènement dans un même lieu
- 🕘 Historique des recherches, avec suggestions sous la barre de recherche

### Compte

- 👤 Inscription, connexion et gestion du profil
- 🔑 Réinitialisation du mot de passe par email
- 🖼️ Photo de profil
- ❤️ Favoris (évènements Ticketmaster et évènements des utilisateur·rice·s)

### Évènements des utilisateur·rice·s

- ➕ Création d'un évènement : l'adresse est convertie automatiquement en coordonnées (géocodage)
- ✏️ Modification et suppression, réservées à son·sa créateur·rice
- 📅 Page "Mes évènements" (à venir et passés)

### Interface

- 📱 Interface responsive (mobile et desktop)
- 🇫🇷 Interface et messages de validation en français

## Stack technique

| Couche | Technologie |
|--------|------------|
| Frontend | Vue.js 3, Pinia, Vue Router, Tailwind CSS 4, Tabler Icons |
| Backend | Laravel, Sanctum |
| Base de données | MySQL |
| Carte | Leaflet.js, Leaflet.markercluster, tuiles CARTO |
| API externes | Ticketmaster Discovery API, Nominatim (OpenStreetMap) |
| Emails | Mailtrap (sandbox) |
| Déploiement | Vercel (frontend) + Railway (backend et base de données) |
| Gestion de projet | GitLab (SCRUM : issues, milestones, merge requests) |

## Architecture

- Le frontend communique uniquement avec le backend via une API REST, authentifiée par token (Sanctum).
- Les évènements Ticketmaster ne sont pas stockés en base : les résultats sont regroupés et **mis en cache 15 minutes**. Ils ne sont sauvegardés (snapshot) que lors d'un ajout en favori.
- Les évènements des utilisateur·rice·s sont stockés dans la table `user_events`, puis fusionnés avec ceux de Ticketmaster dans les résultats (identifiants préfixés `user-`).
- L'API renvoie la page de résultats demandée pour la liste, et l'ensemble des évènements de la zone, en version allégée, pour la carte.

## Installation locale

### Prérequis

- PHP 8.2+
- Composer
- Node.js 20.19+ ou 22.12+
- MySQL

### Backend

```bash
cd backend
composer install
cp .env.example .env
# Remplir les variables dans .env (base de données, clé Ticketmaster, emails)
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

### Frontend

```bash
cd frontend
npm install
cp .env.example .env
# Remplir VITE_API_URL et VITE_CARTO_API_KEY
npm run dev
```

## Variables d'environnement

### Backend (`.env`)

| Variable | Description |
|----------|-------------|
| `APP_KEY` | Clé d'application Laravel |
| `APP_URL` | URL du backend (utilisée pour les URL des images) |
| `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Connexion MySQL |
| `TICKETMASTER_API_KEY` | Clé API Ticketmaster |
| `FRONTEND_URL` | URL du frontend (CORS, liens envoyés par email) |
| `CACHE_STORE` | Stockage du cache (`file` ou `database`) |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` | Serveur SMTP (Mailtrap en sandbox) |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Expéditeur des emails |

### Frontend (`.env`)

| Variable | Description |
|----------|-------------|
| `VITE_API_URL` | URL du backend Laravel |
| `VITE_CARTO_API_KEY` | Clé API CARTO pour les tuiles de la carte (clé publique, restreinte par domaine) |

## Déploiement

- **Dépôt principal** : GitLab, avec un miroir sur GitHub utilisé par Vercel et Railway
- **Workflow** : une branche par fonctionnalité → merge request vers `dev` → merge de `dev` vers `main`, qui déclenche le déploiement
- **Frontend** : Vercel, déploiement automatique depuis `main`. Le fichier `frontend/vercel.json` redirige toutes les URL vers `index.html`, pour que Vue Router gère les rechargements de page.
- **Backend** : Railway, déploiement automatique depuis `main`
  - avant le déploiement : `php artisan migrate --force`
  - au démarrage : `php artisan storage:link --force && php artisan serve`
- **Base de données** : MySQL hébergé sur Railway

## Limites connues et améliorations possibles

- 📧 Les emails partent vers une sandbox Mailtrap : l'envoi réel nécessiterait un service comme Brevo, et idéalement un nom de domaine vérifié.
- 🖼️ Les images uploadées sont stockées sur le disque du serveur Railway, qui est réinitialisé à chaque déploiement. Un stockage externe (S3, Cloudinary) serait nécessaire en production réelle.
- ⚙️ Le backend est servi par `php artisan serve`, un serveur de développement qui traite une requête à la fois. Une production réelle utiliserait Nginx ou Caddy avec PHP-FPM.
- 🔢 L'API Ticketmaster renvoie au maximum 100 évènements par recherche.
- ✉️ Pas de vérification de l'adresse email à l'inscription.
- 🌐 L'email de réinitialisation du mot de passe utilise le modèle par défaut de Laravel, en anglais.

## Données

Les évènements proviennent de l'[API Ticketmaster](https://developer.ticketmaster.com/), et le géocodage des adresses de [Nominatim](https://nominatim.org/) (OpenStreetMap). Ce projet est réalisé dans un cadre strictement éducatif et non commercial.

## Auteur

Arnaud Habraken - EAFC Fléron-Charlemagne - 2026