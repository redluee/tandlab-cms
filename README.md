# Tandlab CMS

Custom PHP CMS voor de Tandlab-website (Concept 1). Geen framework, geen Composer-packages, geen front-end build — vanilla PHP 8.4 + PDO + GD.

## Vereisten

- PHP 8.4+ met extensies: `pdo_sqlite` (dev) of `pdo_mysql` (productie), `gd`.
- Geen Composer nodig.

## Snel starten (development)

```bash
php scripts/install.php --user=admin --password=KiesEenSterkWachtwoord
php -S localhost:8000 -t public public/router.php
```

Bezoek `http://localhost:8000/` voor de website en `http://localhost:8000/admin/login` voor het CMS.

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

Omgevingsvariabelen hebben voorrang op `config.ini`. Draai daarna `php scripts/install.php --user=... --password=...` opnieuw tegen de MySQL-database om het schema aan te maken.

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
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

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

- `php -S localhost:8000 -t public public/router.php` voor lokaal draaien.
- `php scripts/install.php` herhaaldelijk uitvoeren om idempotentie te controleren.
- Handmatige checklist:
  - Hero-slider op mobiel/desktop, fixed navigatie, login-cyclus.
  - Publieke pagina's (`/`, `/tand`, `/team`) bevatten geen `data-edit`-attributen en laden geen editor-assets.
  - Visuele editor (`/admin/bewerken/home|tand|team`): tekstvelden aanklikken en bewerken (bold/italic, links bij `richtext`), afbeelding vervangen/uploaden, alt-tekst (Tand), hero-slides toevoegen/verwijderen/herordenen, nieuw Tand-werkstuk/teamlid toevoegen, item verbergen/tonen, herordenen (sleep-handvat), verwijderen, en "Opslaan" — controleer dat de publieke pagina de wijziging toont.
  - "Annuleren" en het verlaten van de pagina met niet-opgeslagen wijzigingen tonen een waarschuwing.
  - Uploaden van afbeeldingen via de mediabibliotheek en via de editor.
  - Beveiliging: een `POST /admin/bewerken/opslaan` zonder `X-CSRF-Token`-header geeft 403; uitgelogd bezoeken van `/admin/bewerken/*` stuurt door naar `/admin/login`; een onbekende instelling of afbeeldingsbestandsnaam wordt geweigerd; geplakte of geposte `<script>`/`onerror`/`javascript:`-payloads komen gestript terug (zie `rich()`-uitzondering in AGENTS.md).
