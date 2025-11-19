#######################################################
# Makefile - Teleroute Marketplace
# Commandes utiles pour le développement et déploiement
#######################################################

.PHONY: help install start stop restart logs shell test clean backup restore deploy

# Variables
DOCKER_COMPOSE = docker-compose
PHP_CONTAINER = web
DB_CONTAINER = mysql
BACKUP_DIR = backups
TIMESTAMP = $(shell date +%Y%m%d_%H%M%S)

# Couleurs pour l'affichage
GREEN = \033[0;32m
BLUE = \033[0;34m
YELLOW = \033[1;33m
NC = \033[0m # No Color

##@ Aide

help: ## Afficher cette aide
	@echo "$(BLUE)═══════════════════════════════════════════════════════$(NC)"
	@echo "$(GREEN)  Teleroute Marketplace - Commandes Make$(NC)"
	@echo "$(BLUE)═══════════════════════════════════════════════════════$(NC)"
	@awk 'BEGIN {FS = ":.*##"; printf "\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  $(YELLOW)%-20s$(NC) %s\n", $$1, $$2 } /^##@/ { printf "\n$(BLUE)%s$(NC)\n", substr($$0, 5) } ' $(MAKEFILE_LIST)
	@echo ""

##@ Installation

install: ## Installation complète de l'application
	@echo "$(GREEN)🚀 Installation de Teleroute Marketplace...$(NC)"
	@if [ ! -f .env ]; then \
		echo "$(YELLOW)📝 Copie de .env.example vers .env$(NC)"; \
		cp .env.example .env; \
		echo "$(YELLOW)⚠️  N'oubliez pas de configurer .env avec vos paramètres !$(NC)"; \
	fi
	@echo "$(GREEN)🐳 Construction des images Docker...$(NC)"
	$(DOCKER_COMPOSE) build
	@echo "$(GREEN)📦 Démarrage des conteneurs...$(NC)"
	$(DOCKER_COMPOSE) up -d
	@echo "$(GREEN)⏳ Attente du démarrage de MySQL...$(NC)"
	@sleep 10
	@echo "$(GREEN)🗄️  Initialisation de la base de données...$(NC)"
	$(DOCKER_COMPOSE) exec -T $(DB_CONTAINER) mysql -uroot -proot_password -e "CREATE DATABASE IF NOT EXISTS teleroute_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
	$(DOCKER_COMPOSE) exec -T $(DB_CONTAINER) mysql -uroot -proot_password teleroute_marketplace < database/schema.sql
	@echo "$(GREEN)📁 Création des dossiers nécessaires...$(NC)"
	@mkdir -p uploads logs sessions cache backups
	@chmod -R 755 uploads logs sessions cache backups
	@echo "$(GREEN)✅ Installation terminée !$(NC)"
	@echo "$(BLUE)📍 L'application est accessible sur http://localhost$(NC)"

install-dev: install ## Installation en mode développement (avec données de test)
	@echo "$(GREEN)🎭 Génération de données de démonstration...$(NC)"
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php database/seed-demo-data.php --full
	@echo "$(GREEN)✅ Installation dev terminée avec données de test$(NC)"

##@ Docker

start: ## Démarrer les conteneurs Docker
	@echo "$(GREEN)🚀 Démarrage des conteneurs...$(NC)"
	$(DOCKER_COMPOSE) up -d
	@echo "$(GREEN)✅ Conteneurs démarrés$(NC)"
	@make status

stop: ## Arrêter les conteneurs Docker
	@echo "$(YELLOW)⏹️  Arrêt des conteneurs...$(NC)"
	$(DOCKER_COMPOSE) stop
	@echo "$(GREEN)✅ Conteneurs arrêtés$(NC)"

down: ## Arrêter et supprimer les conteneurs
	@echo "$(YELLOW)🗑️  Suppression des conteneurs...$(NC)"
	$(DOCKER_COMPOSE) down
	@echo "$(GREEN)✅ Conteneurs supprimés$(NC)"

restart: ## Redémarrer les conteneurs
	@echo "$(YELLOW)🔄 Redémarrage...$(NC)"
	@make stop
	@sleep 2
	@make start

rebuild: ## Reconstruire les images Docker
	@echo "$(GREEN)🔨 Reconstruction des images...$(NC)"
	$(DOCKER_COMPOSE) build --no-cache
	@make restart

status: ## Afficher le statut des conteneurs
	@echo "$(BLUE)📊 Statut des conteneurs:$(NC)"
	@$(DOCKER_COMPOSE) ps

##@ Logs

logs: ## Afficher tous les logs
	$(DOCKER_COMPOSE) logs -f

logs-web: ## Logs du serveur web
	$(DOCKER_COMPOSE) logs -f $(PHP_CONTAINER)

logs-db: ## Logs de la base de données
	$(DOCKER_COMPOSE) logs -f $(DB_CONTAINER)

logs-app: ## Logs de l'application
	@tail -f logs/app.log

logs-error: ## Logs des erreurs
	@tail -f logs/error.log

##@ Shell & Accès

shell: ## Accéder au shell du conteneur web
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) /bin/bash

shell-db: ## Accéder au shell MySQL
	$(DOCKER_COMPOSE) exec $(DB_CONTAINER) mysql -uteleroute_user -pteleroute_password teleroute_marketplace

shell-root: ## Accéder au shell MySQL en root
	$(DOCKER_COMPOSE) exec $(DB_CONTAINER) mysql -uroot -proot_password

##@ Base de Données

db-import: ## Importer le schéma de base de données
	@echo "$(GREEN)📥 Import du schéma...$(NC)"
	$(DOCKER_COMPOSE) exec -T $(DB_CONTAINER) mysql -uteleroute_user -pteleroute_password teleroute_marketplace < database/schema.sql
	@echo "$(GREEN)✅ Schéma importé$(NC)"

db-export: ## Exporter la base de données
	@echo "$(GREEN)📤 Export de la base de données...$(NC)"
	@mkdir -p $(BACKUP_DIR)
	$(DOCKER_COMPOSE) exec -T $(DB_CONTAINER) mysqldump -uteleroute_user -pteleroute_password teleroute_marketplace > $(BACKUP_DIR)/export-$(TIMESTAMP).sql
	@echo "$(GREEN)✅ Export créé: $(BACKUP_DIR)/export-$(TIMESTAMP).sql$(NC)"

db-reset: ## Réinitialiser la base de données (⚠️ DANGER)
	@echo "$(YELLOW)⚠️  Cette action va SUPPRIMER toutes les données !$(NC)"
	@read -p "Êtes-vous sûr ? (yes/no): " confirm; \
	if [ "$$confirm" = "yes" ]; then \
		echo "$(GREEN)🔄 Réinitialisation...$(NC)"; \
		$(DOCKER_COMPOSE) exec -T $(DB_CONTAINER) mysql -uroot -proot_password -e "DROP DATABASE IF EXISTS teleroute_marketplace;"; \
		$(DOCKER_COMPOSE) exec -T $(DB_CONTAINER) mysql -uroot -proot_password -e "CREATE DATABASE teleroute_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"; \
		make db-import; \
		echo "$(GREEN)✅ Base de données réinitialisée$(NC)"; \
	else \
		echo "$(BLUE)❌ Annulé$(NC)"; \
	fi

db-seed: ## Générer des données de test
	@echo "$(GREEN)🌱 Génération de données de test...$(NC)"
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php database/seed-demo-data.php --full
	@echo "$(GREEN)✅ Données générées$(NC)"

##@ Backup & Restore

backup: ## Créer un backup complet
	@echo "$(GREEN)💾 Création du backup...$(NC)"
	@mkdir -p $(BACKUP_DIR)
	@./scripts/backup.sh full
	@echo "$(GREEN)✅ Backup créé dans $(BACKUP_DIR)/$(NC)"

backup-quick: ## Backup rapide (données seulement)
	@echo "$(GREEN)💾 Backup rapide...$(NC)"
	@mkdir -p $(BACKUP_DIR)
	@./scripts/backup.sh data
	@echo "$(GREEN)✅ Backup créé$(NC)"

restore: ## Restaurer depuis un backup (RESTORE_FILE=chemin/vers/backup.sql.gz)
	@if [ -z "$(RESTORE_FILE)" ]; then \
		echo "$(YELLOW)⚠️  Usage: make restore RESTORE_FILE=backups/backup-xxx.sql.gz$(NC)"; \
		exit 1; \
	fi
	@echo "$(YELLOW)⚠️  Restauration de $(RESTORE_FILE)...$(NC)"
	@./scripts/restore.sh $(RESTORE_FILE)
	@echo "$(GREEN)✅ Restauration terminée$(NC)"

##@ Tests

test: ## Lancer tous les tests
	@echo "$(GREEN)🧪 Lancement des tests...$(NC)"
	@if [ -f vendor/bin/phpunit ]; then \
		vendor/bin/phpunit; \
	else \
		phpunit; \
	fi

test-coverage: ## Tests avec couverture de code
	@echo "$(GREEN)📊 Tests avec couverture...$(NC)"
	@if [ -f vendor/bin/phpunit ]; then \
		vendor/bin/phpunit --coverage-html coverage/; \
	else \
		phpunit --coverage-html coverage/; \
	fi
	@echo "$(GREEN)✅ Rapport disponible dans coverage/index.html$(NC)"

test-unit: ## Tests unitaires uniquement
	@if [ -f vendor/bin/phpunit ]; then \
		vendor/bin/phpunit tests/; \
	else \
		phpunit tests/; \
	fi

test-db: ## Tests de base de données
	@if [ -f vendor/bin/phpunit ]; then \
		vendor/bin/phpunit tests/DatabaseTest.php; \
	else \
		phpunit tests/DatabaseTest.php; \
	fi

##@ Code Quality

lint: ## Vérifier la syntaxe PHP
	@echo "$(GREEN)🔍 Vérification de la syntaxe PHP...$(NC)"
	@find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \; | grep -v "No syntax errors"

fix: ## Corriger le formatage du code (PHP-CS-Fixer)
	@echo "$(GREEN)🔧 Correction du formatage...$(NC)"
	@if [ -f vendor/bin/php-cs-fixer ]; then \
		vendor/bin/php-cs-fixer fix; \
	else \
		echo "$(YELLOW)⚠️  PHP-CS-Fixer non installé$(NC)"; \
	fi

security: ## Scanner les vulnérabilités de sécurité
	@echo "$(GREEN)🔒 Scan de sécurité...$(NC)"
	@echo "Recherche de credentials en dur..."
	@! grep -r "password.*=.*['\"]" --include="*.php" --exclude-dir=vendor . || echo "$(YELLOW)⚠️  Credentials trouvés !$(NC)"
	@echo "Recherche d'injections SQL..."
	@! grep -r '\$_[A-Z]*\[.*\]' --include="*.php" --exclude-dir=vendor . | grep -v PDO || echo "$(YELLOW)⚠️  Possibles injections SQL !$(NC)"

##@ Maintenance

clean: ## Nettoyer les fichiers temporaires
	@echo "$(GREEN)🧹 Nettoyage...$(NC)"
	@rm -rf cache/*
	@rm -rf sessions/*
	@rm -f logs/*.log
	@echo "$(GREEN)✅ Nettoyage terminé$(NC)"

clean-all: clean ## Nettoyage complet (inclus vendor, node_modules)
	@echo "$(YELLOW)🗑️  Nettoyage complet...$(NC)"
	@rm -rf vendor/
	@rm -rf node_modules/
	@rm -rf coverage/
	@echo "$(GREEN)✅ Nettoyage complet terminé$(NC)"

permissions: ## Corriger les permissions des fichiers
	@echo "$(GREEN)🔐 Correction des permissions...$(NC)"
	@chmod -R 755 uploads logs sessions cache backups
	@chmod 600 .env
	@echo "$(GREEN)✅ Permissions corrigées$(NC)"

optimize: ## Optimiser l'application
	@echo "$(GREEN)⚡ Optimisation...$(NC)"
	@make clean
	@echo "Optimisation du cache..."
	@# Ajouter ici les commandes d'optimisation spécifiques
	@echo "$(GREEN)✅ Optimisation terminée$(NC)"

##@ Déploiement

deploy-staging: ## Déployer en staging
	@echo "$(GREEN)🚀 Déploiement en staging...$(NC)"
	@./scripts/deploy.sh staging
	@echo "$(GREEN)✅ Déployé en staging$(NC)"

deploy-production: backup ## Déployer en production (avec backup)
	@echo "$(YELLOW)⚠️  Déploiement en PRODUCTION$(NC)"
	@read -p "Confirmer le déploiement en production ? (yes/no): " confirm; \
	if [ "$$confirm" = "yes" ]; then \
		echo "$(GREEN)🚀 Déploiement en production...$(NC)"; \
		./scripts/deploy.sh production; \
		echo "$(GREEN)✅ Déployé en production$(NC)"; \
	else \
		echo "$(BLUE)❌ Annulé$(NC)"; \
	fi

##@ Health Check

health: ## Vérifier la santé de l'application
	@echo "$(GREEN)🏥 Vérification de santé...$(NC)"
	@curl -s http://localhost/health-check.php | jq . || curl -s http://localhost/health-check.php
	@echo ""

check: ## Vérifier la configuration
	@echo "$(BLUE)═══════════════════════════════════════════════════════$(NC)"
	@echo "$(GREEN)  Vérification de la Configuration$(NC)"
	@echo "$(BLUE)═══════════════════════════════════════════════════════$(NC)"
	@echo ""
	@echo "$(YELLOW)📋 Fichiers de configuration:$(NC)"
	@test -f .env && echo "  ✅ .env existe" || echo "  ❌ .env manquant"
	@test -f config/config.php && echo "  ✅ config/config.php existe" || echo "  ❌ config/config.php manquant"
	@echo ""
	@echo "$(YELLOW)📁 Dossiers:$(NC)"
	@test -d uploads && echo "  ✅ uploads/" || echo "  ❌ uploads/ manquant"
	@test -d logs && echo "  ✅ logs/" || echo "  ❌ logs/ manquant"
	@test -d sessions && echo "  ✅ sessions/" || echo "  ❌ sessions/ manquant"
	@test -d cache && echo "  ✅ cache/" || echo "  ❌ cache/ manquant"
	@test -d backups && echo "  ✅ backups/" || echo "  ❌ backups/ manquant"
	@echo ""
	@echo "$(YELLOW)🐳 Docker:$(NC)"
	@docker --version 2>/dev/null && echo "  ✅ Docker installé" || echo "  ❌ Docker non installé"
	@docker-compose --version 2>/dev/null && echo "  ✅ Docker Compose installé" || echo "  ❌ Docker Compose non installé"
	@echo ""

##@ Développement

dev: ## Mode développement avec hot reload
	@echo "$(GREEN)👨‍💻 Mode développement$(NC)"
	@echo "APP_ENV=development" > .env.local
	@echo "APP_DEBUG=true" >> .env.local
	@make restart
	@make logs

watch: ## Surveiller les logs en temps réel
	@echo "$(GREEN)👀 Surveillance des logs...$(NC)"
	@tail -f logs/app.log logs/error.log

composer-install: ## Installer les dépendances PHP
	@echo "$(GREEN)📦 Installation des dépendances PHP...$(NC)"
	@composer install

npm-install: ## Installer les dépendances JS
	@echo "$(GREEN)📦 Installation des dépendances JS...$(NC)"
	@npm install

assets: ## Compiler les assets frontend
	@echo "$(GREEN)🎨 Compilation des assets...$(NC)"
	@npm run build

##@ Documentation

docs: ## Générer la documentation
	@echo "$(GREEN)📚 Génération de la documentation...$(NC)"
	@echo "Documentation disponible dans:"
	@echo "  - README.md"
	@echo "  - QUICKSTART.md"
	@echo "  - DEVELOPER.md"
	@echo "  - API.md"

api-docs: ## Ouvrir la documentation API
	@echo "$(GREEN)📡 Documentation API...$(NC)"
	@xdg-open http://localhost/api-docs.html || open http://localhost/api-docs.html || echo "Ouvrez http://localhost/api-docs.html dans votre navigateur"

##@ Informations

version: ## Afficher les versions
	@echo "$(BLUE)═══════════════════════════════════════════════════════$(NC)"
	@echo "$(GREEN)  Versions$(NC)"
	@echo "$(BLUE)═══════════════════════════════════════════════════════$(NC)"
	@echo ""
	@echo "$(YELLOW)Teleroute Marketplace:$(NC) 1.0.0"
	@echo "$(YELLOW)PHP:$(NC) $$(php -v | head -n 1)"
	@echo "$(YELLOW)MySQL:$(NC) $$(mysql --version)"
	@echo "$(YELLOW)Docker:$(NC) $$(docker --version)"
	@echo ""

info: version check ## Afficher toutes les informations

stats: ## Statistiques du projet
	@echo "$(BLUE)═══════════════════════════════════════════════════════$(NC)"
	@echo "$(GREEN)  Statistiques du Projet$(NC)"
	@echo "$(BLUE)═══════════════════════════════════════════════════════$(NC)"
	@echo ""
	@echo "$(YELLOW)📊 Code:$(NC)"
	@echo "  Fichiers PHP: $$(find . -name '*.php' -not -path './vendor/*' | wc -l)"
	@echo "  Lignes de code PHP: $$(find . -name '*.php' -not -path './vendor/*' -exec cat {} \; | wc -l)"
	@echo "  Fichiers CSS: $$(find . -name '*.css' -not -path './vendor/*' -not -path './node_modules/*' | wc -l)"
	@echo "  Fichiers JS: $$(find . -name '*.js' -not -path './vendor/*' -not -path './node_modules/*' | wc -l)"
	@echo ""
	@echo "$(YELLOW)📁 Taille:$(NC)"
	@du -sh . 2>/dev/null | awk '{print "  Projet total: " $$1}'
	@du -sh uploads 2>/dev/null | awk '{print "  Uploads: " $$1}' || echo "  Uploads: 0"
	@du -sh logs 2>/dev/null | awk '{print "  Logs: " $$1}' || echo "  Logs: 0"
	@du -sh backups 2>/dev/null | awk '{print "  Backups: " $$1}' || echo "  Backups: 0"
	@echo ""

.DEFAULT_GOAL := help
