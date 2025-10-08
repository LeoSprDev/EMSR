# Symfony EMSR Makefile
.PHONY: help install cache-clear db-create db-migrate db-fixtures test test-coverage lint fix-cs
.DEFAULT_GOAL = help

VARS_FILE := .env.local
ifneq ($(strip $(wildcard $(VARS_FILE))),)
	include $(VARS_FILE)
	export $(shell sed 's/=.*//' $(VARS_FILE))
endif

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## Installation complète du projet
	composer install
	@if [ ! -f .env.local ]; then cp .env.example .env.local; echo "Fichier .env.local créé, pensez à le configurer"; fi
	php bin/console doctrine:database:create --if-not-exists
	php bin/console doctrine:migrations:migrate --no-interaction
	php bin/console doctrine:fixtures:load --no-interaction
	@echo "Installation terminée. Configurez votre .env.local et votre base de données."

cache-clear: ## Vide le cache Symfony
	php bin/console cache:clear
	php bin/console cache:warmup

db-create: ## Crée la base de données
	php bin/console doctrine:database:create --if-not-exists

db-drop: ## Supprime la base de données
	php bin/console doctrine:database:drop --force --if-exists

db-migrate: ## Lance les migrations
	php bin/console doctrine:migrations:migrate --no-interaction

db-fixtures: ## Charge les fixtures
	php bin/console doctrine:fixtures:load --no-interaction

db-reset: db-drop db-create db-migrate db-fixtures ## Reset complet de la base de données

db-update: ## Met à jour le schéma de base de données
	php bin/console doctrine:schema:update --force

test: ## Lance tous les tests
	php bin/phpunit

test-coverage: ## Lance les tests avec couverture de code
	php bin/phpunit --coverage-html var/coverage

lint: ## Vérifie la syntaxe PHP
	php bin/console lint:twig templates/
	php bin/console lint:yaml config/

fix-cs: ## Correction automatique du style de code
	@echo "Ajoutez PHP-CS-Fixer si nécessaire"

user-create: ## Crée un nouvel utilisateur interactivement
	php bin/console app:create-user

dev-server: ## Lance le serveur de développement Symfony
	symfony serve -d

dev-stop: ## Arrête le serveur de développement
	symfony server:stop

assetsinstall: ## Installe les assets
	php bin/console assets:install public

composer-install: ## Met à jour les dépendances Composer
	composer install --no-dev --optimize-autoloader

deployment: composer-install cache-clear assets-install ## Déploiement en production
	@echo "Déploiement terminé"