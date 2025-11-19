# ❓ FAQ - Questions Fréquentes

Guide de résolution de problèmes et réponses aux questions courantes sur Teleroute Marketplace.

---

## 📋 Table des matières

- [Installation](#installation)
- [Configuration](#configuration)
- [Base de données](#base-de-données)
- [Docker](#docker)
- [Erreurs Courantes](#erreurs-courantes)
- [Performance](#performance)
- [Sécurité](#sécurité)
- [API](#api)
- [Fonctionnalités](#fonctionnalités)

---

## 🛠️ Installation

### Q: Comment installer l'application rapidement ?

**R:** Utilisez Docker pour une installation en 3 commandes :

```bash
cp .env.example .env
docker-compose up -d
```

Consultez [QUICKSTART.md](QUICKSTART.md) pour le guide complet.

### Q: Quels sont les prérequis système ?

**R:**
- **Avec Docker**: Docker 20.10+ et Docker Compose 1.29+
- **Sans Docker**: PHP 8.2+, MySQL 8.0+, Apache 2.4+ ou Nginx 1.18+

### Q: L'installation échoue avec "Permission denied"

**R:** Problème de permissions. Exécutez :

```bash
sudo chown -R www-data:www-data /var/www/teleroute-marketplace
sudo chmod -R 755 uploads logs sessions cache backups
```

### Q: Comment installer sur Windows ?

**R:** Utilisez Docker Desktop ou WSL2 :

```bash
# Avec WSL2
wsl --install
# Puis suivre l'installation normale
```

---

## ⚙️ Configuration

### Q: Où se trouve le fichier de configuration ?

**R:** Il y a plusieurs fichiers :
- `.env` - Variables d'environnement (principal)
- `config/config.php` - Configuration PHP
- `config/nginx.conf` - Configuration Nginx (si utilisé)

### Q: Comment configurer les emails ?

**R:** Éditez `.env` :

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe-app
MAIL_FROM_ADDRESS=noreply@votre-domaine.com
```

**Pour Gmail**, créez un "Mot de passe d'application" dans les paramètres Google.

### Q: Comment changer l'URL de l'application ?

**R:** Modifiez `APP_URL` dans `.env` :

```env
APP_URL=https://votre-domaine.com
```

### Q: Comment activer le mode debug ?

**R:** Dans `.env` :

```env
APP_ENV=development
APP_DEBUG=true
LOG_LEVEL=debug
```

⚠️ **Ne jamais activer en production !**

---

## 🗄️ Base de données

### Q: Comment créer la base de données ?

**R:** Utilisez le script d'initialisation :

```bash
# Avec Docker
docker-compose exec web bash database/init.sh

# Sans Docker
mysql -u root -p < database/schema.sql
```

### Q: Erreur "Access denied for user"

**R:** Vérifiez les credentials dans `.env` :

```bash
# Tester la connexion
mysql -h localhost -u teleroute_user -p

# Recréer l'utilisateur si nécessaire
mysql -u root -p
CREATE USER 'teleroute_user'@'localhost' IDENTIFIED BY 'votre_password';
GRANT ALL PRIVILEGES ON teleroute_marketplace.* TO 'teleroute_user'@'localhost';
FLUSH PRIVILEGES;
```

### Q: Comment réinitialiser la base de données ?

**R:**

```bash
# Avec Makefile
make db-reset

# Manuellement
mysql -u teleroute_user -p teleroute_marketplace < database/schema.sql
```

⚠️ **Cela supprimera toutes les données !**

### Q: Comment générer des données de test ?

**R:**

```bash
# Données complètes
php database/seed-demo-data.php --full

# Nombre personnalisé
php database/seed-demo-data.php --users=50 --freight=100 --vehicles=75

# Admin seulement
php database/seed-demo-data.php --admin-only
```

### Q: Comment sauvegarder la base de données ?

**R:**

```bash
# Avec Makefile
make backup

# Avec script
./scripts/backup.sh full

# Manuellement
mysqldump -u teleroute_user -p teleroute_marketplace > backup.sql
```

---

## 🐳 Docker

### Q: Docker ne démarre pas - "Port already allocated"

**R:** Un service utilise déjà le port. Solutions :

```bash
# Option 1: Arrêter le service conflictuel
sudo systemctl stop apache2  # ou nginx
sudo systemctl stop mysql

# Option 2: Changer le port dans docker-compose.yml
ports:
  - "8080:80"  # Au lieu de "80:80"
```

### Q: Comment voir les logs Docker ?

**R:**

```bash
# Tous les logs
docker-compose logs -f

# Service spécifique
docker-compose logs -f web
docker-compose logs -f mysql

# Dernières 100 lignes
docker-compose logs --tail=100
```

### Q: Comment accéder au shell d'un conteneur ?

**R:**

```bash
# Shell Web/PHP
docker-compose exec web bash

# Shell MySQL
docker-compose exec mysql bash

# MySQL client
docker-compose exec mysql mysql -uteleroute_user -p
```

### Q: Docker utilise trop d'espace disque

**R:** Nettoyez les données inutiles :

```bash
# Nettoyer images, conteneurs, volumes non utilisés
docker system prune -a --volumes

# Nettoyer uniquement les conteneurs arrêtés
docker container prune
```

### Q: Comment redémarrer les conteneurs ?

**R:**

```bash
# Avec Makefile
make restart

# Avec Docker Compose
docker-compose restart

# Reconstruire complètement
docker-compose down
docker-compose up -d --build
```

---

## 🐛 Erreurs Courantes

### Q: Page blanche / Erreur 500

**R:** Plusieurs causes possibles :

```bash
# 1. Vérifier les logs
tail -f logs/app.log
tail -f /var/log/apache2/error.log

# 2. Activer l'affichage des erreurs
echo "APP_DEBUG=true" >> .env

# 3. Vérifier les permissions
chmod -R 755 uploads logs sessions cache

# 4. Vérifier la syntaxe PHP
php -l fichier-problematique.php
```

### Q: "Class 'PDO' not found"

**R:** Extension PHP manquante :

```bash
# Ubuntu/Debian
sudo apt-get install php8.2-pdo php8.2-mysql

# Vérifier
php -m | grep pdo
```

### Q: "Headers already sent"

**R:** Espaces/retours à la ligne avant `<?php` :

```bash
# Chercher les fichiers problématiques
grep -r "^ *<?" .
```

Supprimez tous les espaces avant `<?php` dans les fichiers PHP.

### Q: Connexion à la base de données échoue

**R:**

```bash
# 1. Vérifier que MySQL est démarré
sudo systemctl status mysql

# 2. Tester la connexion
mysql -h localhost -u teleroute_user -p

# 3. Vérifier .env
cat .env | grep DB_

# 4. Vérifier les permissions MySQL
SHOW GRANTS FOR 'teleroute_user'@'localhost';
```

### Q: "CSRF token mismatch"

**R:**

```bash
# 1. Vider le cache et les sessions
make clean

# Ou manuellement
rm -rf cache/* sessions/*

# 2. Vérifier que les sessions fonctionnent
php -i | grep session.save_path
```

### Q: Upload de fichiers ne fonctionne pas

**R:**

```bash
# 1. Vérifier les permissions
chmod 755 uploads/
chown www-data:www-data uploads/

# 2. Vérifier la config PHP
php -i | grep upload_max_filesize
php -i | grep post_max_size

# 3. Augmenter les limites si nécessaire (.env ou php.ini)
UPLOAD_MAX_SIZE=20M
POST_MAX_SIZE=20M
```

---

## ⚡ Performance

### Q: L'application est lente

**R:** Plusieurs optimisations possibles :

```bash
# 1. Activer le cache Redis
docker-compose up -d redis
# Puis dans .env:
REDIS_ENABLED=true

# 2. Optimiser MySQL
# Ajouter des indexes manquants
# Analyser les requêtes lentes

# 3. Activer la compression GZIP
# Déjà configuré dans Nginx/Apache

# 4. Utiliser un CDN pour les assets statiques
```

### Q: Comment activer le cache Redis ?

**R:**

```bash
# 1. Démarrer Redis
docker-compose up -d redis

# 2. Installer l'extension PHP
sudo apt-get install php8.2-redis

# 3. Activer dans .env
REDIS_ENABLED=true
REDIS_HOST=redis  # ou localhost
REDIS_PORT=6379

# 4. Tester
php -r "var_dump(extension_loaded('redis'));"
```

### Q: Comment optimiser la base de données ?

**R:**

```bash
# Analyser les tables
mysqlcheck -o teleroute_marketplace -u root -p

# Optimiser les tables
OPTIMIZE TABLE freight_offers, vehicle_offers, users;

# Analyser les requêtes lentes
# Activer le slow query log dans MySQL
```

---

## 🔒 Sécurité

### Q: Comment sécuriser l'application en production ?

**R:** Checklist de sécurité :

```bash
# 1. Désactiver le debug
APP_DEBUG=false
APP_ENV=production

# 2. HTTPS uniquement
# Installer Let's Encrypt
sudo certbot --apache -d votre-domaine.com

# 3. Permissions correctes
chmod 600 .env
chmod 755 uploads logs
chown -R www-data:www-data .

# 4. Pare-feu
sudo ufw enable
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp

# 5. Changer tous les mots de passe par défaut
```

### Q: Comment protéger contre les injections SQL ?

**R:** **Déjà implémenté** - L'application utilise :
- PDO avec prepared statements (100%)
- Validation des entrées (Validator.php)
- Échappement des sorties (htmlspecialchars)

### Q: Comment configurer HTTPS ?

**R:**

```bash
# Avec Let's Encrypt (gratuit)
sudo apt-get install certbot python3-certbot-apache
sudo certbot --apache -d votre-domaine.com -d www.votre-domaine.com

# Le certificat se renouvelle automatiquement
sudo certbot renew --dry-run
```

### Q: Comment scanner les vulnérabilités ?

**R:**

```bash
# Avec Makefile
make security

# Scanner les dépendances PHP
composer audit

# Analyser le code
./vendor/bin/phpstan analyse
```

---

## 📡 API

### Q: Comment tester l'API ?

**R:** Plusieurs méthodes :

```bash
# 1. Avec Postman
# Importer: postman/Teleroute-Marketplace.postman_collection.json

# 2. Avec cURL
curl http://localhost/api/freight.php?limit=10

# 3. Avec navigateur
http://localhost/api/freight.php?limit=10
```

### Q: L'API retourne une erreur 401

**R:** Authentification requise. Assurez-vous d'être connecté :

```bash
# Vérifier la session
curl -c cookies.txt -b cookies.txt http://localhost/login.php
```

### Q: Comment documenter l'API ?

**R:** La documentation existe dans `API.md`. Pour générer une doc interactive :

```bash
# Utiliser Postman ou Swagger
# La collection Postman est prête à l'emploi
```

---

## 💡 Fonctionnalités

### Q: Comment publier une offre de fret ?

**R:**
1. Connexion avec compte chargeur ou "les deux"
2. Aller sur `/post-freight.php`
3. Remplir le formulaire
4. Publier

### Q: Comment créer des alertes automatiques ?

**R:**
1. Effectuer une recherche avec filtres
2. Cliquer sur "Sauvegarder cette recherche"
3. Activer les notifications email
4. Recevoir des alertes quotidiennes

### Q: Comment contacter un utilisateur ?

**R:**
1. Cliquer sur "Contacter" sur une offre
2. Envoyer un message
3. Communication via la messagerie interne

### Q: Puis-je supprimer mon compte ?

**R:** Oui, dans Paramètres > Supprimer mon compte.
Les données seront anonymisées conformément au RGPD.

### Q: Comment vérifier mon entreprise ?

**R:** Fonctionnalité en développement. Actuellement :
- Upload de licence de transport
- Upload d'assurance
- Vérification manuelle par admin

---

## 🔧 Troubleshooting Avancé

### Problème: Imports de fichiers lents

**Solution:**
```php
// Augmenter les timeouts PHP
set_time_limit(300);
ini_set('max_execution_time', 300);
```

### Problème: Sessions perdues

**Solution:**
```bash
# Vérifier le répertoire de sessions
ls -la sessions/
chmod 755 sessions/
chown www-data:www-data sessions/

# Dans php.ini ou .htaccess
session.gc_maxlifetime = 7200
session.cookie_lifetime = 7200
```

### Problème: Emails non envoyés

**Solution:**
```bash
# 1. Vérifier les logs
tail -f logs/email.log

# 2. Tester SMTP
telnet smtp.gmail.com 587

# 3. Vérifier .env
cat .env | grep MAIL_

# 4. Utiliser un service externe (SendGrid, Mailgun)
```

### Problème: Images uploadées ne s'affichent pas

**Solution:**
```bash
# 1. Vérifier les permissions
ls -la uploads/
chmod 755 uploads/
chmod 644 uploads/*

# 2. Vérifier le chemin
# Les images doivent être accessibles via HTTP

# 3. Vérifier le .htaccess (Apache)
# Ne pas bloquer l'accès au dossier uploads/
```

---

## 📞 Support

### Comment obtenir de l'aide ?

1. **Documentation**
   - README.md - Vue d'ensemble
   - QUICKSTART.md - Démarrage rapide
   - DEVELOPER.md - Guide développeur
   - Cette FAQ

2. **Logs**
   - `logs/app.log` - Logs applicatifs
   - `logs/error.log` - Erreurs PHP
   - `logs/security.log` - Événements de sécurité

3. **Communauté**
   - GitHub Issues
   - Discord
   - Email: support@teleroute-marketplace.com

4. **Debugging**
   ```bash
   # Activer le mode debug
   APP_DEBUG=true

   # Vérifier la configuration
   make check

   # Tester la santé de l'app
   curl http://localhost/health-check.php
   ```

---

## 🎯 Cas d'Usage Courants

### Scénario 1: Déploiement Production

```bash
# 1. Backup avant déploiement
make backup

# 2. Déployer
./scripts/deploy.sh production

# 3. Vérifier
make health

# 4. Surveiller
make logs
```

### Scénario 2: Migration de Serveur

```bash
# Sur l'ancien serveur
make backup
# Copier le backup et les uploads

# Sur le nouveau serveur
make install
# Restaurer backup
make restore RESTORE_FILE=backup.sql.gz
# Copier uploads/
```

### Scénario 3: Développement Local

```bash
# Installation rapide
make install-dev

# Travailler
make dev

# Tester
make test

# Nettoyer
make clean
```

---

## 📚 Ressources Utiles

- [Guide officiel PHP](https://www.php.net/manual/fr/)
- [Documentation MySQL](https://dev.mysql.com/doc/)
- [Documentation Docker](https://docs.docker.com/)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Bootstrap 5](https://getbootstrap.com/docs/5.3/)

---

**Dernière mise à jour**: 18 Novembre 2024

*Cette FAQ est maintenue activement. Si vous ne trouvez pas de réponse, ouvrez une issue sur GitHub.*
