# 06 — Development Roadmap

A single-developer estimate is given per task. Adjust for team size. Phases are sequenced — finish each before moving to the next.

Legend: 🟢 must-have for launch · 🟡 nice-to-have for launch · 🔵 post-launch

---

## Phase 1 — Foundation (3–4 days)

Goal: a request reaches a controller, renders a localized view, no styling yet.

- 🟢 Project scaffolding (folder layout from `01-architecture.md`)
- 🟢 `composer.json` for dev-only deps (PHPUnit, Whoops, phpstan optional)
- 🟢 `.env.example`, `.gitignore`, `INSTALL.md`
- 🟢 `public/.htaccess` — front controller rewrites, HTTPS redirect, gzip/brotli, security headers, long-cache for `/assets/`
- 🟢 `public/index.php` front controller
- 🟢 Manual PSR-4 autoloader (no Composer in prod)
- 🟢 `Container` (tiny DI), `Config`, `.env` loader
- 🟢 `Database` (PDO singleton, exception mode, persistent off)
- 🟢 `Router` + `Request` + `Response` + route registration in `config/routes.php`
- 🟢 `View` engine (raw PHP partials + `e()` escaping)
- 🟢 `Session` wrapper, `Csrf` helper
- 🟢 `LocaleMiddleware` + `Translator` + `t()` helper
- 🟢 `resources/lang/{ar,fr,en}/common.php` (initial keys)
- 🟢 Layout `layouts/public.php` with `dir` + Bootstrap RTL/LTR loader
- 🟢 404 / 500 error views + `ErrorHandler`
- 🟢 Run schema migrations + seed admin/brands/models

**Acceptance:** GET `/` redirects to `/ar/`, renders an empty "Hello" page in Arabic, RTL, with header/footer.

---

## Phase 2 — Public Site (6–8 days)

Goal: visitors can browse, filter, see vehicle detail, submit leads.

### 2.1 Homepage
- 🟢 Hero section (configurable headline/sub via settings)
- 🟢 Quick search bar (brand/model dropdowns, budget, year)
- 🟢 Featured vehicles strip (8 cards)
- 🟢 Why Korea cards (4)
- 🟢 How it works timeline
- 🟢 Testimonial carousel (Bootstrap)
- 🟢 FAQ accordion
- 🟢 Final CTA + footer

### 2.2 Vehicle listing
- 🟢 Server-rendered first page (SEO)
- 🟢 Filters sidebar (brand/model/year/price/mileage/fuel/transmission/body)
- 🟢 Sort dropdown (newest, price asc/desc, mileage asc, year desc)
- 🟢 AJAX filter endpoint `/vehicles/filter` returning rendered partial
- 🟢 Pagination (offset, 12/page)
- 🟢 Empty state + skeleton loaders
- 🟢 URL state preserved (history pushState)
- 🟡 Model dropdown depends on brand (AJAX cascade)

### 2.3 Vehicle detail
- 🟢 Image gallery (main + thumbs + lightbox + keyboard nav)
- 🟢 Sticky right-column price + CTAs (desktop)
- 🟢 Sticky bottom CTA bar (mobile)
- 🟢 Tabs: Overview / Specs / Inspection / Cost / Gallery / Video
- 🟢 Inspection visual bars (color-coded by score band)
- 🟢 Import Cost Estimator widget
- 🟢 Similar vehicles strip
- 🟢 WhatsApp link builder with localized prefill
- 🟢 Quote / Reserve / Inquiry modal forms with validation + CSRF
- 🟢 Lead submission endpoints, success page, admin email
- 🟢 SEO meta + JSON-LD Vehicle + OG + hreflang

### 2.4 Trust pages
- 🟢 Why Korea (content-managed)
- 🟢 Import Process (visual timeline)
- 🟢 Testimonials grid
- 🟢 About
- 🟢 Contact (form + map embed optional)
- 🟢 Request-a-Vehicle generic form

