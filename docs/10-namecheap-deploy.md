# 10 — Namecheap Deployment Guide

A complete walkthrough for getting **Korea Auto Export** running on a Namecheap **shared hosting** plan (Stellar / Stellar Plus / Stellar Business). VPS / dedicated plans work the same way with more headroom; the steps below assume cPanel.

---

## 0. Before you start — verify prerequisites

In your Namecheap cPanel, confirm:

1. **PHP version** — `Select PHP Version` (or `MultiPHP Manager`) → set the domain to **PHP 8.1 or higher** (8.3 preferred).
2. **PHP extensions** — in the same tool, tick:
   - `pdo_mysql` ✓
   - `mbstring`  ✓
   - `intl`      ✓
   - `gd`        ✓ (or `imagick`)
   - `openssl`   ✓
   - `json`      ✓
   - `fileinfo`  ✓
3. **SSH access enabled** — `Manage SSH` → confirm it's on. (Stellar plan + above.) Without SSH, you can still install but it's more painful — see "No-SSH path" at the end.
4. **MySQL available** — `MySQL Databases` section visible in cPanel.

If any of these are missing, contact Namecheap support before continuing.

---

## 1. Buy + configure the domain

If you don't have a domain yet:

1. Buy `koreaautoexport.dz` (or `.com`) in Namecheap.
2. Point its nameservers at the same Namecheap hosting account (auto-configured if bought together).
3. Wait up to 1 hour for DNS propagation.

If you're using a **subdomain** (e.g. `app.example.com`):

