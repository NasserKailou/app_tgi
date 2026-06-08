# Guide de déploiement — TGI Hors Classe de Niamey

**Application** : Système de gestion du Tribunal de Grande Instance Hors Classe de Niamey  
**URL de production** : `https://tgihc.ca-niamey.ne`  
**Stack** : PHP 8.1+ · MySQL 8.0 · Apache 2.4

---

## Sommaire

1. [Prérequis serveur](#1-prérequis-serveur)
2. [Déploiement des fichiers](#2-déploiement-des-fichiers)
3. [Configuration de l'URL de base — Point critique](#3-configuration-de-lurl-de-base--point-critique)
4. [Configuration de la base de données](#4-configuration-de-la-base-de-données)
5. [Configuration Apache / Réécriture d'URL](#5-configuration-apache--réécriture-durl)
6. [Droits sur les dossiers d'upload](#6-droits-sur-les-dossiers-dupload)
7. [Import du dump SQL](#7-import-du-dump-sql)
8. [Vérification post-déploiement](#8-vérification-post-déploiement)
9. [Diagnostic de la carte (/carte)](#9-diagnostic-de-la-carte-carte)
10. [Sécurité en production](#10-sécurité-en-production)

---

## 1. Prérequis serveur

### PHP
- Version minimale : **PHP 8.1**
- Extensions requises :
  ```
  php-pdo
  php-pdo_mysql
  php-mbstring
  php-json
  php-fileinfo
  php-gd (recommandé pour les images)
  php-zip
  ```
- Vérifier : `php -v` et `php -m | grep -E "pdo|mbstring|json|fileinfo"`

### MySQL / MariaDB
- Version minimale : **MySQL 8.0** ou **MariaDB 10.5**
- Encodage : `utf8mb4` avec collation `utf8mb4_unicode_ci`

### Apache
- Version : **Apache 2.4**
- Module `mod_rewrite` activé : `a2enmod rewrite`
- Module `mod_headers` activé : `a2enmod headers`

---

## 2. Déploiement des fichiers

```bash
# Cloner la branche main_ak
git clone -b main_ak https://github.com/NasserKailou/app_tgi.git /var/www/tgihc/

# Structure résultante :
# /var/www/tgihc/
#   app/
#   public/           ← DocumentRoot Apache pointe ici
#   migrations/
#   guide.md
#   ...
```

Le **DocumentRoot Apache doit pointer sur `/var/www/tgihc/public/`**, pas sur la racine du dépôt.

---

## 3. Configuration de l'URL de base — Point critique

### Problème

En local, l'application tourne sur `http://localhost:8085/app_tgi/public/`, ce qui donne :
```
BASE_URL = http://localhost:8085/app_tgi/public
```

En production, l'URL est `https://tgihc.ca-niamey.ne/public/`, ce qui donne :
```
BASE_URL = https://tgihc.ca-niamey.ne/public
```

**La logique de `app/config/config.php` détecte automatiquement `BASE_URL`** à partir de `$_SERVER['SCRIPT_NAME']`. Elle fonctionnera correctement en production **si le DocumentRoot pointe sur `/public/`** et qu'Apache est configuré correctement.

### Solution recommandée : `app_config.php`

Créez le fichier `/var/www/tgihc/app_config.php` (à la racine du projet, **jamais versionné**) :

```php
<?php
/**
 * Configuration locale — NE PAS VERSIONNER (ajouté dans .gitignore)
 * Surcharge l'URL de base détectée automatiquement par config.php
 */

// URL de base de l'application (sans slash final)
// Exemple production : https://tgihc.ca-niamey.ne/public
// Exemple local      : http://localhost:8085/app_tgi/public
define('APP_BASE_URL', 'https://tgihc.ca-niamey.ne/public');
```

> **Important** : si le site est à la racine du domaine (`https://tgihc.ca-niamey.ne/` sans `/public/`), 
> utilisez `define('APP_BASE_URL', 'https://tgihc.ca-niamey.ne');`

### Fichier `app_config.php.example` inclus

Un fichier `app_config.php.example` est fourni à la racine du dépôt. Copiez-le :
```bash
cp /var/www/tgihc/app_config.php.example /var/www/tgihc/app_config.php
# Éditez-le avec l'URL réelle
nano /var/www/tgihc/app_config.php
```

### Comment `app_config.php` est chargé

Dans `public/index.php`, en haut du fichier, on a :
```php
$appConfig = ROOT_PATH . '/app_config.php';
if (file_exists($appConfig)) {
    require_once $appConfig;
}
```
Ce fichier est donc chargé avant `config.php` — `APP_BASE_URL` sera défini et utilisé.

---

## 4. Configuration de la base de données

La connexion PDO utilise des **variables d'environnement** (voir `app/config/database.php`) :

| Variable    | Valeur                      |
|-------------|------------------------------|
| `DB_HOST`   | `localhost` (ou IP du serveur MySQL) |
| `DB_NAME`   | `app_tgi`                    |
| `DB_USER`   | `tgi_user` (à créer)         |
| `DB_PASS`   | mot de passe fort            |
| `DB_PORT`   | `3306` (par défaut)          |

### Option A : Variables d'environnement Apache (recommandé)

Dans le VirtualHost Apache ou dans `/etc/apache2/envvars` :
```apache
SetEnv DB_HOST localhost
SetEnv DB_NAME app_tgi
SetEnv DB_USER tgi_user
SetEnv DB_PASS VotreMotDePasseFort
SetEnv DB_PORT 3306
```

### Option B : Fichier `.env` (si supporté par le serveur)

Créez `/var/www/tgihc/.env` (jamais versionné) :
```
DB_HOST=localhost
DB_NAME=app_tgi
DB_USER=tgi_user
DB_PASS=VotreMotDePasseFort
DB_PORT=3306
```

### Création de la base et de l'utilisateur MySQL

```sql
CREATE DATABASE app_tgi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tgi_user'@'localhost' IDENTIFIED BY 'VotreMotDePasseFort';
GRANT ALL PRIVILEGES ON app_tgi.* TO 'tgi_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 5. Configuration Apache / Réécriture d'URL

### VirtualHost recommandé

```apache
<VirtualHost *:443>
    ServerName tgihc.ca-niamey.ne
    DocumentRoot /var/www/tgihc/public

    SSLEngine on
    SSLCertificateFile      /etc/letsencrypt/live/tgihc.ca-niamey.ne/fullchain.pem
    SSLCertificateKeyFile   /etc/letsencrypt/live/tgihc.ca-niamey.ne/privkey.pem

    <Directory /var/www/tgihc/public>
        AllowOverride All
        Options -Indexes +FollowSymLinks
        Require all granted
    </Directory>

    # Variables d'environnement DB
    SetEnv DB_HOST localhost
    SetEnv DB_NAME app_tgi
    SetEnv DB_USER tgi_user
    SetEnv DB_PASS VotreMotDePasseFort
    SetEnv DB_PORT 3306

    ErrorLog  /var/log/apache2/tgihc-error.log
    CustomLog /var/log/apache2/tgihc-access.log combined
</VirtualHost>

# Redirection HTTP → HTTPS
<VirtualHost *:80>
    ServerName tgihc.ca-niamey.ne
    Redirect permanent / https://tgihc.ca-niamey.ne/
</VirtualHost>
```

### `.htaccess` dans `public/`

Le fichier `public/.htaccess` est déjà présent dans le dépôt. Il contient :
```apache
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

> **Si le site est déployé dans un sous-répertoire** (ex: `/public/` dans un domaine partagé),
> changez `RewriteBase /` en `RewriteBase /public/`.

---

## 6. Droits sur les dossiers d'upload

```bash
# Créer les dossiers d'upload si inexistants
mkdir -p /var/www/tgihc/public/uploads/documents
mkdir -p /var/www/tgihc/public/uploads/photos

# Donner les droits à l'utilisateur Apache (www-data sur Debian/Ubuntu)
chown -R www-data:www-data /var/www/tgihc/public/uploads
chmod -R 755 /var/www/tgihc/public/uploads

# Assets statiques (GeoJSON carte, etc.)
chown -R www-data:www-data /var/www/tgihc/public/assets
chmod -R 755 /var/www/tgihc/public/assets
```

---

## 7. Import du dump SQL

```bash
# Import du dump de production
mysql -u tgi_user -p app_tgi < /var/www/tgihc/migrations/app_tgi_20260607.sql

# Vérification
mysql -u tgi_user -p app_tgi -e "SHOW TABLES;" | wc -l
```

> En cas d'erreur `SET NAMES utf8mb4` au début, assurez-vous que MySQL >= 8.0.

---

## 8. Vérification post-déploiement

```bash
# 1. Vérifier que PHP voit les variables d'env
php -r 'echo getenv("DB_HOST") . PHP_EOL;'

# 2. Test de connexion PHP/PDO
php -r '
$pdo = new PDO(
    "mysql:host=localhost;dbname=app_tgi;charset=utf8mb4",
    "tgi_user", "VotreMotDePasseFort"
);
echo "Connexion PDO OK\n";
'

# 3. Vérifier que mod_rewrite est actif
apache2ctl -M | grep rewrite

# 4. Tester la page d'accueil (doit renvoyer 200 ou 302)
curl -I https://tgihc.ca-niamey.ne/public/

# 5. Vérifier les logs Apache en temps réel
tail -f /var/log/apache2/tgihc-error.log
```

---

## 9. Diagnostic de la carte (`/carte`)

### Causes identifiées des problèmes d'affichage

#### A. Contenu mixte (Mixed Content) — Cause principale

La page est servie en **HTTPS** mais la carte chargeait des ressources en **HTTP**, bloquées par le navigateur.

**Correction appliquée** : tous les CDN Leaflet/Highcharts utilisent désormais `https://` explicitement dans `app/views/carte/index.php`.

Le fond CartoDB (`https://{s}.basemaps.cartocdn.com/…`) est nativement HTTPS — aucun problème.

#### B. BASE_URL mal configuré — Cause secondaire

Le GeoJSON (`/assets/data/niger_communes.geojson`) est chargé via :
```js
fetch(BASE + '/assets/data/niger_communes.geojson')
```
où `BASE = '<?= BASE_URL ?>'`.

Si `BASE_URL` contient `http://localhost:8085/app_tgi/public`, le fetch échouera en production.

**Correction** : créer `app_config.php` avec la bonne valeur de `APP_BASE_URL` (voir section 3).

#### C. API `/api/carte-data` inaccessible

Si la session expire ou si l'utilisateur n'est pas connecté, l'API retourne une redirection vers `/login` au lieu du JSON.

**Correction** : l'endpoint `GET /api/carte-data` est protégé par `Auth::requireLogin()` — l'utilisateur doit être connecté.

#### D. Vérification dans le navigateur

Ouvrir `https://tgihc.ca-niamey.ne/public/carte`, puis :
1. Ouvrir **DevTools → Console** : chercher des erreurs `Mixed Content`, `CORS`, `404`
2. Ouvrir **DevTools → Network** : filtrer sur `geojson` et `carte-data`
3. Si le GeoJSON est 404 : vérifier que `public/assets/data/niger_communes.geojson` existe

```bash
ls -la /var/www/tgihc/public/assets/data/niger_communes.geojson
```

---

## 10. Sécurité en production

```bash
# Protéger les fichiers sensibles
chmod 600 /var/www/tgihc/app_config.php
chmod 600 /var/www/tgihc/.env

# Empêcher l'accès direct au répertoire app/
# (déjà géré par le fait que DocumentRoot = public/, mais en doublon de sécurité)
echo "Deny from all" > /var/www/tgihc/app/.htaccess

# Désactiver l'affichage des erreurs PHP en production
# Dans php.ini ou .htaccess :
# php_flag display_errors Off
# php_value error_reporting 0
```

### Headers de sécurité (ajouter dans le VirtualHost)

```apache
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
# HSTS (à activer après test complet)
# Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

---

## Résumé des actions à effectuer en production

| Étape | Action | Fichier/Commande |
|-------|--------|-----------------|
| 1 | Déployer les fichiers | `git clone -b main_ak …` |
| 2 | **Créer `app_config.php`** | `cp app_config.php.example app_config.php` + éditer |
| 3 | Configurer les variables DB | VirtualHost ou `.env` |
| 4 | Créer la base MySQL | `CREATE DATABASE app_tgi …` |
| 5 | Importer le dump SQL | `mysql … < migrations/app_tgi_20260607.sql` |
| 6 | Droits upload | `chown -R www-data:www-data public/uploads` |
| 7 | Vérifier mod_rewrite | `a2enmod rewrite && systemctl restart apache2` |
| 8 | Tester l'application | `curl -I https://tgihc.ca-niamey.ne/public/` |

---

*Guide généré le 2026-06-08 — branche `main_ak` du dépôt `app_tgi`*
