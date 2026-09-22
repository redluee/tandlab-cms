# AGENTS.md — Tandlab CMS

## Projectoverzicht

Custom PHP CMS voor Tandlab (tandtechnisch laboratorium, kroon- en brugwerk, De Meern). Eén concept wordt gebouwd: "Concept 1". Publieke site (Home, Tand, Team, gedeelde `#contact`-sectie) + Nederlandstalig admin-CMS.

## Huisstijl

- Kleuren: groen accent `#78c39c` (donker `#4f9d78`), donkergrijs `#23272a`/`#4a4f54` voor tekst en contact/footer-achtergrond, lichtgrijs `#f4f5f6` voor gemute secties.
- Fixed navigatiebalk bovenaan, gecentreerde content, actief navigatie-item highlighted met de groene kleur.
- Vanilla CSS met custom properties (`:root`), geen build-step, geen CSS-framework.

## Git

- Negeer de git repository standaard.
- Maak onder geen enkele voorwaarde een worktree, branch, tag, commit, merge, rebase, stash, push, pull, status of andere git-operatie zonder expliciete toestemming van de gebruiker.
- Als de gebruiker expliciet om een git-actie vraagt, voer dan alleen die gevraagde actie uit en niets meer.

## Stack

- PHP 8.4, PDO (`pdo_sqlite` dev, `pdo_mysql` productie) achter één driver-toggle in `config/config.php`.
- GD voor image resize + WebP-conversie (`app/Services/ImageService.php`).
- Geen Composer, geen front-end build. Eigen front controller + Router (`app/Http/Router.php`).
- Sessie-auth met `password_hash()`/`password_verify()` (bcrypt), CSRF-tokens per formulier, honeypot-veld op het contactformulier.

## Commands

```bash
php scripts/install.php --user=admin --password=...   # schema + seed + admin-user (idempotent)
php -S localhost:8000 -t public public/router.php      # lokale dev-server
```

## Mappenstructuur

Zie `plan.md` §3. Namespaces volgen PSR-achtige mapping: `App\Db` → `app/Db`, `App\Services` → `app/Services`, `App\Models` → `app/Models`, `App\Controllers` → `app/Controllers`, `App\Http` → `app/Http`. **Mapnamen moeten exact de PascalCase-namespace volgen** (case-sensitive autoloader in `app/bootstrap.php`). `app/views/**` wordt niet ge-autoload, alleen via `require`.

`public/uploads` is een symlink naar `storage/uploads` zodat geüploade/geseede afbeeldingen direct door de webserver (of PHP's built-in server) geserveerd kunnen worden zonder een aparte streaming-route.

## Conventies

- Geen comments tenzij de WHY niet-triviaal is.
- Admin-UI en publieke UI-teksten in het Nederlands.
- Alle SQL via PDO prepared statements; output altijd via de `e()`-helper (`htmlspecialchars`).
- Portable SQL: `app/Db/schema.sqlite.sql` en `app/Db/schema.mysql.sql` zijn functioneel identiek; `AUTOINCREMENT`/`AUTO_INCREMENT` is het enige structurele verschil.
- `site_settings` is een key/value-tabel (`setting_key`, niet `key` — gereserveerd woord in sommige SQL-dialecten).
- Nieuwe afbeeldingen altijd via `ImageService` (validatie → resize → WebP), nooit ruwe uploads direct opslaan.
- `scripts/install.php` mag nooit bestaande, handmatig bewerkte content overschrijven — alleen ontbrekende rijen/instellingen aanvullen.

## Beveiliging

- CSRF-token verplicht op alle POST-formulieren (publiek én admin); validatie via `App\Services\Csrf`.
- Contactformulier: honeypot-veld (`website`) + sessie-rate-limit (30s) naast CSRF.
- Sessie-cookies: `httponly`, `samesite=Lax`, `secure` wanneer HTTPS; sessie-rotatie bij login (`session_regenerate_id`).
- Upload-whitelist (jpeg/png/webp/gif) + GD-recodering naar WebP — geen scriptable bestanden worden opgeslagen.
- `storage/` en `config/` horen buiten de documentroot te staan in productie; root-`.htaccess` blokkeert ze als extra laag.

## Testen

Geen geautomatiseerde testsuite (bewust, gezien de scope). Testaanpak: handmatige smoke-checklist in `README.md` §Testen, plus herhaald draaien van `scripts/install.php` om idempotentie te bevestigen.