### 2.5 Lead capture infrastructure
- 🟢 `LeadService` + repository
- 🟢 Honeypot + 5-req/hour IP throttle
- 🟢 `Mailer` (`PhpMailerAdapter` via SMTP)
- 🟢 WhatsApp click event logging
- 🟢 Phone normalization (E.164 best-effort)

**Acceptance:** A visitor can find a vehicle, view its details, click WhatsApp, OR submit a quote form. Admin receives an email. The lead appears in the DB.

---

## Phase 3 — Admin Panel (5–7 days)

### 3.1 Auth
- 🟢 Login form + throttle table
- 🟢 Session regeneration, secure cookie flags
- 🟢 `AuthMiddleware` on `/admin/*`
- 🟢 Logout + audit log
- 🟡 Remember-me cookie

### 3.2 Dashboard
- 🟢 KPI cards (vehicles, leads, new this week, reserved)
- 🟢 Recent leads table
- 🟢 Popular vehicles list (by views_count)
- 🟡 WhatsApp clicks sparkline

### 3.3 Vehicle CRUD
- 🟢 Index with filters + search + pagination
- 🟢 Create form (tabs: Info / Translations / Media / Inspection / SEO)
- 🟢 Edit form (same)
- 🟢 Delete (soft via `status=archived` button + hard delete confirmation)
- 🟢 Brand/model dropdowns
- 🟢 Slug generator + uniqueness check
- 🟢 Image uploader (multi, drag-drop, sortable, alt-text editor, cover toggle)
- 🟢 Image pipeline (validate, resize, webp+jpeg, EXIF strip)
- 🟢 Video: local upload OR YouTube/Vimeo URL
- 🟢 Inspection sub-form
- 🟢 SEO sub-form
- 🟡 Preview-as-public button

### 3.4 Lead management
- 🟢 Inbox with filters (status/type/source/date)
- 🟢 Detail page (customer info + vehicle link + message + timeline)
- 🟢 Notes (admin-only)
- 🟢 Status transitions
- 🟢 Assign to user
- 🟢 CSV export

### 3.5 Testimonials CRUD
- 🟢 List, create, edit, delete, publish toggle
- 🟢 Translations per locale

### 3.6 Settings
- 🟢 Edit settings (general/contact/estimator/SEO/locales)
- 🟢 FX rate field
- 🟢 Validate types (`type` enum on each setting)

### 3.7 Translations editor
- 🟡 Browse file-based strings + override via `translations` table
- 🟡 Inline edit with locale tabs

**Acceptance:** Admin can fully manage vehicles end-to-end, see leads, and update settings without touching code.

---

## Phase 4 — Polish, SEO, Performance (3–4 days)

- 🟢 `sitemap.xml` generator (cached, regenerated on vehicle save)
- 🟢 `robots.txt`
- 🟢 JSON-LD `Organization` + `BreadcrumbList`
- 🟢 `<link rel="alternate" hreflang>` on every page
- 🟢 Image `<picture>` srcset variants
- 🟢 Critical CSS inline for hero
- 🟢 Lazy-load images below the fold
- 🟢 Minify CSS/JS via simple Node script (optional Composer alt)
- 🟢 Brotli/gzip via .htaccess
- 🟢 Cache headers
- 🟢 Lighthouse pass: mobile ≥ 90 perf, ≥ 95 SEO, ≥ 95 a11y
- 🟢 axe-core a11y audit on key pages
- 🟢 Cross-browser smoke (Safari iOS, Chrome Android, Edge desktop)
- 🟡 Schema for `Service` and `Product` (for vehicles)
- 🟡 Open Graph image generator (vehicle title overlay)

---

## Phase 5 — Pre-launch hardening (2–3 days)

