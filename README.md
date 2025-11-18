# Teleroute Marketplace - Plateforme de Transport Routier

Une plateforme complète de marketplace pour le transport routier, similaire à Teleroute, développée en PHP, JavaScript et Bootstrap.

## 🚀 Fonctionnalités

### Fonctionnalités principales

- ✅ **Système d'authentification complet**
  - Inscription avec vérification d'email
  - Connexion sécurisée
  - Gestion de profil utilisateur

- ✅ **Gestion des offres de fret**
  - Recherche avancée avec filtres multiples
  - Publication d'offres de fret
  - Géolocalisation des chargements et livraisons
  - Calcul automatique des distances

- ✅ **Gestion des offres de véhicules**
  - Recherche de véhicules disponibles
  - Publication d'offres de véhicules
  - Spécifications détaillées (capacité, équipements, etc.)

- ✅ **Messagerie instantanée**
  - Chat en temps réel entre utilisateurs
  - Notifications de nouveaux messages
  - Historique des conversations

- ✅ **Système de notation et d'évaluations**
  - Évaluation des partenaires commerciaux
  - Notes avec critères détaillés (ponctualité, communication, professionnalisme)
  - Profils vérifiés

- ✅ **Gestion des transactions**
  - Suivi des contrats de transport
  - Gestion des documents (CMR, POD)
  - Tracking en temps réel

- ✅ **Tableau de bord intuitif**
  - Statistiques en temps réel
  - Vue d'ensemble des activités
  - Accès rapide aux fonctionnalités

- ✅ **Interface responsive**
  - Design mobile-first avec Bootstrap 5
  - Compatible tous appareils
  - Navigation fluide et moderne

## 📋 Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Apache avec mod_rewrite activé
- Extensions PHP requises:
  - PDO
  - mysqli
  - json
  - mbstring
  - session

## 🔧 Installation

### 1. Cloner le projet

```bash
git clone <url-du-repo>
cd transport
```

### 2. Configuration de la base de données

1. Créer une base de données MySQL:

```sql
CREATE DATABASE teleroute_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Importer le schéma de base de données:

```bash
mysql -u root -p teleroute_marketplace < database/schema.sql
```

### 3. Configuration de l'application

1. Éditer le fichier `config/database.php` et mettre à jour les informations de connexion:

```php
private $host = 'localhost';
private $db_name = 'teleroute_marketplace';
private $username = 'votre_utilisateur';
private $password = 'votre_mot_de_passe';
```

2. Éditer le fichier `config/config.php` et mettre à jour:

```php
define('BASE_URL', 'http://votre-domaine.com');
define('GOOGLE_MAPS_API_KEY', 'votre_cle_api_google_maps');
```

### 4. Configuration du serveur web

#### Apache

Créer un VirtualHost:

```apache
<VirtualHost *:80>
    ServerName teleroute-marketplace.local
    DocumentRoot "/chemin/vers/transport"

    <Directory "/chemin/vers/transport">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "/var/log/apache2/teleroute-error.log"
    CustomLog "/var/log/apache2/teleroute-access.log" common
</VirtualHost>
```

Redémarrer Apache:

```bash
sudo systemctl restart apache2
```

### 5. Permissions des dossiers

```bash
chmod 755 -R .
chmod 777 uploads/
chmod 777 logs/
```

## 🎯 Utilisation

### Accès à l'application

1. Ouvrir votre navigateur et accéder à: `http://localhost/transport` ou votre domaine configuré

2. Créer un compte utilisateur:
   - Cliquer sur "Inscription"
   - Choisir le type de compte (Transporteur, Chargeur, ou les deux)
   - Remplir le formulaire d'inscription

3. Se connecter avec vos identifiants

### Publier une offre de fret (Chargeurs)

1. Se connecter au tableau de bord
2. Cliquer sur "Publier du fret"
3. Remplir les informations:
   - Lieux de chargement et livraison
   - Dates et horaires
   - Type de cargo et détails
   - Prix et conditions
4. Valider la publication

### Publier une offre de véhicule (Transporteurs)

1. Se connecter au tableau de bord
2. Cliquer sur "Publier un véhicule"
3. Remplir les informations:
   - Localisation et destination
   - Type de véhicule et caractéristiques
   - Capacités et équipements
   - Tarification
4. Valider la publication

### Rechercher des offres

1. Utiliser la barre de recherche sur la page d'accueil
2. Appliquer les filtres (ville, date, type, etc.)
3. Consulter les résultats
4. Cliquer sur une offre pour voir les détails
5. Contacter le propriétaire via la messagerie

