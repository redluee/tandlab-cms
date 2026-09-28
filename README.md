# Tandlab CMS

Custom PHP CMS voor de Tandlab-website (Concept 1). Geen framework, geen Composer-packages, geen front-end build — vanilla PHP + PDO + GD.

## Vereisten

- PHP 8.4+ (8.5 werkt ook; gebruik in productie een door PHP actief ondersteunde versie — PHP kent geen LTS) met extensies: `pdo_sqlite` (dev) of `pdo_mysql` (productie), `gd` (met WebP-ondersteuning), `phar` (voor back-ups).
- Composer is **alleen voor development** nodig (PHPUnit, phpcs, PHPStan, pre-commit hook). Op de productieserver is geen Composer nodig: deploy zonder `vendor/`.

## Snel starten (development)

```bash
php scripts/install.php --user=admin --password=KiesEenSterkWachtwoord
php -S localhost:8000 -t public public/router.php
```

Bezoek `http://localhost:8000/` voor de website en `http://localhost:8000/admin/login` voor het CMS.

Het admin-wachtwoord moet minimaal 12 tekens zijn (`install.php` weigert zwakke wachtwoorden en het voorbeeldwachtwoord).

`scripts/install.php` is idempotent: het maakt het schema aan (indien nodig), seedt de content uit `content/**/content.md` alleen als de tabellen leeg zijn, en maakt de opgegeven admin-gebruiker aan als die nog niet bestaat. Herhaald uitvoeren overschrijft geen bewerkte content.

## Configuratie

Standaard gebruikt de applicatie SQLite (`storage/database.sqlite`). Voor productie met MySQL, maak `config/config.ini` aan (genegeerd door git) of zet omgevingsvariabelen:

```ini
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=tandlab
DB_USER=tandlab
DB_PASS=geheim
DB_CHARSET=utf8mb4
```

Zet `APP_DEBUG=0` (standaard) in productie: fouten worden dan niet getoond maar gelogd naar `storage/logs/php-error.log`, en bezoekers krijgen een nette 500-pagina. `APP_DEBUG=1` alleen lokaal.

Omgevingsvariabelen hebben voorrang op `config.ini`. Draai daarna `php scripts/install.php --user=... --password=...` opnieuw tegen de MySQL-database om het schema aan te maken.

## Deploy-checklist

1. Upload de code (zonder `vendor/`, `storage/database.sqlite` en `storage/uploads/*`).
2. Draai **op de server** `php scripts/install.php --user=... --password=...`. Dit is verplicht: de branding-WebP's (`storage/uploads/logo.webp`, `bghero.webp`) en de geseede uploads staan in `.gitignore` en worden hier gegenereerd. Zonder deze stap ontbreken logo en afbeeldingen.
3. `public/uploads` is een symlink naar `../storage/uploads` (staat in git). De webserver moet symlinks volgen (Apache: `Options +FollowSymLinks`, staat standaard aan; nginx: volgt symlinks tenzij `disable_symlinks` aan staat). Controleer na deploy dat `/uploads/logo.webp` laadt; kan de host geen symlinks, kopieer/mount dan `storage/uploads` naar `public/uploads`.
4. Zorg dat `storage/` schrijfbaar is voor de PHP-gebruiker en buiten de documentroot staat.
5. Achter een reverse proxy: `REMOTE_ADDR` moet het client-IP zijn (login-throttle werkt per IP); configureer de proxy/PHP-FPM hierop.

## Back-ups

`php scripts/backup.php` maakt in `storage/backups/` een databasedump (SQLite via `VACUUM INTO`, MySQL via `mysqldump`) en een `tar.gz` van `storage/uploads` en `storage/privacy`; de laatste 14 sets blijven bewaard (`BACKUP_DIR=/pad` voor een andere map). Plan dit dagelijks via cron en kopieer de bestanden naar een andere locatie:

```cron
15 3 * * * cd /var/www/tandlab-cms && php scripts/backup.php >> storage/logs/backup.log 2>&1
```

Herstellen: zet de database-dump terug (SQLite: kopieer naar `storage/database.sqlite`; MySQL: `mysql tandlab < dump.sql`) en pak de tar uit in `storage/`.

## Productie (Apache)

Documentroot moet naar `public/` wijzen. `public/.htaccess` regelt de front-controller-routing; de root-`.htaccess` blokkeert directe toegang als de documentroot per ongeluk breder staat ingesteld.

## Productie (nginx) — voorbeeld

```nginx
server {
    listen 80;
    server_name tandlab.nl;
    root /var/www/tandlab-cms/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "frame-ancestors 'self'" always;

    location ~* \.(?:css|js|webp|jpg|jpeg|png|svg)$ {
        expires 30d;
        access_log off;
    }
}
```

`storage/` en `config/` staan buiten `public/` en zijn dus niet rechtstreeks bereikbaar via de webserver.

## Mappenstructuur

Zie `plan.md` §3 voor de volledige projectstructuur en architectuurbeslissingen.

## Testen

- `composer install` (installeert dev-dependencies + git pre-commit hook).
- `composer check` — lint (phpcs), static analysis (phpstan) en de PHPUnit-testsuite (`tests/`); draait automatisch bij elke `git commit`.
- `php -S localhost:8000 -t public public/router.php` voor lokaal draaien.
- `php scripts/install.php` herhaaldelijk uitvoeren om idempotentie te controleren.
- Handmatige checklist:
  - Hero-slider op mobiel/desktop, fixed navigatie, login-cyclus.
  - Publieke pagina's (`/`, `/tand`, `/team`) bevatten geen `data-edit`-attributen en laden geen editor-assets.
  - Visuele editor (`/admin/bewerken/home|tand|team`): tekstvelden aanklikken en bewerken (bold/italic, links bij `richtext`), afbeelding vervangen/uploaden, alt-tekst (Tand), hero-slides toevoegen/verwijderen/herordenen, nieuw Tand-werkstuk/teamlid toevoegen, item verbergen/tonen, herordenen (sleep-handvat), verwijderen, en "Opslaan" — controleer dat de publieke pagina de wijziging toont.
  - "Annuleren" en het verlaten van de pagina met niet-opgeslagen wijzigingen tonen een waarschuwing.
  - Login: na 5 foute pogingen binnen 15 minuten wordt inloggen (per IP) geblokkeerd; uitloggen werkt alleen via de knop (POST + CSRF).
  - Mediabibliotheek: een afbeelding die nog op een pagina staat of `logo.webp`/`bghero.webp` kan niet worden verwijderd.
  - Instellingen: een `map_embed_url` buiten `https://www.google.com/maps/embed` (of `.../maps?...&output=embed`) wordt geweigerd.
  - Uploaden van afbeeldingen via de mediabibliotheek en via de editor.
  - Beveiliging: een `POST /admin/bewerken/opslaan` zonder `X-CSRF-Token`-header geeft 403; uitgelogd bezoeken van `/admin/bewerken/*` stuurt door naar `/admin/login`; een onbekende instelling of afbeeldingsbestandsnaam wordt geweigerd; geplakte of geposte `<script>`/`onerror`/`javascript:`-payloads komen gestript terug (zie `rich()`-uitzondering in AGENTS.md).
