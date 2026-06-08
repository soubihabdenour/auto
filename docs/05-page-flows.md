# 05 — Page Flows & User Journeys

All flows use Mermaid syntax. Render in any Mermaid-aware viewer (VS Code preview, GitHub, Notion).

---

## 1. Visitor — Discovery to Lead (the golden path)

```mermaid
flowchart TD
    A([Visitor lands on /]) --> B{Locale detected?}
    B -- URL has /ar/, /fr/, /en/ --> D[Set locale]
    B -- Cookie present --> D
    B -- Accept-Language --> D
    B -- None --> C[Default to ar]
    C --> D
    D --> E[Render homepage]
    E --> F{What does visitor do?}
    F -- Clicks Browse Cars --> G[Vehicle listing]
    F -- Clicks featured card --> H[Vehicle detail]
    F -- Uses quick search --> G
    F -- Clicks Request a Vehicle --> R[General request form]
    F -- Bounces --> X([exit])

    G --> G1[AJAX filter results]
    G1 --> H[Vehicle detail]

    H --> H1{Engaged with CTA?}
    H1 -- WhatsApp button --> W[wa.me link opens<br/>+ log click event]
    H1 -- Request Quote --> Q[Quote form modal]
    H1 -- Reserve --> RV[Reserve form modal]
    H1 -- Just browsing --> H2[Scroll / leave]

    Q --> S[POST /inquiry/quote<br/>CSRF + rate-limit checks]
    RV --> S
    R --> S
    S --> SV[Validate input]
    SV -- invalid --> SE[Show inline errors]
    SV -- valid --> SC[Create Lead record<br/>Send admin email<br/>Log analytics]
    SC --> ST[Success page<br/>+ WhatsApp follow-up button]
    ST --> END([Lead captured ✓])
    W --> END
```

## 2. Visitor — Vehicle Detail page deep dive

```mermaid
flowchart TD
    A([/{locale}/vehicles/{slug}]) --> B[Controller@show]
    B --> C[Repository::findBySlug]
    C -- not found --> NF[404 page]
    C -- found --> D[Eager load:<br/>images, videos,<br/>inspection, translation,<br/>brand, model, body_type]
    D --> E[Increment views_count<br/>fire-and-forget]
    E --> F[Build:<br/>SEO meta + JSON-LD Vehicle<br/>Estimator output<br/>WhatsApp prefill<br/>Similar vehicles]
    F --> G[Render view]
    G --> H{Visitor interaction}
    H -- Image click --> I[Open lightbox / fullscreen]
    H -- Thumb click --> J[Swap main image]
    H -- Tab click --> K[Smooth-scroll + active state]
    H -- WhatsApp tap --> WA[Open wa.me<br/>POST /events/whatsapp]
    H -- Quote CTA --> Q[Open quote modal]
    H -- Reserve CTA --> RV[Open reserve modal]
    Q --> POST[Submit lead]
    RV --> POST
    POST --> Done([Lead success ✓])
```

## 3. Visitor — AJAX Vehicle Filtering

```mermaid
sequenceDiagram
    participant U as Visitor
    participant JS as Browser JS
    participant H as History API
    participant S as Server (Vehicle@filter)
    participant DB as MySQL

    U->>JS: Selects brand, year, fuel filter
    JS->>JS: Debounce 300ms
    JS->>H: pushState new query string
    JS->>S: GET /{locale}/vehicles/filter?...
    S->>S: Validate + sanitize filters
    S->>DB: SELECT vehicles WHERE ...<br/>JOIN translations + cover image<br/>LIMIT page
    DB-->>S: rows
    S->>S: Render partial "vehicle-card.php" × N
    S-->>JS: { html, count, has_more }
    JS->>JS: Replace grid HTML<br/>Update count badge
    Note over JS: If scroll near bottom, fetch next page
```

## 4. Lead Submission — Server-side flow

```mermaid
flowchart TD
    A[POST /inquiry] --> B{CSRF valid?}
    B -- no --> X1[419 + log]
    B -- yes --> C{Rate limit OK?<br/>5/h per IP}
    C -- no --> X2[429 Too many requests]
    C -- yes --> D[Validator]
    D -- fail --> X3[Re-render form<br/>with errors]
    D -- pass --> E[Honeypot field empty?]
    E -- no --> X4[Silent 200<br/>discard]
    E -- yes --> F[Normalize phone<br/>Hash IP + UA]
    F --> G[LeadService::store]
    G --> H[INSERT leads]
    H --> I[Queue email to admin<br/>(synchronous in v1)]
    I --> J[Log audit_logs]
    J --> K[Redirect to thank-you page]
    K --> END([Done])
```

## 5. Admin — Authentication flow

```mermaid
flowchart TD
    A[/admin/login GET/] --> B[Show login form]
    B --> C[POST /admin/login]
    C --> D{Throttle check<br/>5 attempts / 15 min}
    D -- locked --> X1[Show locked message<br/>+ countdown]
    D -- ok --> E[Validate email + pwd]
    E -- user not found OR wrong pwd --> F[Increment throttle]
    F --> G[Generic error: invalid credentials]
    E -- ok --> H{is_active?}
    H -- no --> X2[Account disabled]
    H -- yes --> I[session_regenerate_id]
    I --> J[Set $_SESSION user_id, role]
    J --> K{remember_me?}
    K -- yes --> L[Set persistent cookie<br/>+ remember_token]
    K -- no --> M[Skip]
    L --> N[Update last_login_at]
    M --> N
    N --> O[Reset throttle]
    O --> P[Audit log: login]
    P --> Q[Redirect /admin]
```

