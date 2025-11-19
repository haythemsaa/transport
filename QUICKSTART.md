# 🚀 Démarrage Rapide - Teleroute Marketplace

Guide pour lancer l'application en **moins de 5 minutes** et commencer à l'utiliser immédiatement.

---

## ⚡ Installation Express (Docker)

### Prérequis
- Docker & Docker Compose installés
- Port 80 et 3306 disponibles

### Étapes (3 commandes)

```bash
# 1. Cloner le projet
git clone https://github.com/votre-repo/teleroute-marketplace.git
cd teleroute-marketplace

# 2. Configurer l'environnement
cp .env.example .env

# 3. Lancer avec Docker
docker-compose up -d
```

✅ **C'est tout !** L'application est accessible sur **http://localhost**

### Créer un compte admin

```bash
docker-compose exec web php database/seed-demo-data.php --admin
```

**Identifiants par défaut :**
- Email : `admin@teleroute.com`
- Mot de passe : `Admin123!`

---

## 🖥️ Installation Manuelle (sans Docker)

### Prérequis
- PHP 8.2+ avec extensions : `pdo_mysql`, `mbstring`, `json`, `session`
- MySQL 8.0+
- Apache ou Nginx
- Composer (optionnel)

### Installation en 5 étapes

#### 1️⃣ Configurer la base de données

```bash
# Créer la base de données
mysql -u root -p << EOF
CREATE DATABASE teleroute_marketplace CHARACTER SET utf8mb4;
CREATE USER 'teleroute_user'@'localhost' IDENTIFIED BY 'votre_mot_de_passe';
GRANT ALL PRIVILEGES ON teleroute_marketplace.* TO 'teleroute_user'@'localhost';
FLUSH PRIVILEGES;
EOF

# Importer le schéma
mysql -u teleroute_user -p teleroute_marketplace < database/schema.sql
```

#### 2️⃣ Configurer l'application

```bash
# Copier le fichier de configuration
cp .env.example .env

# Éditer .env avec vos informations
nano .env
```

**Configuration minimale :**
```env
DB_HOST=localhost
DB_NAME=teleroute_marketplace
DB_USER=teleroute_user
DB_PASS=votre_mot_de_passe
APP_URL=http://localhost
```

#### 3️⃣ Créer les dossiers nécessaires

```bash
# Créer et configurer les permissions
mkdir -p uploads logs sessions cache backups
chmod 755 uploads logs sessions cache backups
chown -R www-data:www-data uploads logs sessions cache backups
```

#### 4️⃣ Configurer le serveur web

**Apache (.htaccess déjà présent) :**
```bash
# Activer mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Nginx :**
```bash
# Copier la configuration
sudo cp config/nginx.conf /etc/nginx/sites-available/teleroute
sudo ln -s /etc/nginx/sites-available/teleroute /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 5️⃣ Générer des données de test (optionnel)

```bash
php database/seed-demo-data.php
```

---

## 🎯 Premiers pas

### 1. Créer un compte

Allez sur **http://localhost/register.php**

**Types de compte :**
- 🚚 **Transporteur** : Rechercher du fret, proposer des véhicules
- 📦 **Chargeur** : Publier des offres de fret
- 🔄 **Les deux** : Accès complet

### 2. Explorer les fonctionnalités

| Fonctionnalité | URL | Description |
|----------------|-----|-------------|
| **Dashboard** | `/dashboard.php` | Vue d'ensemble de vos activités |
| **Rechercher du fret** | `/search-freight.php` | Trouver des chargements |
| **Rechercher des véhicules** | `/search-vehicles.php` | Trouver des transporteurs |
| **Publier une offre** | `/post-freight.php` | Créer une offre de fret |
| **Proposer un véhicule** | `/post-vehicle.php` | Ajouter votre véhicule |
| **Messages** | `/messages.php` | Communication sécurisée |
| **Favoris** | `/favorites.php` | Vos offres sauvegardées |
| **Alertes** | `/alerts.php` | Notifications automatiques |

### 3. Publier votre première offre

**Pour les chargeurs :**
1. Allez sur `/post-freight.php`
2. Remplissez les informations de transport
3. Publiez l'offre
4. Recevez des propositions de transporteurs