1. cPanel → `Domains` → `Create A New Domain`.
2. Set the document root to `home/<user>/koreaautoexport/public` (you'll create this folder in step 3).

If you're using the **main domain**, the document root will be `public_html/` — see step 3 for the trick.

---

## 2. Create the production package on your laptop

```bash
cd /Users/ady/PycharmProjects/korea-auto-export
bin/prepare-deploy.sh --prod
```

Output:
```
── Packing /Users/ady/PycharmProjects/korea-auto-export/kae-deploy-20260608-150500.tar.gz
✓ Deploy archive ready
  …/kae-deploy-20260608-150500.tar.gz  (3.4M)
```

This is a clean tarball with:
- All app code
- Self-hosted fonts + assets
- DB schema and seeds
- Production `.htaccess` (HSTS + tightened CSP)
- An empty `storage/` tree with `.gitkeep` files
- A `DEPLOY_README.txt` with quick steps

Excluded: `.git`, `.env`, `vendor/`, `node_modules/`, runtime logs, prior backups, demo uploads.

---

## 3. Upload + extract on the server

### Via SSH (recommended)

From your laptop:
```bash
scp kae-deploy-*.tar.gz user@yourdomain.com:~
```

Then SSH in:
```bash
ssh user@yourdomain.com
cd ~
tar -xzf kae-deploy-*.tar.gz
mv korea-auto-export koreaautoexport     # shorter name, no dashes for shell convenience
rm kae-deploy-*.tar.gz
```

### Via cPanel File Manager (no SSH)

1. `Files` → `File Manager` → up to `/home/<user>` (your home).
2. `Upload` → pick the tarball.
3. Once uploaded, right-click it → `Extract` → target folder: your home directory.
4. Rename `korea-auto-export` → `koreaautoexport`.

---

## 4. Wire the domain to `public/`

Three scenarios depending on your hosting setup. Pick the one that matches.

### 4a. Subdomain or addon domain (cleanest)

You set the document root to `~/koreaautoexport/public` in step 1. **Skip to step 5.**

### 4b. Main domain, document root is `public_html/`

You can't change `public_html`'s location. Workaround: make `public_html` BE the project's `public/`.

```bash
# 1. Back up anything Namecheap put in public_html by default
mv public_html public_html.original

# 2. Symlink public_html to the project's public/
ln -s ~/koreaautoexport/public ~/public_html
```

If symlinks aren't allowed (rare on Namecheap, but possible), do this instead:
```bash
# Move everything from project's public/ into public_html
rm -rf public_html
mv koreaautoexport/public public_html

# Edit public_html/index.php — change BASE_PATH to the project root:
nano public_html/index.php
# Replace:    define('BASE_PATH', dirname(__DIR__));
# With:       define('BASE_PATH', $_SERVER['HOME'] . '/koreaautoexport');
```

### 4c. Sub-folder on the main domain (e.g. `example.com/cars`)

```bash
# Symlink one folder
ln -s ~/koreaautoexport/public ~/public_html/cars
```

Then visit `https://example.com/cars` — the app will serve from there. Note: the locale prefix becomes `/cars/ar/`, which works but is less clean than a real subdomain.

---

## 5. Create the MySQL database

cPanel → `MySQL Databases`:

1. **Create New Database**:
   - Database name: `koreaautoexport` → cPanel will prefix it (becomes `<user>_koreaautoexport`)
2. **Create a User**:
   - Username: `kae_app`
   - Password: generate a strong one, save it
   - Will become `<user>_kae_app`
3. **Add User to Database**:
   - Pick the user + database you just created
   - Privileges: tick **ALL PRIVILEGES** (or at minimum: SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX, REFERENCES, LOCK TABLES)

Note the **full** database name and username (with the cPanel prefix). Example:
```
DB_NAME=ady_koreaautoexport
DB_USER=ady_kae_app
DB_PASS=<the password you set>
DB_HOST=localhost
```

---

## 6. Configure `.env`

```bash
cd ~/koreaautoexport
cp .env.example .env
nano .env
```

Set at minimum:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://koreaautoexport.dz       # YOUR domain
APP_TIMEZONE=Africa/Algiers
APP_LOCALE=ar
APP_LOCALES=ar,fr,en

DB_HOST=localhost
DB_PORT=3306
DB_NAME=ady_koreaautoexport
DB_USER=ady_kae_app
DB_PASS=<password from step 5>
DB_CHARSET=utf8mb4

SESSION_NAME=kae_session
SESSION_LIFETIME=7200
SESSION_SECURE=true                       # only after HTTPS is live
SESSION_SAMESITE=Strict

STORAGE_DRIVER=local
STORAGE_PUBLIC_URL=/uploads

MAIL_DRIVER=smtp
MAIL_HOST=mail.koreaautoexport.dz         # use Namecheap's email server hostname
MAIL_PORT=587
MAIL_USER=noreply@koreaautoexport.dz
MAIL_PASS=<email account password>
MAIL_FROM_ADDRESS=noreply@koreaautoexport.dz
MAIL_FROM_NAME="Korea Auto Export"
```

Generate and install the app key:
```bash
php bin/keygen.php --write
```

Lock down `.env`:
```bash
chmod 0600 .env
```

---

## 7. Run the installer

```bash
cd ~/koreaautoexport
php bin/install.php
```

You'll see:
```
→ Running schema (schema.sql) ... ok (17 statements)
→ Running reference seed (00_reference.sql) ... ok (X statements)
Admin email [admin@koreaautoexport.dz]:        ← Enter your real admin email
Admin name [Site Administrator]:               ← or press Enter to keep default
Admin password (min 12 chars):                 ← TYPE A STRONG PASSWORD, IT WON'T ECHO
→ Admin user created: admin@yourdomain.com

✓ Done.
```

If you want demo data too (5 sample vehicles + 3 testimonials), use `--with-demo`.

---

## 8. Permissions

```bash
cd ~/koreaautoexport
chmod -R 0775 storage public/uploads
# On shared hosting the web user is usually the same as your shell user, so chown is not needed.
```

If you have permission issues serving uploads later, try `0755` or check cPanel's `File Manager → Permissions`.

---

## 9. SSL + HTTPS

cPanel → `Security` → `SSL/TLS Status` → `Run AutoSSL`. Wait 2–5 minutes for Let's Encrypt to issue the cert.

Once HTTPS works:

```bash
# Switch on HSTS + force-HTTPS in .htaccess
cd ~/koreaautoexport/public
# (the production .htaccess was already in place from --prod)
# OR if you used a non-prod archive:
cp .htaccess.production .htaccess
```

Verify in your browser:
- `http://koreaautoexport.dz` → should 301 to `https://`
- Browser shows valid cert chain

---

## 10. Daily backup cron

cPanel → `Advanced` → `Cron Jobs`. Add:

| Field | Value |
|---|---|
| Minute  | 0 |
| Hour    | 3 |
| Day     | * |
| Month   | * |
| Weekday | * |
| Command | `cd $HOME/koreaautoexport && /usr/bin/php bin/backup.php >> storage/logs/backup.log 2>&1` |

(Check `which php` over SSH — on some Namecheap plans PHP is at `/opt/cpanel/ea-php83/root/usr/bin/php`. Use whatever `which php` returns.)

---

## 11. Final settings

Sign in at `https://yourdomain.com/admin/login` and update via `/admin/settings`:

- **Contact**: real WhatsApp number, contact email, lead-notification email
- **Cost estimator**: replace placeholders with your ops team's actual numbers (shipping, customs rate, TVA, service fees, FX rate)
- **Analytics** (optional): Plausible domain, GA4 measurement ID, Google Search Console verification token

---

## 12. Day-1 verification

Walk these URLs in a fresh browser session:

- `https://yourdomain.com/` → 302 → `/ar/`
- `/{ar,fr,en}/` — homepage
- `/{locale}/vehicles` — listing
- `/{locale}/vehicles/<slug>` — pick a vehicle, verify gallery + cost estimator
- Submit a test lead from `/{locale}/contact` — wait 2 minutes for the email to arrive
- `/admin/login` — sign in, see the lead in the inbox
- `/sitemap.xml` — valid XML
- `/robots.txt` — has `Sitemap:` line
- Open Search Console → submit `https://yourdomain.com/sitemap.xml`

---

## No-SSH path

If your Namecheap plan doesn't include SSH, you can still install — it's just more clicks:

1. Steps 1–3: upload via File Manager + extract.
2. Step 5: create DB via `MySQL Databases`.
3. Step 6: edit `.env` via File Manager → right-click `.env` → `Edit`.
4. **Step 7 alternative**: import schema + seeds via phpMyAdmin:
   - cPanel → `phpMyAdmin` → select your DB → `Import` tab
   - Upload `database/schema.sql`, click Go
   - Upload `database/seeds/00_reference.sql`, click Go
   - (Optional) Upload `database/seeds/01_demo.sql`
5. **Create the admin user manually** by running this in phpMyAdmin → `SQL` tab (after replacing placeholders):

   First, generate a bcrypt hash. Easiest: use a one-off PHP file you upload to `public_html/_makehash.php`:

   ```php
   <?php
   echo password_hash('YourStrongPasswordHere', PASSWORD_BCRYPT, ['cost' => 12]);
   ```

   Visit `https://yourdomain.com/_makehash.php`, copy the hash, **then immediately delete the file**.

   Now in phpMyAdmin:
   ```sql
   INSERT INTO users (email, password_hash, name, role, is_active)
   VALUES ('you@example.com', '<paste hash here>', 'Your Name', 'admin', 1);
   ```

6. Steps 8–12: identical.

---

## Common Namecheap gotchas

| Symptom | Fix |
|---|---|
| `500 Internal Server Error` on every page | check `~/koreaautoexport/storage/logs/php_error.log` — usually a missing PHP extension or wrong PHP version selected |
| `.htaccess` ignored, no clean URLs | cPanel → `Apache Handlers` → confirm `AllowOverride All`; ticket Namecheap if locked |
| `Connection refused` on DB | `DB_HOST=localhost` (not 127.0.0.1) on shared hosting |
| Login → blank page | `storage/sessions/` not writable. `chmod -R 0775 storage` |
| Uploads broken | `public/uploads` not writable, or `upload_max_filesize` < 12 MB. Edit php.ini via `Select PHP Version → Options` |
| Fonts 404 | check that `public/assets/fonts/` was uploaded (case-sensitive on Linux) |
| Site shows but stylesheet 404 | wrong document-root config — confirm Apache is pointed at `koreaautoexport/public/`, not the project root |
| `/sitemap.xml` returns the homepage | mod_rewrite working but `/{locale}` pattern is eating the URL — re-check `.htaccess` is identical to `public/.htaccess.production` |

---

## Updating the live site later

```bash
# On laptop
cd /Users/ady/PycharmProjects/korea-auto-export
bin/prepare-deploy.sh --prod
scp kae-deploy-*.tar.gz user@yourdomain.com:~

# On server
ssh user@yourdomain.com
cd ~
tar -xzf kae-deploy-*.tar.gz -C kae-update --strip-components=0
rsync -a --exclude='.env' --exclude='storage/' --exclude='public/uploads/' \
      kae-update/korea-auto-export/ koreaautoexport/
rm -rf kae-update kae-deploy-*.tar.gz
# Re-run schema migrations only if database/schema.sql changed
# (Phase 6 will ship a real migration runner; for now diff schema.sql)
```

Or, much simpler if you have Git on the server:

```bash
# One-time
ssh user@yourdomain.com
cd ~/koreaautoexport
git remote add origin https://github.com/yourname/korea-auto-export.git

# Subsequent updates
git pull origin main
```

---

## Cost expectations (Namecheap, ~mid-2026)

| Plan | Cost / yr | Suitable for |
|---|---|---|
| Stellar         | ~$30 | Launch (≤ 5k visitors/mo, 1 domain, basic SSL) |
| Stellar Plus    | ~$60 | Comfortable launch (~30k visitors/mo, unlimited bandwidth, AutoBackup) |
| Stellar Business| ~$90 | Faster page response, ≥ 50k visitors/mo |
| VPS Pulsar      | ~$240 | When you outgrow shared (≥ 100k visitors/mo) |

Add `.com` or `.dz` domain ~$10–30/yr.

---

## You're live.

If anything in this guide doesn't match what you see in cPanel — Namecheap shuffles their menus every year or two — search the cPanel sidebar for the keyword (e.g. "SSL", "PHP", "MySQL") and it'll find the right page. The order of operations doesn't change.
