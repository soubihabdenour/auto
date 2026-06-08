# Installation Guide

## Requirements

- PHP 8.3+ with extensions: `pdo_mysql`, `mbstring`, `intl`, `gd` (or `imagick`), `openssl`, `json`, `fileinfo`
- MySQL 8.0+
- Apache 2.4+ with `mod_rewrite` (or Nginx with equivalent rewrites)
- Composer (only needed for dev tools — production runs without it)

## Quick start (development)

```bash
# 1. Clone
git clone <repo-url> korea-auto-export
cd korea-auto-export

# 2. Configure environment
cp .env.example .env
# edit .env — set DB_*, APP_URL, APP_KEY (any 32+ random chars)

# 3. Create the database
mysql -uroot -p -e "CREATE DATABASE koreaautoexport CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Run the installer (schema + reference seed + optional demo data + admin)
php bin/install.php --with-demo

# 5. (Optional) Install dev tools
composer install

# 6. Serve
php -S 127.0.0.1:8000 -t public
# or:  composer serve
```

Visit http://127.0.0.1:8000 — you should be redirected to `/ar/` and see the full homepage with 5 featured demo vehicles, why-Korea cards, process timeline, testimonial carousel, and FAQ. Omit `--with-demo` for a clean install.

## Acceptance test plan

Hit each URL and confirm the listed behavior. All three locale prefixes (`ar`, `fr`, `en`) should work identically with translated copy; `ar` switches to RTL.

| URL                                                | What to verify                                                              |
|----------------------------------------------------|------------------------------------------------------------------------------|
| `GET  /`                                           | 302 → `/ar/`                                                                 |
| `GET  /{locale}/`                                  | Full homepage; 5 featured vehicle cards, why/process/FAQ, testimonial carousel auto-rotating |
| `GET  /{locale}/vehicles`                          | Listing with all 5 vehicles, filters sidebar, sort dropdown                  |
| `GET  /{locale}/vehicles?brand_id=1&fuel=diesel`   | Filter narrows to Hyundai Tucson; URL state preserved                        |
| `GET  /{locale}/vehicles?sort=price_asc`           | Cards re-ordered                                                              |
| `GET  /{locale}/vehicles?q=tucson`                 | One result (matches description)                                              |
| AJAX filter (any control on listing)               | Cards swap without full reload; URL updates via pushState                    |
| `GET  /{locale}/vehicles/2022-hyundai-tucson-diesel-7a3f` | Detail page: sticky right column with price + 3 CTAs; tabs Overview/Specs/Inspection/Cost; inspection bars colour-coded; cost estimator USD + DZD; similar vehicles |
| `GET  /{locale}/vehicles/missing-slug`             | Localized 404                                                                 |
| `GET  /{locale}/why-korea`                         | Reason cards + comparison table + CTA                                         |
| `GET  /{locale}/import-process`                    | Six-step vertical timeline                                                    |
| `GET  /{locale}/testimonials`                      | Grid of 3 testimonial cards                                                   |
| `GET  /{locale}/about`                             | About copy + values strip                                                     |
| `GET  /{locale}/contact`                           | Contact info + form; submit empty → in-form errors; valid → "Message received" |
| `GET  /{locale}/request-vehicle`                   | Multi-field form, valid submission flashes success                            |
| `POST /events/whatsapp`                            | Returns `{ok:true}`; rate-limited 5/h per IP                                 |
| Admin login                                        | Phase 3                                                                       |

Lead-submission spam protection (verify in DB / mail log):
- POST with `_csrf` missing → 419
- POST with `_website` (honeypot) filled → silent success, no `leads` row
- 6th lead submission in same hour from same IP+route → 429

`storage/logs/mail.log` records every successful lead. Switch to real SMTP via `MAIL_DRIVER=smtp` in `.env`.

## Browser smoke-test checklist

