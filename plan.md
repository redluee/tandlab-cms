# Plan — Tandlab Concept 1 "Origineel ontwerp updated" + Custom PHP CMS

## 1. Beslissingen
- **Database**: SQLite lokaal, MySQL in productie → één PDO-laag met driver-toggle via config; portabele SQL.
- **CSS**: Vanilla modern CSS (custom properties, geen build-step).
- **Contact**: In-page sectie (`#contact`) op álle pagina's, zoals de originele site; footer met contactinfo + Google Maps.
- **Admin**: Nederlands, miniaal, in huisstijl (grijs/groen), alleen wat op de site getoond wordt.
- Alleen Concept 1 wordt gebouwd; geen abstracties voor de andere concepten.

## 2. Tech stack
- PHP 8.4 (aanwezig), PDO + `pdo_sqlite`/`pdo_mysql`.
- GD (`gd` aanwezig) voor image resize → WebP.
- Geen framework, geen Composer-packages, geen front-end build. Router = zelfgeschreven front controller.
- Sessie-auth, `password_hash()` (bcrypt), CSRF-tokens, honeypot.

## 3. Projectstructuur
```
Tandlab-CMS/
├── AGENTS.md
├── README.md
├── plan.md
├── config/
│   └── config.php            # leest config.ini / env; driver SQLite|MySQL
├── app/
│   ├── bootstrap.php         # sessions, autoload, DB, helpers
│   ├── db/
│   │   ├── Database.php      # PDO wrapper (portable SQL)
│   │   └── schema.sql        # + version for MySQL (AUTO_INCREMENT) & SQLite
│   ├── http/
│   │   └── Router.php        # static route table + params, 404
│   ├── controllers/
│   │   ├── PublicController.php
│   │   ├── ContactController.php
│   │   ├── AuthController.php
│   │   └── AdminController.php   # pages, tand, team, settings, messages, media
│   ├── models/               # Setting, Tandwerk, TeamMember, Message, User
│   ├── services/
│   │   ├── ImageService.php  # validate → resize → WebP → uploads/
│   │   ├── Mailer.php        # mail() default, SMTP-ready
│   │   ├── Validator.php, Csrf.php, Auth.php
│   └── views/
│       ├── layout/           # nav, hero, contact-section, footer, admin-shell
│       ├── public/           # home.php, tand.php, team.php
│       └── admin/            # login, home, tand, team, settings, berichten, afbeeldingen
├── public/
│   ├── index.php             # front controller
│   ├── .htaccess             # + nginx voorbeeld in README
│   └── assets/css/           # style.css + admin.css (vanilla)
├── storage/
│   ├── database.sqlite
│   ├── uploads/              # webp afbeeldingen (geseede + geüploade)
│   └── logs/
├── scripts/
│   └── install.php           # idempotent: schema + seed + eerste admin
└── content/                  # bestaande markdown + afbeeldingen (seed-bron)
```

## 4. Databasemodel
Portable DDL, geen MySQL-only backticks; `AUTOINCREMENT` is de enige driver-afwijking (in schema per driver).

- **users**: `id, username UNIQUE, password_hash, created_at`
- **site_settings**: `key PK, value TEXT` → adres (3 regels), telefoon, e-mail, ontvangers, openingstijden, map-embed-URL, privacy-url, nav-labels, footer-tekst, hero titel/subtitels/intro/USP's, hero slide 1/2
- **tandwerk**: `id, title, body, image_path, alt, sort_order, active`
- **team_members**: `id, name, role, bio, photo_path, sort_order, active`
- **messages**: `id, name, email, phone, subject, body, is_read, created_at`

Alle query's via prepared statements.