- 🟢 Reset admin password
- 🟢 Replace all placeholder copy (settings + page content)
- 🟢 Replace placeholder WhatsApp/email
- 🟢 Final cost estimator review with operations team
- 🟢 Production `.env` file
- 🟢 HTTPS cert (Let's Encrypt)
- 🟢 cPanel/server deployment checklist
- 🟢 Daily DB backup cron
- 🟢 Error log rotation
- 🟢 Privacy policy + Terms pages
- 🟢 Cookie consent banner (locale-aware)
- 🟢 Google Analytics 4 / Plausible (privacy-friendly)
- 🟢 Google Search Console verification + sitemap submit
- 🟢 Facebook Pixel (if running paid social)

---

## Phase 6 — Post-launch (continuous)

### 6.1 Iteration (weeks 1–4)
- Listen to actual lead conversation snippets — adjust copy
- A/B headlines on hero
- Optimize slow queries (real EXPLAIN data)
- Patch any SEO crawl issues

### 6.2 Feature roadmap (post-launch, prioritized)

| Order | Feature                       | Effort | Why                                          |
|-------|-------------------------------|--------|----------------------------------------------|
| 1     | Customer accounts             | M      | Foundation for wishlist, comparison, auctions|
| 2     | Vehicle comparison            | S      | Easy conversion lift                         |
| 3     | Wishlist / save vehicle       | S      | Lead nurturing                               |
| 4     | Email digest of new arrivals  | S      | Re-engagement                                |
| 5     | Auction module                | L      | Strategic                                    |
| 6     | Online deposit payment        | M      | Reduce drop-off in negotiating stage         |
| 7     | Shipping tracking page        | M      | Trust + after-sale ops                       |
| 8     | AI vehicle recommendation     | M      | Based on browsing history + filter signals   |
| 9     | Mobile app (REST API)         | L      | Native app for repeat customers              |
| 10    | WhatsApp Business API auto-replies | M | Lead response speed                          |

---

## Estimates Summary

| Phase | Description                | Effort   |
|-------|----------------------------|----------|
| 0     | Architecture (this)        | done     |
| 1     | Foundation                 | 3–4 d    |
| 2     | Public site                | 6–8 d    |
| 3     | Admin panel                | 5–7 d    |
| 4     | Polish/SEO/perf            | 3–4 d    |
| 5     | Pre-launch                 | 2–3 d    |
| **MVP total** | **end-to-end launch** | **~3–4 weeks solo, ~2 weeks pair** |

---

## Risks & Mitigations

| Risk                                           | Mitigation                                                |
|------------------------------------------------|-----------------------------------------------------------|
| Shared hosting CPU limits during image upload  | Image pipeline runs synchronously in admin only; use OPcache; reduce orig storage to 1600w max |
| Arabic typography / font rendering issues      | Test on real Android devices early; ship 2 fonts with `font-display: swap` |
| Customs/shipping cost variability              | Make estimator clearly an estimate; admin-editable; show range, not single number, in v1.1 |
| Lead spam                                      | Honeypot + IP throttle + email confirmation step (v1.1) |
| FX rate drift                                  | Weekly admin update; show "as of {date}" beside DZD price |
| Slow first-byte on shared host                 | Aggressive `Cache-Control` + filesystem cache for homepage and listing |
| Image hotlinking                               | Referrer header check in .htaccess for `/uploads/`        |
| Forgetting to publish translations             | Validator warns when saving vehicle with missing locale translations |

---

## Definition of Done — MVP Launch

- [ ] All Phase 1–5 🟢 items complete
- [ ] Lighthouse mobile: Perf ≥ 90, SEO ≥ 95, A11y ≥ 95
- [ ] axe-core: zero serious/critical
- [ ] No console errors on any public page
- [ ] All public pages exist in AR, FR, EN
- [ ] Sitemap valid and submitted
- [ ] At least 20 real vehicles seeded
- [ ] Admin can: create vehicle in <5 min, see leads, export CSV
- [ ] Lead → admin email roundtrip < 2 min
- [ ] WhatsApp link tested on Android + iOS
- [ ] Daily DB backup verified