- Chrome / Safari / Firefox desktop: homepage hero + cards align cleanly
- Mobile Safari iOS: sticky bottom CTA bar appears on vehicle detail, respects `env(safe-area-inset-bottom)`
- AR locale: `<html dir="rtl">`, layout mirrors; lang-switcher swaps to `/fr/...` or `/en/...` preserving the path
- Gallery: thumb click swaps main image; ← / → keys navigate; main-image click opens lightbox; ESC closes
- WhatsApp CTA: opens `wa.me/{number}?text={localized prefill}`; verify a `whatsapp_click_events` row was written

## Production (shared Apache hosting)

For a complete launch sequence, follow **[docs/09-launch-checklist.md](docs/09-launch-checklist.md)** — this section is the quick version.

1. Upload the project so that `public/` is the document root.
   Typical cPanel layout:
   ```
   /home/user/koreaautoexport/    ← project root (NOT web-accessible)
   /home/user/koreaautoexport/public/ ← document root
   ```
2. Use the production .htaccess (HSTS + tighter CSP + force-HTTPS):
   ```bash
   cp public/.htaccess.production public/.htaccess
   ```
3. Copy `.env.example` → `.env` and edit:
   ```ini
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://koreaautoexport.dz
   SESSION_SECURE=true
   SESSION_SAMESITE=Strict
   MAIL_DRIVER=smtp
   ```
4. Generate an `APP_KEY` and write it to `.env`:
   ```bash
   php bin/keygen.php --write
   ```
5. Create the database (cPanel phpMyAdmin or `mysql -u…`), then run the installer:
   ```bash
   php bin/install.php
   # Prompts for admin email + password — store the password in your password manager.
   ```
6. Permissions:
   ```bash
   chmod -R 775 storage public/uploads
   chown -R www-data:www-data storage public/uploads   # adjust user
   chmod 0600 .env
   ```
7. Install the backup cron (`crontab -e`):
   ```cron
   0 3 * * * cd /home/user/koreaautoexport && /usr/bin/php bin/backup.php >> storage/logs/backup.log 2>&1
   ```
8. After SSL is live, sign in to `/admin/settings` and:
   - Set the real WhatsApp number, contact phone + email, lead-notification email
   - Replace the placeholder cost-estimator values with operations-team numbers
   - Enter Plausible / GA4 measurement ID + Google Search Console verification token (Analytics group)
9. Submit `https://koreaautoexport.dz/sitemap.xml` to Google Search Console.

## Production-relevant scripts

| Command | What it does |
|---|---|
| `php bin/install.php --with-demo`     | Schema + reference seed + 5 demo vehicles + interactive admin creation |
| `php bin/install.php --admin-only`    | Rotate / reset the admin password without touching the schema |
| `php bin/install.php --no-admin`      | Schema + seeds only, no admin prompt |
| `php bin/keygen.php`                  | Print a fresh 64-char APP_KEY |
| `php bin/keygen.php --write`          | Generate and write APP_KEY into the existing .env in place |
| `php bin/backup.php`                  | Daily DB dump → `storage/backups/kae_YYYY-MM-DD_HHMMSS.sql.gz`; 30-day + 12-month retention; rotates `php_error.log` when > 5 MB |

## Troubleshooting

| Problem                              | Fix                                                          |
|--------------------------------------|--------------------------------------------------------------|
| "Class not found"                    | Check that file is under `app/<Namespace>/<Class>.php` and namespace matches `App\<Namespace>` |
| 404 on every page                    | mod_rewrite disabled, or `.htaccess` overrides not allowed   |
| MySQL connection refused             | Check `DB_HOST`, `DB_PORT`, firewall, socket vs. tcp         |
| Whoops not shown on errors           | `APP_DEBUG=true` AND dev composer install                    |
| Sessions don't persist               | `storage/sessions/` not writable                             |
| Uploads fail                         | `public/uploads/` not writable, or `upload_max_filesize` too low |
| RTL layout broken                    | `dir="rtl"` attribute on `<html>` only set when locale=ar; clear cache |
