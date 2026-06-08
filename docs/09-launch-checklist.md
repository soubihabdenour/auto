# 09 — Launch Checklist

Concrete, sequential go-live checklist. Do each item in order; tick it off before moving to the next.

---

## 1. Pre-deploy code review (T-7 days)

- [ ] All `_TODO` / placeholder copy replaced (search `git grep -i "placeholder\|TODO\|FIXME"`)
- [ ] Real WhatsApp number in `settings.whatsapp_number` (test it from a phone)
- [ ] Real contact email in `settings.contact_email` and `settings.lead_notification_email`
- [ ] Cost-estimator settings reviewed with operations team:
      `estimator_shipping_base_usd`, `estimator_customs_rate`,
      `estimator_tva_rate`, `estimator_service_fee_flat_usd`,
      `estimator_service_fee_percent`, `fx_usd_to_dzd`
- [ ] Privacy + Terms pages reviewed by an Algerian lawyer
- [ ] At least 20 real vehicles seeded with translations + inspection + images
- [ ] Featured vehicles flagged (`is_featured=1`)
- [ ] 6+ testimonials with real names/cities/translations

---

## 2. Production environment (T-3 days)

### 2.1 Server prerequisites

- [ ] PHP 8.3+ with `pdo_mysql`, `mbstring`, `intl`, `gd` (or `imagick`), `openssl`, `json`, `fileinfo`
- [ ] MySQL 8.0+ (utf8mb4 default)
- [ ] Apache 2.4+ with `mod_rewrite`, `mod_headers`, `mod_deflate`, `mod_brotli`
- [ ] `AllowOverride All` enabled for the project directory
- [ ] Disk space ≥ 5 GB for backups
- [ ] Cron access

### 2.2 Filesystem layout

```
/home/user/koreaautoexport/        ← project root (NOT web-accessible)
├── app/                            ← code
├── public/                         ← web root (point Apache here)
├── storage/                        ← writable; .htaccess denies access
├── .env                            ← outside web root, mode 0600
└── ...
```

- [ ] Document root set to `public/` in cPanel "Domains" or Apache vhost
- [ ] Permissions:
      ```bash
      chmod -R 775 storage public/uploads
      chown -R www-data:www-data storage public/uploads
      chmod 0600 .env
      ```
- [ ] `public/.htaccess.production` copied over `public/.htaccess`

### 2.3 Database

- [ ] Production MySQL database created with utf8mb4 / utf8mb4_unicode_ci
- [ ] Dedicated DB user with minimum privileges (SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER on this DB only)
- [ ] DB credentials in `.env` (not in shell history, not in cron files)

### 2.4 `.env` — production

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://koreaautoexport.dz
APP_KEY=<generate via: php bin/keygen.php --write>
SESSION_SECURE=true
SESSION_SAMESITE=Strict
MAIL_DRIVER=smtp
MAIL_FROM_ADDRESS=noreply@koreaautoexport.dz
```

- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` generated with `php bin/keygen.php --write`
- [ ] `SESSION_SECURE=true` (only sent over HTTPS)
- [ ] `MAIL_DRIVER=smtp` with real credentials (test with a manual lead submission, check it lands in the inbox)

---

## 3. HTTPS + DNS (T-2 days)

- [ ] DNS A/AAAA records pointing at the production host
- [ ] Let's Encrypt cert installed (cPanel one-click or `certbot --apache`)
- [ ] Auto-renewal cron verified: `certbot renew --dry-run`
- [ ] Visit `https://koreaautoexport.dz` — confirm valid cert chain (browser, no warnings)
- [ ] Once stable: enable HSTS via `Strict-Transport-Security: max-age=31536000; preload`
      (already in `public/.htaccess.production`; commit changes)
- [ ] Submit domain to https://hstspreload.org for browser preload list

---

## 4. Installer + admin (T-1 day)

