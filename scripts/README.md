# Scripts de Déploiement et Maintenance

Ce dossier contient tous les scripts nécessaires pour le déploiement, la sauvegarde et la maintenance de Teleroute Marketplace.

## Scripts Disponibles

### 1. setup.sh - Configuration Initiale du Serveur

Configure un serveur vierge pour héberger l'application.

**Usage:**
```bash
sudo ./setup.sh
```

**Ce qu'il fait:**
- Installation d'Apache 2.4
- Installation de PHP 8.2 + extensions
- Installation de MySQL 8.0
- Installation de Composer
- Installation de Node.js & NPM
- Configuration d'Apache avec virtual host
- Configuration du firewall (UFW)
- Création de la structure de répertoires
- Configuration des cron jobs pour backups

**Prérequis:**
- Ubuntu 20.04+ / Debian 11+
- Accès root (sudo)
- Connexion Internet

**Après l'exécution:**
Les credentials de la base de données sont sauvegardés dans `/root/.teleroute-marketplace-credentials`

---

### 2. deploy.sh - Déploiement de l'Application

Déploie une nouvelle version de l'application en production.

**Usage:**
```bash
./deploy.sh [environment] [branch]
```

**Exemples:**
```bash
./deploy.sh production main
./deploy.sh staging develop
./deploy.sh development feature/new-feature
```

**Ce qu'il fait:**
1. Crée un backup complet avant déploiement
2. Active le mode maintenance
3. Pull le code depuis Git
4. Installe/met à jour les dépendances (Composer, NPM)
5. Exécute les migrations de base de données
6. Vide le cache
7. Configure les permissions
8. Redémarre les services (Apache, PHP-FPM)
9. Désactive le mode maintenance
10. Vérifie que le site fonctionne
11. **Rollback automatique en cas d'erreur**

**Variables d'environnement:**
```bash
APP_DIR="/var/www/teleroute-marketplace"
BACKUP_DIR="/var/backups/teleroute"
GIT_REPO="https://github.com/your-repo/teleroute-marketplace.git"
```

**Rollback manuel:**
Si besoin de revenir en arrière manuellement:
```bash
cd /var/backups/teleroute
tar -xzf backup_YYYYMMDD_HHMMSS.tar.gz -C /var/www/teleroute-marketplace
sudo systemctl reload apache2
```

---

### 3. backup.sh - Sauvegarde des Données

Crée des sauvegardes de la base de données et/ou des fichiers.

**Usage:**
```bash
./backup.sh [type]
```

**Types de backup:**
- `full` - Backup complet (BDD + fichiers)
- `database` - Backup de la base de données uniquement
- `files` - Backup des fichiers uniquement

**Exemples:**
```bash
./backup.sh full         # Backup complet (recommandé)
./backup.sh database     # Seulement la BDD
./backup.sh files        # Seulement les fichiers
```

**Ce qu'il fait:**
1. Crée un dump MySQL compressé (.sql.gz)
2. Archive les fichiers (tar.gz)
3. Vérifie l'intégrité du backup
4. Upload vers S3 (si configuré)
5. Nettoie les vieux backups (>30 jours)
6. Génère un rapport

**Configuration S3 (optionnel):**
Pour activer les backups offsite vers Amazon S3:
```bash
# Dans backup.sh, modifier:
S3_BUCKET="your-bucket-name"
S3_ENABLED=true

# Installer AWS CLI
apt-get install awscli
aws configure
```

**Restauration:**
```bash
# Base de données
gunzip backup_database.sql.gz
mysql -u user -p teleroute_marketplace < backup_database.sql

# Fichiers
tar -xzf backup_files.tar.gz -C /var/www/teleroute-marketplace

# Backup complet
tar -xzf backup_full.tar.gz
# Puis restaurer BDD et fichiers séparément
```

**Localisation des backups:**
```
/var/backups/teleroute/
├── database/          # Backups BDD
│   ├── db_20241118_020000.sql.gz
│   └── db_20241119_020000.sql.gz
├── files/            # Backups fichiers
│   ├── files_20241118_030000.tar.gz
│   └── files_20241119_030000.tar.gz
└── full/             # Backups complets
    ├── full_20241118_020000.tar.gz
    └── full_20241119_020000.tar.gz
```

---

### 4. crontab.example - Configuration des Tâches Planifiées

Template de configuration pour les cron jobs.

**Installation:**
```bash
sudo cp scripts/crontab.example /etc/cron.d/teleroute-marketplace
sudo chmod 644 /etc/cron.d/teleroute-marketplace
sudo systemctl restart cron
```

**Tâches automatiques:**

| Tâche | Fréquence | Description |
|-------|-----------|-------------|
| Backup complet | Tous les jours 2h | Backup BDD + fichiers |
| Backup BDD | Toutes les 6h | Backup base de données |
| Nettoyage logs | Tous les lundis 1h | Supprime logs > 30 jours |
| Offres expirées | Tous les jours 7h | Marque offres périmées |
| Alertes | Toutes les heures | Traite alertes utilisateurs |
| Statistiques | Tous les jours 6h | Met à jour les stats |
| Optimisation BDD | Tous les dimanches 4h | OPTIMIZE TABLE |

**Vérifier les cron jobs:**
```bash
# Voir les crons actifs
crontab -l

# Tester manuellement un cron
sudo -u www-data /var/www/teleroute-marketplace/scripts/backup.sh full

# Voir les logs
tail -f /var/www/teleroute-marketplace/logs/backup.log
tail -f /var/www/teleroute-marketplace/logs/cron.log
```

