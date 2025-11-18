# Teleroute Marketplace - Documentation Développeur

## Table des matières

1. [Architecture](#architecture)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Structure du projet](#structure-du-projet)
5. [Base de données](#base-de-données)
6. [API](#api)
7. [Modèles](#modèles)
8. [Sécurité](#sécurité)
9. [Déploiement](#déploiement)
10. [Tests](#tests)
11. [Contribution](#contribution)

---

## Architecture

### Stack Technologique

- **Backend**: PHP 8.2+ avec architecture MVC
- **Base de données**: MySQL 8.0 / MariaDB 10.5+
- **Frontend**: Bootstrap 5.3, jQuery 3.7, Vanilla JS
- **Cartes**: Leaflet.js + OpenStreetMap
- **Graphiques**: Chart.js
- **Containerisation**: Docker + Docker Compose

### Pattern MVC

```
┌─────────────┐
│   Client    │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│    View     │ (*.php pages)
│  (Template) │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Controller  │ (Business logic in pages)
│   (Logic)   │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│    Model    │ (User, FreightOffer, etc.)
│   (Data)    │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  Database   │
└─────────────┘
```

---

## Installation

### Prérequis

- PHP 8.2+
- MySQL 8.0+ / MariaDB 10.5+
- Apache 2.4+ ou Nginx
- Composer (optionnel)
- Node.js 18+ (optionnel, pour assets)

### Installation Locale

#### Option 1: Installation manuelle

```bash
# 1. Cloner le repository
git clone https://github.com/your-repo/teleroute-marketplace.git
cd teleroute-marketplace

# 2. Créer la base de données
mysql -u root -p
CREATE DATABASE teleroute_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'teleroute_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON teleroute_marketplace.* TO 'teleroute_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# 3. Importer le schéma
mysql -u teleroute_user -p teleroute_marketplace < database/schema.sql

# 4. Configurer l'environnement
cp .env.example .env
nano .env  # Éditer les variables

# 5. Configurer les permissions
chmod 755 uploads logs cache
chown -R www-data:www-data .

# 6. Démarrer Apache
sudo systemctl start apache2
```

#### Option 2: Docker (Recommandé)

```bash
# 1. Cloner le repository
git clone https://github.com/your-repo/teleroute-marketplace.git
cd teleroute-marketplace

# 2. Démarrer les conteneurs
docker-compose up -d

# 3. Importer le schéma
docker-compose exec db mysql -u root -proot_password teleroute_marketplace < database/schema.sql

# 4. Accéder à l'application
open http://localhost:8080
```

### Données de Test

Pour générer des données de test:

```bash
php generate-test-data.php
```

Cela créera:
- 10 utilisateurs (mot de passe: `password123`)
- 30+ offres de fret
- 25+ offres de véhicules
- Notifications pour tous les utilisateurs

---

## Configuration

### Variables d'environnement (.env)

Créer un fichier `.env` à la racine:

```env
# Base de données
DB_HOST=localhost
DB_NAME=teleroute_marketplace
DB_USER=teleroute_user
DB_PASS=your_secure_password

# Application
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

# Session
SESSION_LIFETIME=7200

# Mail (optionnel)
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
```

### Configuration Apache

Fichier `/etc/apache2/sites-available/teleroute.conf`:

```apache
<VirtualHost *:80>
    ServerName teleroute.local
    DocumentRoot /var/www/teleroute-marketplace

    <Directory /var/www/teleroute-marketplace>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/teleroute-error.log
    CustomLog ${APACHE_LOG_DIR}/teleroute-access.log combined
</VirtualHost>
```

Activer le site:
```bash
sudo a2ensite teleroute.conf
sudo systemctl reload apache2
```

---

## Structure du projet

```
teleroute-marketplace/
├── admin/                  # Panel d'administration
│   ├── index.php          # Dashboard admin
│   ├── users.php          # Gestion utilisateurs
│   ├── offers.php         # Gestion offres
│   └── logs.php           # Visualiseur de logs
├── api/                   # API REST endpoints
│   ├── favorites.php      # CRUD favoris
│   ├── map-data.php       # Données carte
│   ├── messages.php       # Messagerie temps réel
│   └── notifications.php  # Notifications
├── assets/                # Ressources statiques
│   ├── css/              # Feuilles de style
│   ├── js/               # JavaScript
│   └── img/              # Images
├── config/               # Configuration
│   ├── config.php        # Configuration principale
│   └── database.php      # Connexion BDD
├── database/             # Base de données
│   └── schema.sql        # Schéma SQL
├── exports/              # Scripts d'export
│   ├── freight-csv.php   # Export fret CSV
│   ├── vehicles-csv.php  # Export véhicules CSV
│   └── transactions-csv.php
├── helpers/              # Fonctions utilitaires
│   ├── functions.php     # Helpers généraux
│   └── Logger.php        # Système de logging
├── includes/             # Templates partiels
│   ├── header.php        # En-tête
│   └── footer.php        # Pied de page
├── logs/                 # Logs applicatifs
├── models/               # Modèles de données
│   ├── User.php          # Modèle utilisateur
│   ├── FreightOffer.php  # Modèle offre fret
│   ├── VehicleOffer.php  # Modèle véhicule
│   ├── Message.php       # Modèle message
│   ├── Rating.php        # Modèle notation
│   └── Transaction.php   # Modèle transaction
├── scripts/              # Scripts de déploiement
│   ├── deploy.sh         # Déploiement production
│   ├── backup.sh         # Sauvegarde
│   └── setup.sh          # Configuration serveur
├── uploads/              # Fichiers uploadés
├── .htaccess             # Configuration Apache
├── docker-compose.yml    # Configuration Docker
├── Dockerfile            # Image Docker
├── CHANGELOG.md          # Historique versions
├── DEVELOPER.md          # Cette documentation
└── README.md             # Documentation utilisateur
```

---

## Base de données

### Schéma

#### Table `users`
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('transporter', 'shipper', 'both') NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    siret VARCHAR(14),
    address TEXT,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_ratings INT DEFAULT 0,
    is_verified BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

#### Tables principales

1. **users** - Comptes utilisateurs (transporteurs/chargeurs)
2. **freight_offers** - Offres de fret à transporter
3. **vehicle_offers** - Offres de véhicules disponibles
4. **conversations** - Fils de discussion
5. **messages** - Messages entre utilisateurs
6. **ratings** - Évaluations et avis
7. **transactions** - Historique des transactions
8. **notifications** - Notifications utilisateurs
9. **favorites** - Offres favorites
10. **saved_searches** - Recherches sauvegardées / Alertes
11. **user_documents** - Documents (KBIS, assurance, etc.)
12. **activity_logs** - Logs d'activité

### Migrations

Actuellement, le schéma est géré via `database/schema.sql`. Pour les futures migrations:

```bash
# Créer une migration
php database/create_migration.php "add_column_to_users"

# Exécuter les migrations
php database/migrate.php
```

### Indexes

Les indexes suivants sont créés pour optimiser les performances:

```sql
-- Offres de fret
CREATE INDEX idx_freight_dates ON freight_offers(loading_date, delivery_date);
CREATE INDEX idx_freight_cities ON freight_offers(loading_city, delivery_city);

-- Offres de véhicules
CREATE INDEX idx_vehicle_dates ON vehicle_offers(departure_date);
CREATE INDEX idx_vehicle_cities ON vehicle_offers(departure_city, destination_city);

-- Messages
CREATE INDEX idx_messages_receiver ON messages(receiver_id, is_read);
```

---

## API

### Endpoints REST

#### Favoris (`/api/favorites.php`)

**GET** - Récupérer les favoris
```http
GET /api/favorites.php?action=list&type=freight
```

**POST** - Ajouter aux favoris
```http
POST /api/favorites.php
Content-Type: application/json

{
    "action": "add",
    "offer_type": "freight",
    "offer_id": 123
}
```

**DELETE** - Retirer des favoris
```http
POST /api/favorites.php
Content-Type: application/json

{
    "action": "remove",
    "favorite_id": 45
}
```

#### Notifications (`/api/notifications.php`)

**GET** - Compteur de notifications non lues
```http
GET /api/notifications.php?action=count
Response: {"count": 5}
```

**POST** - Marquer comme lu
```http
POST /api/notifications.php
{
    "action": "mark_read",
    "notification_id": 123
}
```

#### Messages (`/api/messages.php`)

**GET** - Récupérer les messages d'une conversation
```http
GET /api/messages.php?conversation_id=456&limit=50
```

**POST** - Envoyer un message
```http
POST /api/messages.php
{
    "receiver_id": 789,
    "message": "Bonjour, est-ce que l'offre est toujours disponible?"
}
```

#### Carte (`/api/map-data.php`)

**GET** - Données pour la carte
```http
GET /api/map-data.php?type=freight
Response: {
    "freight": [
        {
            "id": 1,
            "departure_lat": 48.8566,
            "departure_lng": 2.3522,
            "arrival_lat": 45.7640,
            "arrival_lng": 4.8357,
            "weight": 15000,
            "price": 850
        }
    ]
}
```

### Authentification

Toutes les API nécessitant une authentification utilisent la session PHP:

```php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
```

### Format de réponse

Toutes les API retournent du JSON:

```json
// Succès
{
    "success": true,
    "data": {...}
}

// Erreur
{
    "success": false,
    "error": "Message d'erreur"
}
```

---

## Modèles

### Utilisation des modèles

```php
// Créer une instance
$freightModel = new FreightOffer();

// Créer une offre
$offerId = $freightModel->create([
    'user_id' => 1,
    'loading_city' => 'Paris',
    'delivery_city' => 'Lyon',
    'weight' => 15000,
    'price' => 850,
    // ...
]);

// Récupérer une offre
$offer = $freightModel->getById($offerId);

// Mettre à jour
$freightModel->update($offerId, ['price' => 900]);

// Supprimer (soft delete)
$freightModel->delete($offerId);

// Rechercher
$offers = $freightModel->search([
    'loading_city' => 'Paris',
    'min_weight' => 10000,
    'max_weight' => 20000
]);
```

### Modèle de base

Tous les modèles étendent une classe de base (à créer):

```php
abstract class Model {
    protected $db;
    protected $table;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ... autres méthodes communes
}
```

---

## Sécurité

### Protection CSRF

Toutes les formulaires incluent un token CSRF:

```php
// Générer un token
<input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

// Vérifier le token
if (!verifyCsrfToken($_POST['csrf_token'])) {
    die('CSRF token invalid');
}
```

### Protection XSS

Utiliser `h()` pour échapper les données:

```php
echo h($user['company_name']); // Échappe les caractères HTML
```

### Protection SQL Injection

Toujours utiliser des requêtes préparées:

```php
// ✅ BON
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// ❌ MAUVAIS - NE JAMAIS FAIRE
$result = $db->query("SELECT * FROM users WHERE email = '$email'");
```

### Hashage des mots de passe

```php
// Hasher
$hash = password_hash($password, PASSWORD_BCRYPT);

// Vérifier
if (password_verify($password, $hash)) {
    // Mot de passe correct
}
```

### Headers de sécurité

Définis dans `.htaccess`:

```apache
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set X-Content-Type-Options "nosniff"
Header always set Content-Security-Policy "default-src 'self'..."
```

### Validation des entrées

```php
// Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Email invalide');
}

// Nombre
$price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);

// Texte
$city = trim(strip_tags($_POST['city']));
```

---

## Déploiement

### Déploiement Manuel

```bash
# 1. Créer une sauvegarde
./scripts/backup.sh full

# 2. Mettre à jour le code
git pull origin main

# 3. Mettre à jour les dépendances
composer install --no-dev --optimize-autoloader

# 4. Migrer la base de données
mysql -u user -p database < database/migrations/2024_11_18_new_feature.sql

# 5. Vider le cache
rm -rf cache/*

# 6. Redémarrer Apache
sudo systemctl reload apache2
```

### Déploiement Automatisé

```bash
./scripts/deploy.sh production main
```

Le script effectue automatiquement:
1. Backup
2. Activation mode maintenance
3. Pull du code
4. Installation dépendances
5. Migrations BDD
6. Cache clear
7. Permissions
8. Redémarrage services
9. Vérification santé
10. Rollback si erreur

### Configuration Serveur

Pour un nouveau serveur:

```bash
sudo ./scripts/setup.sh
```

Ce script installe et configure:
- Apache
- PHP 8.2 + extensions
- MySQL
- Composer
- Node.js
- Certificat SSL
- Firewall
- Cron jobs

---

## Tests

### Tests Manuels

1. **Authentification**
   - Inscription nouveau compte
   - Connexion
   - Déconnexion
   - Mot de passe oublié

2. **Offres**
   - Créer offre fret/véhicule
   - Modifier offre
   - Supprimer offre
   - Rechercher offres

3. **Messagerie**
   - Envoyer message
   - Recevoir message
   - Marquer comme lu

4. **Transactions**
   - Créer transaction
   - Évaluer partenaire

### Tests de Charge

```bash
# Apache Bench
ab -n 1000 -c 10 http://localhost/

# wrk
wrk -t12 -c400 -d30s http://localhost/
```

### Tests de Sécurité

```bash
# OWASP ZAP
zap-cli quick-scan http://localhost/

# Scan SQLi
sqlmap -u "http://localhost/search-freight.php?city=Paris"
```

---

## Logging

### Utilisation du Logger

```php
$logger = Logger::getInstance();

// Différents niveaux
$logger->emergency('System is down!');
$logger->alert('Action required immediately');
$logger->critical('Critical condition');
$logger->error('Runtime error', ['error' => $e->getMessage()]);
$logger->warning('Warning message');
$logger->notice('Normal but significant');
$logger->info('Informational message');
$logger->debug('Debug information');

// Canaux spécifiques
$logger->security('warning', 'Failed login attempt', ['ip' => $_SERVER['REMOTE_ADDR']]);
$logger->database('error', 'Query failed', ['query' => $sql]);
$logger->api('info', 'API request', ['endpoint' => '/api/favorites.php']);
```

### Visualiser les logs

Interface admin: `/admin/logs.php`

Ou en ligne de commande:
```bash
tail -f logs/app-2024-11-18.log
tail -f logs/security-2024-11-18.log
```

---

## Contribution

### Workflow Git

```bash
# 1. Créer une branche
git checkout -b feature/nouvelle-fonctionnalite

# 2. Développer et commiter
git add .
git commit -m "feat: Ajout de la nouvelle fonctionnalité"

# 3. Pousser
git push origin feature/nouvelle-fonctionnalite

# 4. Créer une Pull Request sur GitHub
```

### Convention de commit

Format: `type(scope): description`

Types:
- `feat`: Nouvelle fonctionnalité
- `fix`: Correction de bug
- `docs`: Documentation
- `style`: Formatage
- `refactor`: Refactorisation
- `test`: Tests
- `chore`: Maintenance

Exemples:
```
feat(search): Ajout filtres avancés
fix(auth): Correction validation email
docs(api): Documentation endpoints REST
refactor(models): Simplification User model
```

### Standards de code

- **PHP**: PSR-12
- **JavaScript**: Standard JS
- **SQL**: snake_case pour tables/colonnes
- **CSS**: BEM methodology

### Pull Request Checklist

- [ ] Code testé localement
- [ ] Pas de console.log / var_dump
- [ ] Documentation mise à jour
- [ ] CHANGELOG.md mis à jour
- [ ] Pas de credentials en dur
- [ ] Tests passent
- [ ] Code review demandé

---

## Ressources

### Documentation externe

- [PHP Manual](https://www.php.net/manual/fr/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Bootstrap 5 Docs](https://getbootstrap.com/docs/5.3/)
- [Leaflet.js](https://leafletjs.com/)
- [Chart.js](https://www.chartjs.org/)

### Outils recommandés

- **IDE**: PHPStorm, VS Code
- **Database**: MySQL Workbench, phpMyAdmin
- **API Testing**: Postman, Insomnia
- **Git GUI**: GitKraken, SourceTree

### Support

- Email: dev@teleroute-marketplace.com
- Slack: #dev-teleroute
- Issues: https://github.com/your-repo/teleroute-marketplace/issues

---

**Version**: 1.0.0
**Dernière mise à jour**: 18 Novembre 2024
**Auteur**: Équipe Teleroute Marketplace
