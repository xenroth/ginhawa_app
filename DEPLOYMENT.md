# Ginhawa Shared Hosting Deployment Guide

This guide targets iFastNet Premium/cPanel-style hosting. The account must provide PHP 8.2+, Laravel 12-compatible Composer dependencies, MySQL/MariaDB, Apache rewrite support, and either a configurable domain document root or a way to keep the Laravel project outside `public_html`.

## A. Build the release locally

```powershell
composer update --no-dev --optimize-autoloader
Copy-Item .env.example .env
php artisan key:generate
```

The first run uses `composer update` because this repository may not contain `composer.lock` yet. Commit the generated `composer.lock` afterward, then future deployments should use `composer install --no-dev --optimize-autoloader`. Do not upload your local `.env`. Create a production `.env` on the server. Upload `vendor/`; shared hosting may not have Composer or SSH.

## B. Create the database

In cPanel, create a MySQL database and database user, then grant the user all privileges. Shared hosts commonly prefix both names, for example `account_ginhawa` and `account_ginhawa_user`.

## C. Upload with the correct document root

Preferred layout:

```text
/home/ACCOUNT/ginhawa_app/       # complete Laravel project
/home/ACCOUNT/public_html/       # public directory or unrelated files
```

Set the domain document root to `/home/ACCOUNT/ginhawa_app/public`. Only `public/` should be web-accessible. The project includes `public/.htaccess` for Laravel routing and a root `.htaccess` as a fallback protection if the host forces the project root as the document root.

## D. Configure production environment

Create `.env` in the Laravel project root:

```env
APP_NAME=GINHAWA
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_KEY=base64:generated-key
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=account_ginhawa
DB_USERNAME=account_ginhawa_user
DB_PASSWORD=database-password
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
GINHAWA_VERSION=0.1.0
GINHAWA_GITHUB_REPOSITORY=xenroth/ginhawa_app
GINHAWA_GITHUB_TOKEN=
```

Set `GINHAWA_GITHUB_TOKEN` only for a private repository. Use a read-only token and never expose it in a public file.

## E. Permissions and first boot

Laravel must write to `storage/` and `bootstrap/cache/`. Use permissions `755` or `775`, depending on the hosting account’s PHP user.

Open the site once. The first request checks for the `roles` table and runs the seeded migration automatically. Initial administrator credentials:

```text
admin@ginhawa.local
change-me-now
```

Change the password immediately.

## F. GitHub release updater

The administrator dashboard has **CHECK GITHUB** and, when a newer published release exists, **INSTALL version** controls. It reads the latest release from:

```text
https://api.github.com/repos/xenroth/ginhawa_app/releases/latest
```

Only published releases are accepted. Create semantic tags such as `v0.2.0`. The updater preserves `.env`, `vendor/`, `storage/`, `bootstrap/cache/`, and `database/database.sqlite`, then writes the installed version to `VERSION`.

It does not run Composer or migrations automatically. After releases that change dependencies or schema, run:

```bash
composer dump-autoload --no-dev --optimize
php artisan migrate --force
php artisan optimize:clear
```

## Troubleshooting

- **404 on every route:** confirm Apache rewrite support and that the document root is `public/`.
- **500 error:** inspect `storage/logs/laravel.log`; keep `APP_DEBUG=false` in production.
- **Database error:** verify cPanel’s prefixed database and username exactly.
- **GitHub 404:** the repository is private, the repository name is wrong, or it has no published release.
- **Archive cannot open:** enable PHP’s `ZipArchive` extension or install releases manually.