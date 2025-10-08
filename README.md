# EMSR - Gestion des Mouvements RH

Application Symfony 7.3 pour la gestion des mouvements d'agents RH avec quatre types de mouvements :
- **Entrées** : Nouveaux agents
- **Sorties** : Départs d'agents 
- **Mobilités** : Changements de poste/service
- **Renouvellements** : Renouvellements de CDD

## Fonctionnalités

### Rôles et permissions
- **ADMIN** : Gestion complète (utilisateurs, configuration, tous les mouvements)
- **RH** : Création, édition et suppression des mouvements
- **INFO** : Lecture seule + accusé de prise en compte

### Interface utilisateur
- Vue mensuelle avec 4 tableaux synchronisés
- Filtrage par mois pour consultation historique
- Notification email automatique sur chaque modification
- Interface responsive et intuitive

### Notifications
- Email automatique au service informatique à chaque création/modification/suppression
- Tableaux complets du mois avec mise en évidence des changements
- Configuration SMTP paramétrable

## Prérequis

- PHP 8.1 ou supérieur
- PostgreSQL 13+
- Composer
- Serveur web (Apache/Nginx)

## Installation

### 1. Cloner le projet
```bash
git clone https://github.com/LeoSprDev/EMSR.git
cd EMSR
```

### 2. Installer les dépendances
```bash
composer install
```

### 3. Configuration de l'environnement
Copier le fichier `.env.example` vers `.env` et configurer :

```bash
cp .env.example .env
```

**Variables importantes à configurer :**

```env
# Base de données PostgreSQL
DATABASE_URL="postgresql://username:password@127.0.0.1:5432/emsr_db?serverVersion=15&charset=utf8"

# Configuration SMTP pour les emails
MAILER_DSN=smtp://user:password@smtp.example.com:587
MAILER_FROM_EMAIL=noreply@example.com
MAILER_FROM_NAME="EMSR - Mouvements RH"

# Email du service informatique (destinataire des notifications)
INFO_EMAIL=informatique@example.com

# Clé secrète Symfony
APP_SECRET=your-secret-key-here

# Environnement
APP_ENV=prod
APP_DEBUG=0
```

### 4. Créer la base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Charger les données initiales
```bash
php bin/console doctrine:fixtures:load
```

Ceci créera :
- Un compte administrateur : `admin` / `admin123`
- Un compte service informatique : `info` / `info123`

### 6. Configuration du serveur web

**Apache (.htaccess)**
```apache
RewriteEngine On
RewriteCond %{REQUEST_URI}::$0 ^(/.+)/(.*)::\2$
RewriteRule .* - [E=BASE:%1]
RewriteCond %{HTTP:Authorization} .
RewriteRule ^ - [E=HTTP_AUTHORIZATION:%0]
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^ - [L]
RewriteRule ^ %{ENV:BASE}/index.php [L]
```

**Nginx**
```nginx
location / {
    try_files $uri /index.php$is_args$args;
}

location ~ ^/index\.php(/|$) {
    fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    fastcgi_split_path_info ^(.+\.php)(/.*)$;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    fastcgi_param DOCUMENT_ROOT $realpath_root;
}
```

## Utilisation

### Connexion
Accédez à l'application via votre navigateur. Utilisez les comptes créés lors de l'installation ou créez de nouveaux utilisateurs via le panneau d'administration.

### Gestion des mouvements
1. **RH** : Connectez-vous avec un compte RH pour ajouter/modifier/supprimer des mouvements
2. **Service INFO** : Connectez-vous avec le compte INFO pour marquer les mouvements comme "pris en compte"
3. **Administrateur** : Accès complet + gestion des utilisateurs

### Notifications email
Chaque action de création/modification/suppression génère automatiquement un email au service informatique avec :
- Les 4 tableaux du mois concerné
- Mise en évidence en vert des lignes modifiées
- Lien direct vers la vue mensuelle

## Scripts utiles

```bash
# Vider le cache
make cache-clear

# Mise à jour de la base de données
make db-update

# Lancer les tests
make test

# Créer un nouvel utilisateur
php bin/console app:create-user

# Réinitialiser les permissions
php bin/console app:setup-permissions
```

## Tests

Lancer la suite de tests :
```bash
php bin/phpunit
```

Tests couverts :
- Authentification et autorisations
- CRUD des mouvements
- Envoi d'emails de notification
- Filtres mensuels
- Accusés de prise en compte

## Structure du projet

```
src/
├── Controller/          # Contrôleurs Symfony
├── Entity/             # Entités Doctrine
├── Form/               # Formulaires Symfony
├── Repository/         # Repositories Doctrine
├── Service/            # Services métier
└── Security/           # Configuration sécurité

templates/
├── base.html.twig      # Template de base
├── mouvement/          # Templates des mouvements
├── security/           # Templates d'authentification
└── email/              # Templates d'emails

migrations/             # Migrations Doctrine
tests/                  # Tests PHPUnit
config/                 # Configuration Symfony
```

## Support et maintenance

Pour tout problème ou question :
1. Vérifier les logs dans `var/log/`
2. Consulter la documentation Symfony 7.3
3. Vérifier la configuration SMTP pour les emails

## Licence

Propriétaire - Usage interne uniquement