**Pour les transporteurs :**
1. Allez sur `/post-vehicle.php`
2. Ajoutez les détails de votre véhicule
3. Publiez votre disponibilité
4. Recevez des demandes de chargement

---

## 🔧 Commandes Utiles

### Avec Makefile (recommandé)

```bash
make install          # Installation complète
make start            # Démarrer Docker
make stop             # Arrêter Docker
make restart          # Redémarrer
make logs             # Voir les logs
make test             # Lancer les tests
make backup           # Backup de la BDD
make clean            # Nettoyer les fichiers temporaires
```

### Sans Makefile

```bash
# Démarrer Docker
docker-compose up -d

# Voir les logs
docker-compose logs -f web

# Arrêter
docker-compose down

# Backup BDD
./scripts/backup.sh full

# Restaurer BDD
./scripts/restore.sh backups/backup-2024-11-18.sql.gz

# Tests
vendor/bin/phpunit

# Logs applicatifs
tail -f logs/app.log
```

---

## 📊 Données de Démonstration

### Générer des données de test

```bash
# Générer 50 offres de fret, 30 véhicules, 20 utilisateurs
php database/seed-demo-data.php --users=20 --freight=50 --vehicles=30

# Générer uniquement un admin
php database/seed-demo-data.php --admin-only

# Mode complet (avec messages, favoris, notifications)
php database/seed-demo-data.php --full
```

### Comptes de test générés

| Type | Email | Mot de passe |
|------|-------|--------------|
| Admin | `admin@teleroute.com` | `Admin123!` |
| Transporteur | `carrier1@example.com` | `Carrier123!` |
| Chargeur | `shipper1@example.com` | `Shipper123!` |
| Les deux | `both1@example.com` | `Both123!` |

---

## 🔍 Vérification de l'Installation

### Health Check

Visitez **http://localhost/health-check.php**

Vérifications effectuées :
- ✅ Version PHP (8.2+)
- ✅ Connexion base de données
- ✅ Extensions PHP requises
- ✅ Permissions des dossiers
- ✅ Fichiers de configuration
- ✅ Tables de la base de données

### Tests Automatisés

```bash
# Lancer tous les tests
vendor/bin/phpunit

# Tests spécifiques
vendor/bin/phpunit tests/DatabaseTest.php
vendor/bin/phpunit tests/AuthTest.php
vendor/bin/phpunit tests/ValidatorTest.php

# Avec couverture
vendor/bin/phpunit --coverage-html coverage/
```

---

## 🌐 Accès aux Interfaces

| Interface | URL | Port | Credentials |
|-----------|-----|------|-------------|
| **Application** | http://localhost | 80 | Compte créé |
| **PHPMyAdmin** | http://localhost:8080 | 8080 | teleroute_user / password |
| **API Documentation** | http://localhost/api-docs.html | 80 | - |
| **Health Check** | http://localhost/health-check.php | 80 | - |

---

## 📱 Test de l'API

### Avec cURL

```bash
# Récupérer les offres de fret
curl -X GET "http://localhost/api/freight.php?limit=10"

# Récupérer les véhicules disponibles
curl -X GET "http://localhost/api/vehicles.php?limit=10"

# Recherche avec filtres
curl -X GET "http://localhost/api/freight.php?origin_country=FR&destination_country=DE"
```

### Avec Postman

1. Importer la collection : `postman/Teleroute-Marketplace.postman_collection.json`
2. Configurer l'environnement : Base URL = `http://localhost`
3. Tester les endpoints

---

## ⚙️ Configuration Avancée

### Activer le cache Redis

```bash
# Démarrer Redis
docker-compose up -d redis

# Activer dans .env
REDIS_ENABLED=true
REDIS_HOST=redis
REDIS_PORT=6379
```

### Configurer les emails

```env
# SMTP Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe-app
MAIL_FROM_ADDRESS=noreply@teleroute-marketplace.com
MAIL_FROM_NAME=Teleroute Marketplace
```

### Activer les cron jobs

```bash
# Éditer crontab
crontab -e

# Ajouter les tâches
0 * * * * php /var/www/teleroute-marketplace/cron/process-alerts.php
0 7 * * * php /var/www/teleroute-marketplace/cron/clean-expired-offers.php
0 2 * * * /var/www/teleroute-marketplace/scripts/backup.sh full
```

