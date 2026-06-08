# 01 — System Architecture

## 1. Architectural Style

A **classic server-rendered MVC monolith** with a thin AJAX layer for vehicle filtering and gallery interactions. No framework. The architecture is deliberately conservative to:

- Run on cheap shared LAMP hosting (Algerian hosting providers, OVH, Hostinger)
- Stay debuggable for any PHP developer
- Avoid Composer-only deployment problems on shared hosts
- Allow incremental extraction of services later (auctions, payments, API)

## 2. High-Level Component Diagram

```
                ┌─────────────────────────────────────────────┐
                │              Browser (visitor)              │
                │   HTML + CSS + Bootstrap 5 RTL + vanilla JS │
                └───────────────┬─────────────────────────────┘
                                │ HTTPS
                ┌───────────────▼─────────────────────────────┐
                │         Apache 2.4 + mod_rewrite             │
                │  .htaccess routes everything → public/index.php │
                └───────────────┬─────────────────────────────┘
                                │
        ┌───────────────────────▼────────────────────────────┐
        │              Front Controller (index.php)           │
        │  • Bootstrap autoloader (PSR-4 manual)              │
        │  • Load env / config                                │
        │  • Build Container                                  │
        │  • Dispatch to Router                               │
        └───────────────────────┬────────────────────────────┘
                                │
        ┌───────────────────────▼────────────────────────────┐
        │                     Router                          │
        │  • Match request → Controller@method                │
        │  • Apply middleware (auth, locale, csrf, ratelimit) │
        └───────────────────────┬────────────────────────────┘
                                │
        ┌───────────────────────▼────────────────────────────┐
        │                  Controllers                        │
        │  Public:  Home, Vehicles, Inquiry, Page             │
        │  Admin:   Auth, Dashboard, Vehicles, Leads,         │
        │           Testimonials, Settings, Translations      │
        └─────┬─────────────────────┬─────────────────────────┘
              │                     │
       ┌──────▼──────┐       ┌──────▼──────┐
       │   Services  │       │    Models   │
       │  • Lead     │       │ • Vehicle   │
       │  • Estimate │       │ • Lead      │
       │  • Mailer   │       │ • User      │
       │  • Storage  │       │ • Testimonial│
       │  • Image    │       │ • Setting   │
       │  • Auth     │       └──────┬──────┘
       │  • i18n     │              │
       │  • SEO      │       ┌──────▼──────┐
       └──────┬──────┘       │ Repository  │
              │              │  (PDO)      │
              └──────┬───────┴──────┬──────┘
                     │              │
              ┌──────▼──────┐  ┌────▼─────┐
              │    Views    │  │   MySQL  │
              │ PHP partials│  │   8.0    │
              │   + i18n    │  └──────────┘
              └─────────────┘
```

## 3. Directory Layout

