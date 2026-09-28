# AGENTS.md — Tandlab CMS

## Projectoverzicht

Custom PHP CMS voor Tandlab (tandtechnisch laboratorium, kroon- en brugwerk, De Meern). Eén concept wordt gebouwd: "Concept 1". Publieke site (Home, Tand, Team, gedeelde `#contact`-sectie met statische contactgegevens, geen formulier) + Nederlandstalig admin-CMS.

## Visuele editor

Content van Home, Tand en Team wordt beheerd via een inline visuele editor (`/admin/bewerken/{home,tand,team}`), niet via losse admin-formulieren. `App\Services\EditMode` zet een globale vlag; de publieke views (`app/views/public/**`) worden hergebruikt voor zowel de publieke site als de editor. De `edit()`-helper (`app/bootstrap.php`) voegt `data-edit`/`data-edit-type`-attributen toe zolang `EditMode::on()` waar is, en geeft anders een lege string terug — publieke pagina's blijven zo byte-voor-byte ongewijzigd (geen `data-edit`, geen editor-CSS/JS) wanneer er niet bewerkt wordt. Client-side zit de logica in `public/assets/js/editor.js` (vanilla JS, `contenteditable` + `document.execCommand`, SortableJS voor herordenen) en `public/assets/js/media-picker.js` (gedeelde mediabibliotheek-picker). Wijzigingen worden pas opgeslagen bij een expliciete "Opslaan"-actie, via `POST /admin/bewerken/opslaan` (JSON body, `X-CSRF-Token`-header), afgehandeld door `App\Controllers\EditorController`.

## Huisstijl

- Kleuren: groen accent `#78c39c` (donker `#4f9d78`), donkergrijs `#23272a`/`#4a4f54` voor tekst en contact/footer-achtergrond, lichtgrijs `#f4f5f6` voor gemute secties.
- Fixed navigatiebalk bovenaan, gecentreerde content, actief navigatie-item highlighted met de groene kleur.
- Vanilla CSS met custom properties (`:root`), geen build-step, geen CSS-framework.

## Stack

- PHP 8.4+ (8.5 getest), PDO (`pdo_sqlite` dev, `pdo_mysql` productie) achter één driver-toggle in `config/config.php`.
- GD voor image resize + WebP-conversie (`app/Services/ImageService.php`).
- Geen front-end build. Eigen front controller + Router (`app/Http/Router.php`).
- Composer wordt uitsluitend als dev-tooling gebruikt (PHPUnit, PHP_CodeSniffer, PHPStan) — geen runtime-dependencies, geen build-step voor productie.
- Sessie-auth met `password_hash()`/`password_verify()` (bcrypt), CSRF-tokens per formulier.

## Commands

```bash
php scripts/install.php --user=admin --password=...   # schema + seed + admin-user (idempotent, wachtwoord min. 12 tekens)
php scripts/backup.php                                 # DB-dump + tar.gz van uploads/privacy naar storage/backups
php -S localhost:8000 -t public public/router.php      # lokale dev-server
composer install                                       # dev-dependencies + pre-commit hook installeren
composer check                                          # lint (phpcs) + static analysis (phpstan) + tests (phpunit)
composer lint                                           # alleen phpcs
composer stan                                           # alleen phpstan
composer test                                           # alleen phpunit
```

## Mappenstructuur

Zie `plan.md` §3. Namespaces volgen PSR-achtige mapping: `App\Db` → `app/Db`, `App\Services` → `app/Services`, `App\Models` → `app/Models`, `App\Controllers` → `app/Controllers`, `App\Http` → `app/Http`. **Mapnamen moeten exact de PascalCase-namespace volgen** (case-sensitive autoloader in `app/bootstrap.php`). `app/views/**` wordt niet ge-autoload, alleen via `require`.