### Mode debug

```env
# Dans .env
APP_ENV=development
APP_DEBUG=true
LOG_LEVEL=debug
```

---

## 🆘 Problèmes Courants

### Erreur de connexion à la base de données

```bash
# Vérifier que MySQL est démarré
sudo systemctl status mysql

# Tester la connexion
mysql -u teleroute_user -p teleroute_marketplace

# Vérifier les credentials dans .env
cat .env | grep DB_
```

### Erreurs de permissions

```bash
# Donner les bonnes permissions
sudo chown -R www-data:www-data uploads logs sessions cache
sudo chmod -R 755 uploads logs sessions cache
```

### Page blanche / Erreur 500

```bash
# Activer l'affichage des erreurs
echo "APP_DEBUG=true" >> .env

# Vérifier les logs
tail -f logs/app.log
tail -f /var/log/apache2/error.log  # ou nginx
```

### Docker ne démarre pas

```bash
# Vérifier les ports
sudo netstat -tulpn | grep -E '80|3306|8080'

# Arrêter les services conflictuels
sudo systemctl stop apache2  # ou nginx
sudo systemctl stop mysql

# Redémarrer Docker
docker-compose down
docker-compose up -d
```

---

## 📚 Documentation Complète

- 📖 **README.md** - Documentation générale
- 🔧 **DEVELOPER.md** - Guide développeur
- 🔒 **SECURITY.md** - Politique de sécurité
- 🤝 **CONTRIBUTING.md** - Guide de contribution
- 📡 **API.md** - Documentation API
- ❓ **FAQ.md** - Questions fréquentes

---

## 🎓 Tutoriels Vidéo (à venir)

- Installation en 5 minutes
- Publier sa première offre
- Utiliser la recherche avancée
- Configurer les alertes automatiques
- Gérer son profil professionnel

---

## 💡 Conseils pour Démarrer

### Pour les Transporteurs

1. **Complétez votre profil** avec licence de transport et assurance
2. **Ajoutez vos véhicules** avec disponibilités
3. **Configurez des alertes** pour recevoir les nouvelles offres
4. **Consultez régulièrement** le tableau de bord
5. **Répondez rapidement** aux demandes

### Pour les Chargeurs

1. **Créez un compte entreprise** vérifié
2. **Publiez des offres détaillées** (poids, volume, dates)
3. **Utilisez les filtres** pour trouver les bons transporteurs
4. **Vérifiez les avis** et l'historique
5. **Communiquez clairement** via la messagerie

---

## 🚀 Passer en Production

### Checklist avant déploiement

- [ ] Changer tous les mots de passe par défaut
- [ ] Configurer HTTPS (Let's Encrypt)
- [ ] Désactiver le mode debug (`APP_DEBUG=false`)
- [ ] Configurer les backups automatiques
- [ ] Activer les logs de sécurité
- [ ] Configurer le firewall
- [ ] Tester la restauration de backup
- [ ] Configurer le monitoring
- [ ] Vérifier les performances
- [ ] Tester les emails

### Script de déploiement

```bash
# Mode production
./scripts/deploy.sh production

# Avec backup automatique
./scripts/deploy.sh production --backup
```

---

## 📞 Support

- 📧 Email : support@teleroute-marketplace.com
- 💬 Discord : [Rejoindre](https://discord.gg/teleroute)
- 📖 Documentation : https://docs.teleroute-marketplace.com
- 🐛 Bugs : https://github.com/votre-repo/issues

---

## ⭐ Prochaines Étapes

1. **Explorer l'interface** - Familiarisez-vous avec toutes les fonctionnalités
2. **Tester avec des données réelles** - Commencez à publier de vraies offres
3. **Configurer les notifications** - Email, SMS, push
4. **Inviter votre équipe** - Créez plusieurs comptes utilisateurs
5. **Personnaliser** - Logo, couleurs, textes

---

**🎉 Félicitations ! Vous êtes prêt à utiliser Teleroute Marketplace !**

Pour toute question, consultez la **FAQ.md** ou contactez le support.