```
korea-auto-export/
├── public/                       # ← Apache document root
│   ├── index.php                 # Front controller
│   ├── .htaccess                 # Rewrite rules, cache headers
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   ├── img/                  # Static branding
│   │   └── fonts/
│   ├── uploads/                  # User-uploaded media (vehicle images/videos)
│   │   ├── vehicles/{id}/orig/
│   │   ├── vehicles/{id}/large/  # 1600w
│   │   ├── vehicles/{id}/medium/ # 800w
│   │   └── vehicles/{id}/thumb/  # 400w
│   └── sitemap.xml               # Generated
├── app/
│   ├── Core/
│   │   ├── Application.php
│   │   ├── Router.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── View.php
│   │   ├── Container.php
│   │   ├── Database.php          # PDO singleton
│   │   ├── Session.php
│   │   ├── Csrf.php
│   │   ├── Validator.php
│   │   └── Exception/
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── LocaleMiddleware.php
│   │   ├── CsrfMiddleware.php
│   │   └── RateLimitMiddleware.php
│   ├── Controllers/
│   │   ├── Public/
│   │   │   ├── HomeController.php
│   │   │   ├── VehicleController.php
│   │   │   ├── InquiryController.php
│   │   │   └── PageController.php
│   │   └── Admin/
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       ├── VehicleController.php
│   │       ├── LeadController.php
│   │       ├── TestimonialController.php
│   │       ├── SettingController.php
│   │       └── TranslationController.php
│   ├── Models/
│   │   ├── BaseModel.php
│   │   ├── User.php
│   │   ├── Vehicle.php
│   │   ├── VehicleImage.php
│   │   ├── VehicleVideo.php
│   │   ├── InspectionReport.php
│   │   ├── Lead.php
│   │   ├── Testimonial.php
│   │   ├── Setting.php
│   │   └── Translation.php
│   ├── Repositories/
│   │   ├── VehicleRepository.php
│   │   ├── LeadRepository.php
│   │   └── ...
│   ├── Services/
│   │   ├── Auth/AuthService.php
│   │   ├── I18n/Translator.php
│   │   ├── I18n/LocaleResolver.php
│   │   ├── Storage/StorageInterface.php
│   │   ├── Storage/LocalStorage.php
│   │   ├── Storage/S3Storage.php           # stub for v2
│   │   ├── Image/ImageProcessor.php        # GD or Imagick
│   │   ├── Lead/LeadService.php
│   │   ├── Lead/WhatsAppLinkBuilder.php
│   │   ├── Estimate/ImportCostEstimator.php
│   │   ├── Seo/MetaBuilder.php
│   │   ├── Seo/SchemaBuilder.php           # JSON-LD
│   │   ├── Seo/SitemapGenerator.php
│   │   ├── Mailer/MailerInterface.php
│   │   └── Mailer/PhpMailerAdapter.php
│   └── Helpers/
│       ├── url.php
│       ├── asset.php
│       ├── trans.php
│       ├── csrf.php
│       └── format.php           # currency, mileage, dates
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── public.php
│   │   │   └── admin.php
│   │   ├── partials/
│   │   │   ├── header.php
│   │   │   ├── footer.php
│   │   │   ├── nav.php
│   │   │   ├── lang-switcher.php
│   │   │   ├── vehicle-card.php
│   │   │   └── lead-buttons-sticky.php
│   │   ├── public/
│   │   │   ├── home.php
│   │   │   ├── vehicles/index.php
│   │   │   ├── vehicles/show.php
│   │   │   ├── pages/why-korea.php
│   │   │   ├── pages/import-process.php
│   │   │   ├── pages/testimonials.php
│   │   │   ├── pages/contact.php
│   │   │   └── pages/about.php
│   │   ├── admin/
│   │   │   └── ...
│   │   └── errors/
│   │       ├── 404.php
│   │       └── 500.php
│   └── lang/
│       ├── ar/
│       │   ├── common.php
│       │   ├── home.php
│       │   ├── vehicle.php
│       │   └── admin.php
│       ├── fr/
│       └── en/
├── config/
│   ├── app.php
│   ├── database.php
│   ├── routes.php
│   ├── locales.php
│   ├── seo.php
│   └── estimator.php        # shipping/customs formulas
├── database/
│   ├── migrations/
│   │   ├── 001_create_users.sql
│   │   ├── 002_create_vehicles.sql
│   │   ├── ...
│   ├── seeds/
│   │   ├── 001_admin_user.sql
│   │   ├── 002_settings.sql
│   │   └── 003_demo_vehicles.sql
│   └── schema.sql           # consolidated
├── storage/
│   ├── cache/
│   ├── logs/
│   └── sessions/
├── tests/                   # PHPUnit (added in Phase 4)
├── docs/                    # this folder
├── .env.example
├── .gitignore
├── composer.json            # only for dev tools (PHPUnit, Whoops)
├── INSTALL.md
└── README.md
```

## 4. Request Lifecycle