## 5. Routing (`public/index.php`)
| Pad | Actie |
|---|---|
| `/` | Home (hero slider, intro, USP's/trust, `#contact`, footer) |
| `/tand` | Tand (grid 2×3) |
| `/team` | Team (medewerkers + portretten) |
| `/contact/submit` | POST contactformulier (CSRF + honeypot) |
| `/admin` | redirect → login of dashboard |
| `/admin/{login,logout,paginas,tand,team,instellingen,berichten,afbeeldingen}` | CMS |
| anders | 404 |

Server: `.htaccess` (Apache) + `php -S` fallback-routing in `index.php`; nginx voorbeeld in README.

## 6. Publieke pagina's (Concept 1)
Algemeen: fixed navigatiebalk bovenaan, gecentreerde tekst, logo, actief item highlighted in `#78c39c` (WCAG-AA donker-tekst-op-groen contrasteert), semantische HTML, `defer` JS voor hero-slider (eenvoudige crossfade, géén dependency).

**Home**: banner-slider (2 afbeeldingen, crossfade), slogan "UW SPECIALIST IN KROON- EN BRUGWERK" met `bgHero.png` tandprofielen links én rechts van de tekst, intro + USP's ("Actief sinds 1985", "Erkend leerbedrijf", "Korte lijntjes"), trust-blok (40+ jaar, lokale samenwerking, moderne scan/3D-techniek), quick-link naar `#contact`. Daaronder de gezamenlijke `#contact`-sectie landelijke sectie (§7).

**Tand**: zelfde nav + footer. Grid 2×3:
1. image (laboratorium-pand.jpg) – 2. tekst **Laboratorium**
3. tekst **Samenwerking** – 4. image (samenwerking.jpg, apparatuur)
5. image (behandelkamer.jpg) – 6. tekst **Behandelkamer**
Data komt uit `tandwerk` (geseede als Laboratorium, Samenwerking, Behandelkamer, reorderbaar beheerd via CMS).

**Team**: zelfde nav + footer. Koptekst ("Wij zijn een erkend leerbedrijf…") + raster van medewerkers uit `team_members` met artistieke portretten (12 bestaande PNG's), naam + functie + bio.

**Footer**: linkerhelft contactinfo (adres, telefoon, e-mail, openingstijden), rechterhelft ge-embedde Google Map op adres van Tandlab.

## 7. Contactsectie (alle pagina's) + formulier
- Blok: adres, telefoon, e-mail, openingstijden, privacy-statement-link, embedded map.
- Formuliervelden: naam, e-mail, telefoon (optioneel), onderwerp (vraag / order / scan aanleveren), bericht.
- Bescherming: CSRF-token, honeypot-veld, basis-sanitatie/validatie, `htmlspecialchars` output.
- Opslag in `messages` + optionele e-mailnotificatie (`Mailer`) naar ontvangers uit instellingen.

## 8. CMS (admin, Nederlands, in huisstijl)
- **Login**: sessie, bcrypt, CSRF; eerste admin via install-script (CLI pwd). Formulier-gebaseerd, geen registratie.
- **Pagina's/Home**: hero titel, subtitel(s), intro, USP's, hero-slide-afbeeldingen droppen.
- **Tand**: werkstukken CRUD (titel, omschrijving, foto, volgorde, actief) → stuurt de 2×3 grid.
- **Team**: leden CRUD (naam, functie, bio, foto-upload, volgorde, actief).
- **Instellingen**: NAW, telefoon, e-mail + ontvangers, openingstijden, map-URL, privacy-url.
- **Berichten**: inbox (lees/ongelezen, verwijderen).
- **Afbeeldingen**: uploader met voorvertoning en automatische optimalisatie (§9).

## 9. Image pipeline (ImageService, GD)
- Validatie (mime/type, max bytes), resize op max breedte (hero 1920, content 1200, portret 800), convert → WebP (`imagewebp`), opslag in `storage/uploads`.
- Bij seed worden de bestaande `content/**/images/*` geconverteerd.
- Huidige `logo.png` en `bgHero.png` worden gekopieerd/gebruikt als zijnde (kleine/transparante assets → geen conversie).

## 10. Beveiliging
- PDO prepared statements; output-escaped; sessie (hardened cookie flags), sessie-rotatie bij login.
- CSRF op álle admin- en publieke formulieren.
- Honeypot, rate-limit-lite (timestamp per sessie) op contactformulier.
- Image-upload: whitelist + GD-recodering (geen scriptable bestanden opgeslagen).
- `.htaccess` blokkeert `storage/` en `config/`; `public/` is enige documentroot.

## 11. Config / environments
`config/config.php` leest `config.ini` of env; default SQLite (`storage/database.sqlite`), `DB_DRIVER=mysql` in productie met DSN/host/dbnaam/user/pass uit env. Voorbeeld-`.gitignore` (database.sqlite, uploads, config.ini).

## 12. Seed / migratie content
`scripts/install.php` (idempotent, CLI):
1. Maakt schema (SQLite of MySQL).
2. Seedit 3× `tandwerk`, 12× `team_members`, site_settings uit `content/**/content.md`.
3. Converteert afbeeldingen naar WebP in `storage/uploads`, schrijft paden terug.
4. Maakt admin-gebruiker aan (CLI-argumenten) als die nog niet bestaat.
Daarna: handmatige content-check tegen huidige WordPress staat (later stap, buiten v1).

## 13. AGENTS.md (wordt aangelegd in uitvoeringsfase)
Bevat: projectoverzicht & huisstijl (kleuren, patronen), stack, commands (`php -S localhost:8000 -t public`, `php scripts/install.php`), mappenstructuur, conventies (geen comments tenzij gevraagd, Nederlands UI, vanilla CSS met custom properties, PDO + escape, DB-portabiliteit, image pipeline, beveiliging), testing-aanpak.

## 14. Bouwvolgorde (milestones)
1. Skelet: config, bootstrap, Database, Router, layout-partials + basis CSS.
2. Schema + install/seed-script; verifieer seed uit `content/`.
3. Publieke pagina's (Home, Tand, Team) in Concept 1-layout met geseede data.
4. Contactformulier (CSRF/honeypot) + `messages`-opslag + notificatie.
5. Admin: login → Home-instellingen → Tand → Team → Instellingen → Berichten → Afbeeldingen.
6. ImageService pipeline klaarzetten en door admin-/seed-flow laten lopen.
7. Testen (zie §15), README + nginx-voorbeeld, AGENTS.md afronden.

## 15. Testen & runnen
- `php -S localhost:8000 -t public` voor dev.
- `php scripts/install.php` herhaalbaar uitvoeren op een lege DB (idempotentie-check).
- Basale regressie via een klein `scripts/smoke.php` (routes 200, formulier-CSRF werkt, login-werkcyclus) óf handmatige checklist.
- Handmatige QA: mobiel/desktop hero-slider, fixed nav, contactformulier-ontvangst.

## Open aannames
- Mail v1 via PHP `mail()`; SMTP later configureerbaar (geen extra dependency).
- Google Maps embed via een door Tandlab goedgekeurde iframe-URL (instelbaar, zonder API-key).
- Diensten op Tand beheerd als losse items (Laboratorium/Samenwerking/Behandelkamer = default seed) zodat de 2×3 grid klopt bij 3 items.