---

## Cron Scripts (dossier /cron)

### clean-expired-offers.php

Nettoie les offres expirées automatiquement.

**Usage:**
```bash
php cron/clean-expired-offers.php
```

**Actions:**
- Marque comme 'expired' les offres dont la date est dépassée
- Supprime définitivement les offres soft-deleted depuis > 90 jours

### process-alerts.php

Traite les alertes sauvegardées et envoie des notifications.

**Usage:**
```bash
php cron/process-alerts.php
```

**Actions:**
- Récupère toutes les alertes actives
- Cherche les nouvelles offres correspondantes
- Crée des notifications pour les matches
- Met à jour last_matched timestamp

---

## Workflows Recommandés

### Déploiement en Production

```bash
# 1. Tester en staging d'abord
./deploy.sh staging develop

# 2. Si OK, déployer en production
./deploy.sh production main

# 3. Vérifier les logs
tail -f /var/www/teleroute-marketplace/logs/app-$(date +%Y-%m-%d).log
```

### Backup Avant Modification Importante

```bash
# Créer un backup manuel
./backup.sh full

# Faire vos modifications
# ...

# En cas de problème, restaurer
tar -xzf /var/backups/teleroute/full/full_YYYYMMDD_HHMMSS.tar.gz
```

### Maintenance Programmée

```bash
# 1. Activer le mode maintenance
touch /var/www/teleroute-marketplace/maintenance.flag

# 2. Créer un backup
./backup.sh full

# 3. Faire la maintenance
mysql -u root -p < migration.sql
# ou
php maintenance-script.php

# 4. Tester
curl http://localhost/

# 5. Désactiver le mode maintenance
rm /var/www/teleroute-marketplace/maintenance.flag
```

---

## Monitoring et Alertes

### Espace Disque

```bash
# Vérifier l'espace disponible
df -h

# Taille des backups
du -sh /var/backups/teleroute/*

# Taille de la base de données
mysql -e "SELECT table_schema, ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.tables WHERE table_schema = 'teleroute_marketplace' GROUP BY table_schema"
```

### Logs

```bash
# Logs applicatifs
tail -f /var/www/teleroute-marketplace/logs/app-*.log

# Logs Apache
tail -f /var/log/apache2/teleroute-error.log

# Logs MySQL
tail -f /var/log/mysql/error.log

# Logs système
journalctl -u apache2 -f
```

### Santé de l'Application

```bash
# Test HTTP
curl -I http://localhost/

# Test avec temps de réponse
curl -w "@curl-format.txt" -o /dev/null -s http://localhost/

# Test base de données
mysql -u teleroute_user -p -e "SELECT COUNT(*) FROM users"
```

---

## Dépannage

### Le déploiement échoue

```bash
# Vérifier les logs de déploiement
cat /var/log/deploy.log

# Vérifier les permissions
ls -la /var/www/teleroute-marketplace
sudo chown -R www-data:www-data /var/www/teleroute-marketplace

# Vérifier Git
cd /var/www/teleroute-marketplace
git status
git log -1
```

### Les backups ne fonctionnent pas

```bash
# Vérifier que le cron tourne
sudo systemctl status cron

# Tester le backup manuellement
sudo -u www-data /var/www/teleroute-marketplace/scripts/backup.sh full

# Vérifier les logs de backup
tail -100 /var/www/teleroute-marketplace/logs/backup.log

# Vérifier l'espace disque
df -h /var/backups/
```

### Le site est lent

```bash
# Vider le cache
rm -rf /var/www/teleroute-marketplace/cache/*

# Optimiser la base de données
mysql teleroute_marketplace -e "OPTIMIZE TABLE users, freight_offers, vehicle_offers, messages"

# Redémarrer les services
sudo systemctl restart apache2
sudo systemctl restart mysql
```

---

## Sécurité

### Permissions Recommandées

```bash
# Dossiers
find /var/www/teleroute-marketplace -type d -exec chmod 755 {} \;

# Fichiers
find /var/www/teleroute-marketplace -type f -exec chmod 644 {} \;

# Scripts
chmod +x /var/www/teleroute-marketplace/scripts/*.sh
chmod +x /var/www/teleroute-marketplace/cron/*.php

# Dossiers écriture
chmod 775 /var/www/teleroute-marketplace/uploads
chmod 775 /var/www/teleroute-marketplace/logs
chmod 775 /var/www/teleroute-marketplace/cache

# Fichier sensible
chmod 600 /var/www/teleroute-marketplace/.env
```

### Backups Offsite

Pour une sécurité maximale, configurez les backups offsite:

1. **Amazon S3**
```bash
S3_BUCKET="teleroute-backups"
S3_ENABLED=true
```

2. **Rsync vers serveur distant**
```bash
rsync -avz /var/backups/teleroute/ user@remote-server:/backups/teleroute/
```

3. **FTP/SFTP**
```bash
lftp -e "mirror -R /var/backups/teleroute /backups; bye" sftp://user:pass@remote-server
```

---

## Support

Pour toute question ou problème:
- **Documentation**: `/var/www/teleroute-marketplace/DEVELOPER.md`
- **Logs**: `/var/www/teleroute-marketplace/logs/`
- **Email**: support@teleroute-marketplace.com

---

**Version**: 1.0.0
**Dernière mise à jour**: 18 Novembre 2024