```
1. Browser → GET /fr/vehicles/2021-hyundai-tucson-diesel
2. .htaccess rewrites to /index.php?_url=/fr/vehicles/...
3. index.php boots:
   a. require autoloader
   b. load .env via Dotenv (lightweight, hand-rolled)
   c. set error handler (dev: Whoops, prod: custom logger)
   d. instantiate Container, register services
4. Container builds Router with routes from config/routes.php
5. Middleware pipeline:
   a. LocaleMiddleware    → resolves locale from URL prefix, sets translator
   b. CsrfMiddleware      → verifies token on POST/PUT/DELETE
   c. AuthMiddleware      → only on /admin/* routes
   d. RateLimitMiddleware → only on lead-submission endpoints
6. Controller@method called with Request DI
7. Controller calls Service(s), Service calls Repository, Repository hits DB via PDO
8. Controller returns View::render('public/vehicles/show', $data)
9. View renders inside layouts/public.php (sets <html dir="rtl"> for AR)
10. Response sent with security headers
```

## 5. Routing

Clean URLs, locale-prefixed (Algerian SEO benefits from per-language URLs):

| Pattern                                    | Controller@method                | Notes                       |
|--------------------------------------------|----------------------------------|-----------------------------|
| `GET  /`                                   | redirect → `/{default_locale}/`  | Default = `ar`              |
| `GET  /{locale}/`                          | Home@index                       |                             |
| `GET  /{locale}/vehicles`                  | Vehicle@index                    | Filters via query string    |
| `GET  /{locale}/vehicles/filter`           | Vehicle@filter (AJAX)            | Returns JSON or HTML partial|
| `GET  /{locale}/vehicles/{slug}`           | Vehicle@show                     | Slug = `{year}-{brand}-{model}-{vin_tail}` |
| `POST /{locale}/inquiry`                   | Inquiry@store                    | CSRF + rate limit           |
| `POST /{locale}/inquiry/quote`             | Inquiry@quote                    |                             |
| `POST /{locale}/inquiry/reserve`           | Inquiry@reserve                  |                             |
| `GET  /{locale}/why-korea`                 | Page@whyKorea                    |                             |
| `GET  /{locale}/import-process`            | Page@importProcess               |                             |
| `GET  /{locale}/testimonials`              | Page@testimonials                |                             |
| `GET  /{locale}/about`                     | Page@about                       |                             |
| `GET  /{locale}/contact`                   | Page@contact                     |                             |
| `GET  /sitemap.xml`                        | Page@sitemap                     | Generated on-the-fly + cached|
| `GET  /robots.txt`                         | Page@robots                      |                             |
| `GET  /admin/login`                        | Admin\Auth@showLogin             |                             |
| `POST /admin/login`                        | Admin\Auth@login                 |                             |
| `POST /admin/logout`                       | Admin\Auth@logout                |                             |
| `GET  /admin`                              | Admin\Dashboard@index            | Auth required               |
| `GET  /admin/vehicles`                     | Admin\Vehicle@index              |                             |
| `GET  /admin/vehicles/create`              | Admin\Vehicle@create             |                             |
| `POST /admin/vehicles`                     | Admin\Vehicle@store              |                             |
| `GET  /admin/vehicles/{id}/edit`           | Admin\Vehicle@edit               |                             |
| `PUT  /admin/vehicles/{id}`                | Admin\Vehicle@update             |                             |
| `DELETE /admin/vehicles/{id}`              | Admin\Vehicle@destroy            |                             |
| `POST /admin/vehicles/{id}/images`         | Admin\VehicleImage@upload        |                             |
| `GET  /admin/leads`                        | Admin\Lead@index                 |                             |
| `GET  /admin/leads/{id}`                   | Admin\Lead@show                  |                             |
| `PUT  /admin/leads/{id}/status`            | Admin\Lead@updateStatus          |                             |
| `GET  /admin/leads/export`                 | Admin\Lead@exportCsv             |                             |
| `GET  /admin/testimonials`                 | Admin\Testimonial@index          |                             |
| `GET  /admin/settings`                     | Admin\Setting@index              |                             |
| `GET  /admin/translations`                 | Admin\Translation@index          |                             |

## 6. Internationalization (i18n)

**Strategy: URL-prefixed locale + DB-backed translatable content + file-based UI strings.**