## 6. Admin — Vehicle creation flow

```mermaid
flowchart TD
    A[/admin/vehicles/create GET/] --> B[Show empty form<br/>brands & models prefetched]
    B --> C[POST /admin/vehicles]
    C --> CSRF{CSRF + auth?}
    CSRF -- fail --> X[403]
    CSRF -- ok --> V[Validate]
    V -- fail --> RB[Re-render with errors]
    V -- ok --> S[Slug generator:<br/>year-brand-model-vin_tail<br/>(uniqueness check)]
    S --> T1[BEGIN TRANSACTION]
    T1 --> T2[INSERT vehicles]
    T2 --> T3[INSERT vehicle_translations × locales]
    T3 --> T4[COMMIT]
    T4 --> R[Redirect to edit page<br/>with success flash]
    R --> E[Edit view, Media tab pre-open]
    E --> U[User uploads images]
    U --> UP[POST /admin/vehicles/{id}/images]
    UP --> IM[ImageProcessor:<br/>validate mime<br/>generate thumb/medium/large<br/>strip EXIF<br/>convert to webp + jpeg]
    IM --> ST[Storage::put → public/uploads/vehicles/{id}/]
    ST --> DB2[INSERT vehicle_images]
    DB2 --> RET[Return JSON: id, url, thumb_url]
    RET --> UI[Append thumb in UI]
```

## 7. Admin — Lead management flow

```mermaid
stateDiagram-v2
    [*] --> new : Lead created
    new --> contacted : Admin calls / messages
    contacted --> qualified : Customer responds positively
    contacted --> lost : No response (timeout)
    qualified --> negotiating : Discussing terms
    negotiating --> won : Deposit received
    negotiating --> lost : Customer drops out
    won --> [*]
    lost --> [*]
    new --> lost : Spam / invalid
```

## 8. Locale switching flow

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant M as LocaleMiddleware
    participant T as Translator

    U->>B: Click "FR" in language switcher
    B->>B: Build URL: /fr{current_path_without_locale}
    B->>M: GET /fr/vehicles/2022-hyundai-tucson-...
    M->>M: Parse locale from URL
    M->>M: Set cookie locale=fr (1 year)
    M->>T: Translator::setLocale('fr')
    T->>T: Load resources/lang/fr/*.php
    T->>T: Merge admin overrides from translations table
    M-->>B: Render page in French
```

## 9. SEO indexing flow

```mermaid
flowchart TD
    A[Search engine crawls /sitemap.xml] --> B[Page@sitemap]
    B --> C[Cached?<br/>storage/cache/sitemap.xml]
    C -- yes & fresh < 1h --> D[Stream cached]
    C -- no --> E[Generate:<br/>- static pages × 3 locales<br/>- vehicle pages × 3 locales<br/>- page records × 3 locales]
    E --> F[Write cache]
    F --> D
    D --> G[Crawler hits /ar/vehicles/{slug}]
    G --> H[Vehicle@show renders]
    H --> I[Response includes:<br/>title, meta, OG, hreflang,<br/>JSON-LD Vehicle schema,<br/>canonical]
    I --> J[Search engine indexes]
    J --> K[User Google search]
    K --> L[Rich snippet with image,<br/>price, hours of operation]
```

## 10. Image upload pipeline

```mermaid
flowchart LR
    U[Upload file] --> V{Mime whitelist}
    V -- reject --> E1[Error]
    V -- ok --> S{Size < 10 MB?}
    S -- no --> E2[Error]
    S -- ok --> EX[Strip EXIF<br/>GPS / camera info]
    EX --> R{Re-encode<br/>image/webp<br/>image/jpeg fallback}
    R --> T1[400w thumb]
    R --> T2[800w medium]
    R --> T3[1600w large]
    R --> T4[orig (stored, never served)]
    T1 --> ST[Storage::put]
    T2 --> ST
    T3 --> ST
    T4 --> ST
    ST --> DB[(vehicle_images row)]
    DB --> URL[Public URL via Storage::url]
```

## 11. Page-level data dependencies (cheat sheet)

| Page             | Data needed                                                                | Queries  |
|------------------|----------------------------------------------------------------------------|----------|
| Home             | site settings, 8 featured vehicles, 4 testimonials, page-meta              | 4        |
| Vehicle listing  | brands/models for filters, vehicles page, count                            | 4–5      |
| Vehicle detail   | vehicle+translation, images, videos, inspection, brand, model, similar(4)  | 8–10     |
| Why Korea        | page translation, settings                                                 | 2        |
| Import Process   | page translation                                                           | 1        |
| Testimonials     | testimonials+translations                                                  | 2        |
| Contact          | settings                                                                   | 1        |
| Admin dashboard  | counts, recent leads, popular vehicles, click events agg                   | 4        |
| Admin vehicle ed.| vehicle + relations, brands list, models list, body_types                  | 5        |
| Sitemap          | all published vehicles + pages × locales                                   | 2 (cached)|
