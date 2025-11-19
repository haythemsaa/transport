# 🚚 Teleroute Marketplace

<div align="center">

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green.svg)

**La plateforme n°1 de mise en relation pour le transport routier en Europe**

[Fonctionnalités](#-fonctionnalités) • [Installation](#-installation) • [Documentation](#-documentation) • [Démo](#-démo) • [Support](#-support)

</div>

---

## 📋 Table des matières

- [À propos](#-à-propos)
- [Fonctionnalités](#-fonctionnalités)
- [Technologies](#-technologies)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Utilisation](#-utilisation)
- [Documentation](#-documentation)
- [Sécurité](#-sécurité)
- [Déploiement](#-déploiement)
- [Contribution](#-contribution)
- [Support](#-support)
- [Licence](#-licence)

---

## 🎯 À propos

**Teleroute Marketplace** est une plateforme complète de mise en relation entre transporteurs et chargeurs pour le transport routier en Europe. Inspirée des leaders du marché (Teleroute, TimoCom, Wtransnet), cette application offre toutes les fonctionnalités nécessaires pour optimiser le transport de marchandises.

### 🌟 Points forts

- ✅ **Application complète** - Toutes les fonctionnalités d'une bourse de fret professionnelle
- 🔒 **Sécurité renforcée** - Protection CSRF, XSS, SQL injection, headers de sécurité
- 🚀 **Performance optimisée** - Cache, compression, indexes BDD
- 📱 **Responsive** - Interface adaptée mobile, tablette, desktop
- 🐳 **Docker ready** - Déploiement en 1 commande
- 📊 **Analytics intégrés** - Statistiques et rapports détaillés
- 🌍 **Multilingue ready** - Architecture préparée pour i18n
- 📝 **Bien documenté** - Documentation complète utilisateur et développeur

---

## ⚡ Fonctionnalités

### 🔐 Gestion des Utilisateurs

- Inscription et authentification sécurisées
- Profils professionnels détaillés (transporteur/chargeur/mixte)
- Vérification des comptes (KBIS, assurance)
- Système de notation et avis (1-5 étoiles)
- Gestion des documents (assurance, licence, KBIS)
- Paramètres avancés (notifications, confidentialité, sécurité)

### 📦 Offres de Fret

- Publication d'offres de fret détaillées
- Recherche avancée multi-critères
- Filtres géographiques, poids, volume, type de marchandise
- Localisation avec coordonnées GPS
- Gestion du statut (active, en cours, livrée, expirée)
- Export CSV des offres

### 🚛 Offres de Véhicules

- Publication de véhicules disponibles
- Multiples types de véhicules (camion, fourgon, semi-remorque, etc.)
- Capacité de charge et volume
- Disponibilité par dates
- Recherche et filtres avancés
- Export CSV

### 💬 Messagerie

- Système de messagerie instantanée
- Conversations privées entre utilisateurs
- Notifications en temps réel
- Historique des messages
- Badge de messages non lus
- Interface intuitive type chat

### ⭐ Évaluations et Confiance

- Notation des partenaires (1-5 étoiles)
- Commentaires détaillés
- Historique des transactions
- Badge "Vérifié" pour comptes validés
- Score de réputation global
- Statistiques de performance

### 💰 Transactions

- Suivi des transactions
- Historique complet
- Statuts multiples (en attente, confirmée, livrée, annulée)
- Export pour comptabilité
- Rapports financiers

### 🔔 Notifications

- Notifications en temps réel
- Multiples canaux (email, SMS, push)
- Préférences personnalisables
- Notifications d'alertes automatiques
- Historique complet

### 🛠️ Outils Avancés

#### 📍 Calculateur
- Calcul de distance (formule Haversine)
- Estimation de prix (carburant + péages)
- Calcul d'émissions CO2
- 13 villes européennes préconfigurées

#### 🎯 Matching Automatique
- Algorithme de compatibilité (score 0-100%)
- Critères: géographie, dates, type véhicule, rating
- Suggestions intelligentes
- Gain de temps optimisé

#### 🔔 Alertes Personnalisées
- Création d'alertes sur mesure
- Notifications automatiques
- Filtres multiples
- Sauvegarde des recherches

#### 📊 Analytics & Statistiques
- Tableaux de bord personnalisés
- Graphiques interactifs (Chart.js)
- Historique sur 12 mois
- Top 5 des routes
- KPIs métier

#### 🗺️ Optimisation de Trajets
- Réduction des kilomètres à vide
- Suggestions de fret au retour
- Économies de carburant
- Calcul ROI

#### ⭐ Favoris
- Sauvegarde offres favorites
- Organisation par type
- Accès rapide
- API REST

#### 🗺️ Carte Interactive
- Visualisation géographique (Leaflet.js)
- Marqueurs départ/arrivée
- Filtres en temps réel
- Auto-refresh 30s

### 📂 Autres Fonctionnalités

- **Annuaire professionnel** - Recherche d'entreprises de transport
- **Programme de parrainage** - 50€ par filleul + 20% de réduction
- **Blog/Actualités** - Articles et conseils métier
- **FAQ complète** - 14 questions avec recherche
- **Tarifs transparents** - 4 formules (Gratuit, Starter, Professional, Enterprise)
- **Rapports et exports** - CSV, PDF (premium)
- **Contact et support** - Formulaire de contact avec validation

### 🔧 Administration

- Dashboard admin complet
- Gestion des utilisateurs
- Gestion des offres
- Visualisation des logs
- Statistiques globales
- Logs de sécurité
- Monitoring système

---

## 🛠️ Technologies

### Backend
- **PHP 8.2+** - Langage serveur
- **MySQL 8.0** - Base de données relationnelle
- **Apache 2.4** - Serveur web
- **PDO** - Accès base de données sécurisé
- **Sessions PHP** - Gestion authentification

### Frontend
- **Bootstrap 5.3** - Framework CSS responsive
- **jQuery 3.7** - Manipulation DOM et AJAX
- **Bootstrap Icons** - Bibliothèque d'icônes
- **Chart.js** - Graphiques et visualisations
- **Leaflet.js** - Cartes interactives
- **OpenStreetMap** - Données cartographiques

### DevOps
- **Docker** - Containerisation
- **Docker Compose** - Orchestration multi-conteneurs
- **Git** - Gestion de versions
- **Bash** - Scripts automatisation

### Outils
- **Redis** - Cache et sessions (optionnel)
- **PhpMyAdmin** - Gestion BDD
- **Composer** - Gestion dépendances PHP (optionnel)
- **NPM** - Gestion dépendances JS (optionnel)

---

## 📥 Installation

### Prérequis

- PHP 8.2 ou supérieur
- MySQL 8.0 ou MariaDB 10.5+
- Apache 2.4 ou Nginx
- 2GB RAM minimum
- 10GB espace disque

### Option 1: Installation avec Docker (Recommandé)

```bash
# 1. Cloner le repository
git clone https://github.com/haythemsaa/transport.git
cd transport

# 2. Lancer les conteneurs
docker-compose up -d

# 3. Importer le schéma
docker-compose exec db mysql -u root -proot_password teleroute_marketplace < database/schema.sql

# 4. Accéder à l'application
open http://localhost:8080

# PhpMyAdmin disponible sur http://localhost:8081
# User: root / Password: root_password
```

C'est tout ! L'application est prête à l'emploi avec Docker.

### Option 2: Installation Manuelle

#### Sur Ubuntu/Debian

```bash
# 1. Cloner le repository
git clone https://github.com/haythemsaa/transport.git
cd transport

# 2. Exécuter le script de setup (installe tout automatiquement)
sudo ./scripts/setup.sh

# 3. Le script configure automatiquement:
#    - Apache, PHP 8.2, MySQL
#    - Base de données et utilisateur
#    - Virtual host Apache
#    - Permissions
#    - Firewall
#    - Cron jobs pour backups

# 4. Credentials sauvegardés dans:
sudo cat /root/.teleroute-marketplace-credentials
```

#### Installation Manuelle Détaillée

```bash
# 1. Installer les dépendances
sudo apt update
sudo apt install -y apache2 php8.2 php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl mysql-server

# 2. Créer la base de données
mysql -u root -p
CREATE DATABASE teleroute_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'teleroute_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON teleroute_marketplace.* TO 'teleroute_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# 3. Importer le schéma
mysql -u teleroute_user -p teleroute_marketplace < database/schema.sql

# 4. Configurer l'application
cp .env.example .env
nano .env  # Éditer avec vos credentials

# 5. Permissions
sudo chown -R www-data:www-data /var/www/teleroute-marketplace
sudo chmod -R 755 /var/www/teleroute-marketplace
sudo chmod -R 775 /var/www/teleroute-marketplace/uploads
sudo chmod -R 775 /var/www/teleroute-marketplace/logs

# 6. Générer des données de test (optionnel)
php generate-test-data.php
```

---

## ⚙️ Configuration

### Fichier .env

Créer un fichier `.env` à la racine (ou copier `.env.example`):

```env
# Base de données
DB_HOST=localhost
DB_NAME=teleroute_marketplace
DB_USER=teleroute_user
DB_PASS=your_secure_password

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# Session
SESSION_LIFETIME=7200

# Email (SMTP)
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=noreply@example.com
MAIL_PASSWORD=your_mail_password
MAIL_FROM_ADDRESS=noreply@votre-domaine.com
MAIL_FROM_NAME="Teleroute Marketplace"
```

### SSL / HTTPS

Pour activer HTTPS avec Let's Encrypt:

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d votre-domaine.com -d www.votre-domaine.com
```

Le certificat se renouvelle automatiquement.

---

## 🚀 Utilisation

### Accès à l'Application

- **Frontend**: http://localhost (ou votre domaine)
- **Administration**: http://localhost/admin/
- **PhpMyAdmin**: http://localhost:8081 (si Docker)

### Comptes de Test

Après avoir exécuté `generate-test-data.php`:

| Type | Email | Password |
|------|-------|----------|
| Transporteur | transporter1@example.com | password123 |
| Chargeur | shipper1@example.com | password123 |
| Mixte | both1@example.com | password123 |

### Workflow Type

1. **Inscription** - Créer un compte transporteur ou chargeur
2. **Compléter le profil** - SIRET, adresse, documents
3. **Publier une offre** - Fret ou véhicule disponible
4. **Rechercher** - Filtres avancés pour trouver des correspondances
5. **Contacter** - Messagerie instantanée
6. **Finaliser** - Transaction et évaluation

---

## 📚 Documentation

- **[DEVELOPER.md](DEVELOPER.md)** - Documentation technique complète
- **[CHANGELOG.md](CHANGELOG.md)** - Historique des versions
- **[scripts/README.md](scripts/README.md)** - Guide des scripts de déploiement

### Structure du Projet

```
transport/
├── admin/                  # Panel d'administration
├── api/                    # API REST endpoints
├── assets/                 # Ressources statiques (CSS, JS, images)
├── config/                 # Configuration
├── cron/                   # Scripts cron automatisés
├── database/               # Schéma SQL
├── exports/                # Scripts d'export CSV
├── helpers/                # Fonctions utilitaires
├── includes/               # Templates (header, footer)
├── logs/                   # Logs applicatifs
├── models/                 # Modèles MVC
├── scripts/                # Scripts de déploiement
├── uploads/                # Fichiers uploadés
├── docker-compose.yml      # Configuration Docker
├── Dockerfile              # Image Docker
├── .htaccess              # Configuration Apache
└── README.md              # Ce fichier
```

---

## 🔒 Sécurité

### Mesures Implémentées

- ✅ **Protection CSRF** - Tokens sur tous les formulaires
- ✅ **Protection XSS** - Échappement HTML systématique
- ✅ **Protection SQL Injection** - Requêtes préparées PDO
- ✅ **Hashage bcrypt** - Mots de passe sécurisés
- ✅ **Headers de sécurité** - CSP, X-Frame-Options, etc.
- ✅ **Rate limiting** - Protection contre brute force
- ✅ **Session sécurisée** - HttpOnly, SameSite cookies
- ✅ **Validation inputs** - Filtres sur toutes les entrées
- ✅ **Upload sécurisé** - Vérification types MIME
- ✅ **Logs sécurité** - Traçabilité complète

### Signaler une Vulnérabilité

Si vous découvrez une faille de sécurité, merci de nous contacter à:
**security@teleroute-marketplace.com**

---

## 🚢 Déploiement

### Déploiement Automatisé

```bash
# Déployer en production
./scripts/deploy.sh production main

# Déployer en staging
./scripts/deploy.sh staging develop
```

Le script effectue automatiquement:
- ✅ Backup avant déploiement
- ✅ Mode maintenance
- ✅ Pull du code Git
- ✅ Installation dépendances
- ✅ Migrations BDD
- ✅ Permissions
- ✅ Redémarrage services
- ✅ Vérification santé
- ✅ **Rollback automatique en cas d'erreur**

### Backups Automatiques

Les backups sont configurés automatiquement via cron:

```bash
# Backup complet quotidien à 2h
0 2 * * * ./scripts/backup.sh full

# Backup BDD toutes les 6h
0 */6 * * * ./scripts/backup.sh database
```

---

## 🤝 Contribution

Les contributions sont les bienvenues ! Voici comment participer:

1. **Fork** le projet
2. **Créer** une branche feature (`git checkout -b feature/AmazingFeature`)
3. **Commit** vos changements (`git commit -m 'feat: Add AmazingFeature'`)
4. **Push** vers la branche (`git push origin feature/AmazingFeature`)
5. **Ouvrir** une Pull Request

### Standards de Code

- **PHP**: PSR-12
- **JavaScript**: Standard JS
- **SQL**: snake_case
- **Commits**: Convention Conventional Commits

---

## 💬 Support

### Canaux de Support

- 📧 **Email**: support@teleroute-marketplace.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/haythemsaa/transport/issues)
- 📖 **Documentation**: [DEVELOPER.md](DEVELOPER.md)

### FAQ

Consultez notre [FAQ complète](http://localhost/faq.php) pour les questions fréquentes.

---

## 📝 Licence

Ce projet est sous licence **MIT**.

---

## 🙏 Remerciements

- [Bootstrap](https://getbootstrap.com/) - Framework CSS
- [Chart.js](https://www.chartjs.org/) - Graphiques
- [Leaflet](https://leafletjs.com/) - Cartes interactives
- [OpenStreetMap](https://www.openstreetmap.org/) - Données cartographiques
- Tous les contributeurs du projet

---

## 📊 Statistiques du Projet

- **Lignes de code**: ~15,000+
- **Fichiers**: 75+
- **Tables BDD**: 12
- **API Endpoints**: 5
- **Outils avancés**: 7

---

<div align="center">

**Fait avec ❤️ pour la communauté du transport**

[⬆ Retour en haut](#-teleroute-marketplace)

</div>