- **UI strings** (buttons, labels, errors): PHP array files in `resources/lang/{locale}/*.php`
  - Loaded by `Translator` per-request based on locale
  - Helper: `t('vehicle.specs.transmission')`
- **Content** (vehicle descriptions, page bodies, testimonials): stored translatable per record
  - Approach: separate `*_translations` tables (preferred over JSON column for query-ability and FULLTEXT search):
    - `vehicle_translations(vehicle_id, locale, title, description, meta_title, meta_description)`
    - `page_translations(page_key, locale, title, body, meta_title, meta_description)`
    - `testimonial_translations(testimonial_id, locale, body)`
- **Locale resolution priority**:
  1. URL prefix (`/ar/...`, `/fr/...`, `/en/...`)
  2. Cookie `locale`
  3. `Accept-Language` header
  4. Default = `ar`
- **RTL**: `<html dir="rtl" lang="ar">` for Arabic; Bootstrap 5 RTL build loaded conditionally; logical CSS properties (`margin-inline-start` etc.) where custom.
- **Fonts**:
  - Arabic: Noto Naskh Arabic / IBM Plex Sans Arabic
  - Latin: Inter (matches the Korean tech-brand feel)

## 7. Security

| Concern              | Mitigation                                                             |
|----------------------|------------------------------------------------------------------------|
| SQL injection        | PDO prepared statements; no string concatenation in queries            |
| XSS                  | Mandatory `e($value)` helper in views; CSP header                      |
| CSRF                 | Per-session token, validated on all unsafe methods                     |
| Session fixation     | `session_regenerate_id(true)` on login                                 |
| Password storage     | `password_hash(..., PASSWORD_BCRYPT, cost=12)`                         |
| Brute force          | Login throttling: 5 attempts / 15 min per IP+username, then 15-min lockout |
| File upload          | Whitelist mime (image/jpeg, png, webp; video/mp4), re-encode images via GD, randomize filenames, store outside public if possible (use signed URLs later) |
| Mass assignment      | Explicit DTOs / fillable arrays on models                              |
| Headers              | `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Content-Security-Policy` (script-src 'self' + nonce) |
| HTTPS                | Enforced via .htaccess redirect + HSTS                                 |
| Rate limiting        | Filesystem-backed counter per IP for lead submission (5/hour)          |
| Admin path           | `/admin` is not secret, but secured by auth + IP allowlist option in settings |
| Logging              | All admin actions logged to `audit_logs` table                         |

## 8. Performance

- **Server**:
  - PHP OPcache enabled
  - DB indexes on every filterable column (see schema)
  - Vehicle list query uses LIMIT + cursor pagination later, OFFSET for v1
  - Cache homepage featured + dropdown filter options in `storage/cache/` for 10 min
- **Frontend**:
  - Minified CSS/JS bundles served with `Cache-Control: public, max-age=31536000, immutable` (hashed filenames)
  - `<img loading="lazy" decoding="async">` everywhere except hero
  - WebP variants + `<picture>` fallback to JPEG
  - Responsive `srcset` 400/800/1600
  - Critical CSS inlined for hero
  - Bootstrap RTL CSS purged of unused utilities at build (Phase 4)
  - JS: ES modules, no jQuery (Bootstrap 5 ships its own JS)
- **DB query budgets**: home < 5 queries, vehicle list < 8, vehicle detail < 12. Repository layer logs slow queries in dev.

## 9. SEO

- Locale-prefixed URLs (`/ar/...`, `/fr/...`, `/en/...`) with `<link rel="alternate" hreflang>` for cross-language pages.
- Dynamic `<title>`, `<meta description>`, OG, Twitter card per page.
- JSON-LD `Vehicle` schema on detail pages, `Organization` schema site-wide, `BreadcrumbList` on inner pages.
- `sitemap.xml` auto-generated, includes all locales, regenerated when vehicles change.
- `robots.txt` allows everything except `/admin`.
- Image `alt` enforced (form validation in admin).
- Slugs: `2021-hyundai-tucson-diesel-7a3f` (4-char VIN suffix prevents collisions).
- Canonical URL on every page.