`public/uploads` is een symlink naar `storage/uploads` zodat geüploade/geseede afbeeldingen direct door de webserver (of PHP's built-in server) geserveerd kunnen worden zonder een aparte streaming-route.

## Conventies

- Geen comments tenzij de WHY niet-triviaal is.
- Admin-UI en publieke UI-teksten in het Nederlands.
- Alle SQL via PDO prepared statements; output altijd via de `e()`-helper (`htmlspecialchars`).
- Uitzondering: content-velden die rich text mogen bevatten (bijv. `hero_intro`, `address`, Tand-beschrijvingen, team-bio's) worden uitgevoerd via de `rich()`-helper (`app/bootstrap.php`) in plaats van `e()`/`nl2br(e())`. `rich()` geeft platte waarden (geen `<`) terug via `nl2br(e(...))`, en HTML-waarden via `App\Services\HtmlSanitizer::clean()`, dat een whitelist van tags/attributen afdwingt (o.a. geen `<script>`, geen `javascript:`-links). Content wordt zowel bij opslaan als bij tonen gesaniteerd. Gebruik `rich()` nooit voor attributen, URL's of `alt`-teksten — die blijven via `e()` gaan.
- Portable SQL: `app/Db/schema.sqlite.sql` en `app/Db/schema.mysql.sql` zijn functioneel identiek; `AUTOINCREMENT`/`AUTO_INCREMENT` is het enige structurele verschil.
- `site_settings` is een key/value-tabel (`setting_key`, niet `key` — gereserveerd woord in sommige SQL-dialecten).
- Nieuwe afbeeldingen altijd via `ImageService` (validatie → resize → WebP), nooit ruwe uploads direct opslaan.
- `scripts/install.php` mag nooit bestaande, handmatig bewerkte content overschrijven — alleen ontbrekende rijen/instellingen aanvullen.

## Beveiliging

- CSRF-token verplicht op alle POST-formulieren (publiek én admin); validatie via `App\Services\Csrf`.
- Login: throttle per IP (5 mislukte pogingen / 15 min, tabel `login_attempts`); logout is POST + CSRF.
- Foutafhandeling: `App\Services\ErrorHandler` (geregistreerd in `bootstrap.php`) zet `display_errors` uit tenzij `APP_DEBUG=1`, logt naar `storage/logs/` en toont `app/views/public/500.php`.
- Security headers: `App\Services\SecurityHeaders` + `public/.htaccess`.
- `map_embed_url` wordt gevalideerd met `Validator::googleMapsEmbedUrl()` (alleen `https://www.google.com/maps/embed` of `.../maps?...&output=embed`), bij opslaan én bij tonen.
- Mediabibliotheek: afbeeldingen die op een pagina in gebruik zijn en `logo.webp`/`bghero.webp` (`Image::isProtected()`) kunnen niet worden verwijderd.
- Sessie-cookies: `httponly`, `samesite=Lax`, `secure` wanneer HTTPS; sessie-rotatie bij login (`session_regenerate_id`).
- Upload-whitelist (jpeg/png/webp/gif) + GD-recodering naar WebP — geen scriptable bestanden worden opgeslagen.
- `storage/` en `config/` horen buiten de documentroot te staan in productie; root-`.htaccess` blokkeert ze als extra laag.

## Testen

- Automatische testsuite met PHPUnit in `tests/` (unit-tests voor `app/Services`, `app/Http`; geen database- of view-rendering-afhankelijkheden — `tests/bootstrap.php` laadt alleen de autoloader en de globale helpers, zonder sessie te starten of te verbinden met de database).
- Lint via PHP_CodeSniffer (`phpcs.xml`, PSR-12) en static analysis via PHPStan (`phpstan.neon`, level 5). `app/views/**` is uitgesloten van phpcs (HTML/PHP-mix, niet op PSR-12 gebouwd).
- `composer install` installeert automatisch een git pre-commit hook (`tools/pre-commit`, geïnstalleerd door `Tools\HookInstaller`) die `composer check` draait en de commit blokkeert bij falende lint/stan/tests.
- Daarnaast: handmatige smoke-checklist in `README.md` §Testen, plus herhaald draaien van `scripts/install.php` om idempotentie te bevestigen — dit dekt DB-/view-integratiepaden die de unit-tests bewust niet aanraken.