```bash
# On production
cd /home/user/koreaautoexport
php bin/install.php --with-demo   # answers schema + reference + demo
# Prompted: admin email + password (≥ 12 chars, store in 1Password)
```

- [ ] `php bin/install.php` ran cleanly; final "Done" message
- [ ] Admin login works at `/admin/login`
- [ ] Initial admin password rotated immediately to a strong, unique value
- [ ] Production admin email matches `settings.lead_notification_email`

---

## 5. Daily DB backup cron

```cron
# crontab -e
0 3 * * * cd /home/user/koreaautoexport && /usr/bin/php bin/backup.php >> storage/logs/backup.log 2>&1
```

- [ ] Cron entry installed (`crontab -l`)
- [ ] First run successful: `ls storage/backups/`
- [ ] Restore test: `gunzip -c storage/backups/kae_*.sql.gz | head -50` shows valid SQL
- [ ] Offsite backup configured (rsync to S3 / a separate VPS / GDrive)

---

## 6. Analytics + Search Console

- [ ] Plausible domain (or GA4 measurement ID) entered in `/admin/settings` → Analytics
- [ ] Google Search Console verification token in `/admin/settings` → Analytics
- [ ] `/sitemap.xml` submitted in Search Console
- [ ] First indexing request submitted for `/ar/`, `/fr/`, `/en/`
- [ ] Cookie banner appears on a fresh browser session
- [ ] Click "Accept all" → analytics script loads (verify in DevTools → Network)
- [ ] Click "Reject" → no analytics script loaded

---

## 7. SEO sanity check

- [ ] `https://koreaautoexport.dz/sitemap.xml` returns valid XML
- [ ] `https://koreaautoexport.dz/robots.txt` references the sitemap
- [ ] Every page has unique `<title>` and `<meta description>`
- [ ] hreflang alternates correct on a few sample pages (View Source → search `hreflang`)
- [ ] Lighthouse mobile: Perf ≥ 90, SEO ≥ 95, A11y ≥ 95
- [ ] Schema rich-results test passes for one vehicle detail page:
      https://search.google.com/test/rich-results
- [ ] Open Graph preview correct via https://www.opengraph.xyz/

---

## 8. Trust + legal

- [ ] Privacy policy reviewed + linked in footer
- [ ] Terms of service reviewed + linked in footer
- [ ] Cookie banner copy reviewed
- [ ] "Last updated" dates accurate

---

## 9. Smoke tests (T-0)

Walk these URLs in production, ideally on real mobile + desktop:

- [ ] `/` → 302 → `/ar/`
- [ ] `/{locale}/` — full homepage, demo vehicles visible
- [ ] `/{locale}/vehicles` — listing + AJAX filter (change a filter, see results update without reload)
- [ ] `/{locale}/vehicles/<slug>` — detail page, gallery works, lightbox opens, modal opens
- [ ] WhatsApp button → opens wa.me with prefilled localized message
- [ ] Submit a contact-form lead → arrives in `lead_notification_email` inbox within 2 minutes
- [ ] `/admin/login` → can sign in, dashboard renders, see the lead just submitted
- [ ] Lang switcher: `/ar/` ↔ `/fr/` ↔ `/en/` preserves the path
- [ ] RTL: Arabic layout mirrors correctly

---

## 10. Day-1 ops

- [ ] Monitor `storage/logs/php_error.log` and `storage/logs/mail.log`
- [ ] Check Search Console daily for crawl errors
- [ ] Check leads inbox manually (until email delivery is verified stable)
- [ ] Daily backup verified for first 3 days
- [ ] At least one customer journey completed end-to-end (lead → reply → reservation → delivery)

---

## What we deliberately don't ship in v1

- Customer accounts / wishlist
- Online deposit payment
- Real-time shipping tracking
- Mobile app
- WhatsApp Business API auto-replies

These land post-launch — see `docs/06-roadmap.md` Phase 6 priorities.