## 10. Lead Generation Mechanics

| Lead source        | Captured fields                                                | Channel       |
|--------------------|----------------------------------------------------------------|---------------|
| Inquiry form       | name, phone, whatsapp, country, vehicle_id, message            | Web → DB      |
| Quotation request  | inquiry fields + desired delivery date, financing yes/no       | Web → DB + mail |
| Reservation        | inquiry fields + reservation_deposit_acknowledged              | Web → DB + mail |
| WhatsApp click     | logged as event (vehicle_id, locale, timestamp, IP hash)       | Click-tracking  |

- WhatsApp link builder produces `https://wa.me/{phone}?text={prefill}` where prefill is the localized message including vehicle title and URL.
- Sticky bottom button bar on mobile (WhatsApp + Quote + Reserve).
- Admin gets email on every new lead (configurable in settings).
- Lead statuses: `new`, `contacted`, `qualified`, `negotiating`, `won`, `lost`.

## 11. Import Cost Estimator

Configurable in `config/estimator.php` and overridable via admin settings:

```
shipping_base       = 1500 USD          # container share, Busan → Algiers
shipping_per_kg     = 0.0               # negligible for vehicles
customs_rate        = 0.30              # 30% of (vehicle_price + shipping) — placeholder, admin-editable
local_taxes_rate    = 0.19              # TVA 19%
service_fee_flat    = 500 USD
service_fee_percent = 0.02
fx_usd_to_dzd       = (live setting)    # admin-updatable
```

Output on vehicle page:
```
Vehicle Price (FOB Busan)  $X,XXX
Shipping & Insurance       $Y,YYY
Customs Estimate           $Z,ZZZ
Service Fee                $A,AAA
─────────────────────────────────
Total Estimate (USD)       $T,TTT
Total Estimate (DZD)       T,TTT,TTT DA
```

Disclaimer banner: "Estimate only. Final cost confirmed after customs clearance."

## 12. Storage Abstraction

```php
interface StorageInterface {
    public function put(string $path, string $contents, array $opts = []): string;  // returns public URL
    public function delete(string $path): bool;
    public function url(string $path): string;
    public function exists(string $path): bool;
}
```

V1 implementation: `LocalStorage` writes under `public/uploads/`.
V2 swap: `S3Storage` (uses AWS SDK, signed URLs for private buckets). No code change in callers.

## 13. Deployment Model

**v1: shared Apache hosting.**
- Document root → `public/`
- Everything else lives one level above (not web-accessible)
- `.env` outside `public/`
- Deployment: `git pull` on server, run `database/migrations/*.sql` via cPanel/phpMyAdmin
- No build step required for v1 (CSS/JS shipped as source). Phase 4 adds a small Node build for asset bundling.

**Future v2: VPS + Nginx + PHP-FPM + Redis + S3** — schema is ready.

## 14. Future-Ready Extension Points

| Future feature        | Pre-built hook                                                    |
|-----------------------|-------------------------------------------------------------------|
| Vehicle auctions      | `vehicles.listing_type` enum already includes `auction`; tables for bids planned in 06-roadmap |
| User accounts         | `users.role` enum already includes `customer`; auth service generic |
| Vehicle comparison    | `wishlist` table planned; client-side comparison uses existing JSON endpoints |
| AI recommendations    | Search & filter already produce queryable signals; event log table planned |
| Online payments       | `payments` table reserved in schema; service interface in `Services/Payment/` |
| Shipping tracking     | `shipments` table reserved                                        |
| Mobile API            | Controllers thin — API controllers can wrap services and return JSON; route prefix `/api/v1/` reserved |

## 15. Open Decisions Deferred to Implementation

- Templating: raw PHP partials vs. tiny custom engine — leaning raw PHP for simplicity
- Form rendering: handcrafted helpers vs. a `FormBuilder` — leaning helpers
- Pagination: offset for v1, cursor for v2
- WhatsApp Business API integration: out of scope v1; mailto + wa.me link only