## 🗂️ Structure du projet

```
transport/
├── assets/
│   ├── css/
│   │   └── style.css          # Styles personnalisés
│   └── js/
│       └── main.js            # JavaScript principal
├── config/
│   ├── config.php             # Configuration générale
│   └── database.php           # Configuration DB
├── database/
│   └── schema.sql             # Schéma de la base de données
├── helpers/
│   └── functions.php          # Fonctions utilitaires
├── includes/
│   ├── header.php             # En-tête HTML
│   └── footer.php             # Pied de page HTML
├── models/
│   ├── User.php               # Modèle utilisateur
│   ├── FreightOffer.php       # Modèle offres de fret
│   ├── VehicleOffer.php       # Modèle offres de véhicules
│   ├── Message.php            # Modèle messagerie
│   ├── Rating.php             # Modèle évaluations
│   └── Transaction.php        # Modèle transactions
├── uploads/                   # Fichiers uploadés
├── logs/                      # Fichiers de logs
├── index.php                  # Page d'accueil
├── register.php               # Inscription
├── login.php                  # Connexion
├── dashboard.php              # Tableau de bord
└── README.md                  # Ce fichier
```

## 🔒 Sécurité

L'application intègre plusieurs mesures de sécurité:

- Protection CSRF sur tous les formulaires
- Hashage des mots de passe avec bcrypt
- Validation et échappement des données
- Protection contre les injections SQL (requêtes préparées)
- Protection XSS
- Sessions sécurisées
- Logs d'activité

## 🛠️ Technologies utilisées

- **Backend**: PHP 7.4+
- **Base de données**: MySQL 5.7+
- **Frontend**:
  - HTML5
  - CSS3
  - JavaScript ES6+
  - Bootstrap 5.3
  - Bootstrap Icons
  - jQuery 3.7

## 📊 Base de données

### Tables principales

- `users` - Utilisateurs (transporteurs et chargeurs)
- `freight_offers` - Offres de fret
- `vehicle_offers` - Offres de véhicules
- `conversations` - Conversations de messagerie
- `messages` - Messages
- `ratings` - Évaluations
- `transactions` - Transactions/contrats
- `notifications` - Notifications
- `favorites` - Favoris/signets
- `saved_searches` - Recherches sauvegardées
- `user_documents` - Documents utilisateurs
- `activity_logs` - Logs d'activité

## 🎨 Personnalisation

### Modifier les couleurs

Éditer le fichier `assets/css/style.css`:

```css
:root {
    --primary-color: #02475E;
    --secondary-color: #FF0054;
    --success-color: #28a745;
    --info-color: #17a2b8;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
}
```

### Modifier le nom de l'application

Éditer le fichier `config/config.php`:

```php
define('APP_NAME', 'Votre nom d\'application');
```

## 📝 API Google Maps

Pour activer la géolocalisation et les cartes:

1. Obtenir une clé API Google Maps: https://developers.google.com/maps/documentation/javascript/get-api-key
2. Activer les APIs suivantes:
   - Maps JavaScript API
   - Geocoding API
   - Places API
3. Ajouter la clé dans `config/config.php`

## 🐛 Débogage

Les logs sont stockés dans le dossier `logs/`:
- `error.log` - Erreurs PHP
- `activity.log` - Activités utilisateurs

Pour activer le mode debug, éditer `config/config.php`:

```php
define('DEBUG_MODE', true);
```

## 📧 Configuration email

Pour activer l'envoi d'emails (vérification, notifications):

Éditer `config/config.php`:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'votre-email@gmail.com');
define('SMTP_PASSWORD', 'votre-mot-de-passe-app');
```

## 🚀 Développements futurs

- [ ] Application mobile (React Native)
- [ ] API REST complète
- [ ] Intégration paiement en ligne
- [ ] Système de facturation automatique
- [ ] Module de reporting avancé
- [ ] Intégration avec TMS (Transport Management System)
- [ ] Module d'optimisation de tournées
- [ ] Intelligence artificielle pour matching automatique

## 📄 Licence

Ce projet est sous licence MIT.

## 👥 Support

Pour toute question ou problème:
- Ouvrir une issue sur GitHub
- Consulter la documentation
- Contacter le support technique

## 🙏 Remerciements

Inspiré par Teleroute, leader européen des bourses de fret.

---

Développé avec ❤️ pour la communauté du transport routier
