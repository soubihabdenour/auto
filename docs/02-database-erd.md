# 02 — Database ERD

MySQL 8.0, charset `utf8mb4`, collation `utf8mb4_unicode_ci`. All IDs are `BIGINT UNSIGNED AUTO_INCREMENT`.

## 1. Entity Overview

```
┌──────────────┐         ┌──────────────────────┐
│   users      │         │       leads          │
│──────────────│         │──────────────────────│
│ id (PK)      │◄──┐     │ id (PK)              │
│ email        │   │     │ vehicle_id (FK,NULL) │──┐
│ password_hash│   │     │ name                 │  │
│ name         │   │     │ phone                │  │
│ role         │   │     │ whatsapp             │  │
│ is_active    │   │     │ country              │  │
│ last_login_at│   │     │ city                 │  │
│ created_at   │   │     │ email                │  │
└──────────────┘   │     │ message              │  │
                   │     │ lead_type            │  │
                   │     │ status               │  │
                   │     │ source               │  │
                   │     │ assigned_to (FK)─────┼──┘
                   └─────│ locale               │
                         │ ip_hash              │
                         │ user_agent           │
                         │ created_at           │
                         │ updated_at           │
                         └──────────┬───────────┘
                                    │
                          ┌─────────▼────────┐
                          │   lead_notes     │
                          │──────────────────│
                          │ id (PK)          │
                          │ lead_id (FK)     │
                          │ user_id (FK)     │
                          │ body             │
                          │ created_at       │
                          └──────────────────┘

┌─────────────────────────────────┐
│           vehicles              │
│─────────────────────────────────│
│ id (PK)                         │
│ slug (UNIQUE)                   │
│ brand_id (FK)                   │
│ model_id (FK)                   │
│ year                            │
│ vin (UNIQUE, NULL)              │
│ mileage_km                      │
│ engine_cc                       │
│ engine_power_hp                 │
│ transmission ENUM               │
│ fuel_type ENUM                  │
│ drivetrain ENUM                 │
│ body_type_id (FK)               │
│ exterior_color                  │
│ interior_color                  │
│ doors                           │
│ seats                           │
│ origin_country                  │
│ location                        │
│ price_usd                       │
│ price_currency                  │
│ listing_type ENUM (sale|auction)│
│ status ENUM                     │
│ is_featured                     │
│ sold_at                         │
│ published_at                    │
│ created_by (FK→users)           │
│ created_at, updated_at          │
└─────┬────────────────┬──────────┘
      │                │
      │                │
┌─────▼──────────┐    ┌▼────────────────────────┐
│ vehicle_images │    │ vehicle_translations    │
│────────────────│    │─────────────────────────│
│ id (PK)        │    │ id (PK)                 │
│ vehicle_id(FK) │    │ vehicle_id (FK)         │
│ path           │    │ locale                  │
│ alt_ar         │    │ title                   │
│ alt_fr         │    │ description             │
│ alt_en         │    │ meta_title              │
│ width, height  │    │ meta_description        │
│ size_bytes     │    │ UNIQUE(vehicle_id,loc)  │
│ is_cover       │    └─────────────────────────┘
│ sort_order     │
│ created_at     │    ┌─────────────────────────┐
└────────────────┘    │   vehicle_videos        │
                      │─────────────────────────│
                      │ id (PK)                 │
                      │ vehicle_id (FK)         │
                      │ path | external_url     │
                      │ provider ENUM           │
                      │ poster_path             │
                      │ sort_order              │
                      └─────────────────────────┘

┌─────────────────────────────────┐
│      inspection_reports         │
│─────────────────────────────────│
│ id (PK)                         │
│ vehicle_id (FK, UNIQUE)         │
│ overall_score (0-100)           │
│ engine_score                    │
│ exterior_score                  │
│ interior_score                  │
│ tires_score                     │
│ brakes_score                    │
│ electrical_score                │
│ accident_history ENUM           │
│ inspector_name                  │
│ inspected_at                    │
│ report_pdf_path                 │
│ notes_ar, notes_fr, notes_en    │
│ created_at, updated_at          │
└─────────────────────────────────┘

┌──────────────┐   ┌──────────────┐   ┌──────────────┐
│   brands     │   │    models    │   │  body_types  │
│──────────────│   │──────────────│   │──────────────│
│ id (PK)      │◄──│ brand_id(FK) │   │ id (PK)      │
│ slug         │   │ slug         │   │ key (UNIQUE) │
│ name         │   │ name         │   │ name_ar,fr,en│
│ logo_path    │   │ created_at   │   └──────────────┘
│ country      │   └──────────────┘
│ sort_order   │
└──────────────┘

┌──────────────────────────┐    ┌──────────────────────────────┐
│      testimonials        │    │   testimonial_translations   │
│──────────────────────────│    │──────────────────────────────│
│ id (PK)                  │◄───│ id (PK)                      │
│ customer_name            │    │ testimonial_id (FK)          │
│ customer_city            │    │ locale                       │
│ avatar_path              │    │ body                         │
│ rating (1-5)             │    │ UNIQUE(testimonial_id,locale)│
│ vehicle_purchased        │    └──────────────────────────────┘
│ is_published             │
│ sort_order               │
│ created_at               │
└──────────────────────────┘

┌──────────────────────┐    ┌──────────────────────────┐
│       pages          │    │    page_translations     │
│──────────────────────│    │──────────────────────────│
│ id (PK)              │◄───│ id (PK)                  │
│ key (UNIQUE)         │    │ page_id (FK)             │
│ template             │    │ locale                   │
│ is_published         │    │ title, body              │
│ updated_at           │    │ meta_title, meta_desc    │
└──────────────────────┘    └──────────────────────────┘

┌─────────────────┐    ┌────────────────────┐    ┌────────────────────┐
│   settings      │    │   translations     │    │   audit_logs       │
│─────────────────│    │────────────────────│    │────────────────────│
│ key (PK)        │    │ id (PK)            │    │ id (PK)            │
│ value (LONGTEXT)│    │ namespace          │    │ user_id (FK)       │
│ type            │    │ key                │    │ action             │
│ is_public       │    │ locale             │    │ entity             │
│ updated_at      │    │ value              │    │ entity_id          │
└─────────────────┘    │ UNIQUE(ns,key,loc) │    │ payload (JSON)     │
                       │ updated_at         │    │ ip                 │
                       └────────────────────┘    │ created_at         │
                                                 └────────────────────┘

┌──────────────────────────┐
│  whatsapp_click_events   │   (lightweight analytics)
│──────────────────────────│
│ id (PK)                  │
│ vehicle_id (FK, NULL)    │
│ locale                   │
│ ip_hash                  │
│ user_agent_hash          │
│ created_at               │
└──────────────────────────┘

┌──────────────────────────┐
│   login_throttle         │
│──────────────────────────│
│ id (PK)                  │
│ key (IP+username hash)   │
│ attempts                 │
│ locked_until             │
│ updated_at               │
└──────────────────────────┘
```

## 2. Reserved (Future) Tables

These are NOT created in v1 but the schema reserves their names and keeps `vehicles.listing_type` aware of them so we don't paint ourselves into a corner.

- `auctions`, `bids`, `auction_winners`
- `customers` (extends `users.role=customer`)
- `wishlists`, `vehicle_comparisons`
- `payments`, `payment_transactions`
- `shipments`, `shipment_events`
- `api_tokens`

## 3. Relationships at a Glance

- `users` 1—N `leads` (via `assigned_to`)
- `users` 1—N `audit_logs`
- `brands` 1—N `models`
- `brands` 1—N `vehicles`
- `models` 1—N `vehicles`
- `body_types` 1—N `vehicles`
- `vehicles` 1—N `vehicle_images`
- `vehicles` 1—N `vehicle_videos`
- `vehicles` 1—1 `inspection_reports`
- `vehicles` 1—N `vehicle_translations`
- `vehicles` 1—N `leads` (a lead can be tied to a specific vehicle, or be general)
- `leads` 1—N `lead_notes`
- `testimonials` 1—N `testimonial_translations`
- `pages` 1—N `page_translations`

## 4. Indexing Strategy

Listed in `03-database-schema.sql`. Highlights:

- `vehicles` filterable cols all indexed: `(status, is_featured)`, `(brand_id, model_id)`, `year`, `price_usd`, `mileage_km`, `fuel_type`, `transmission`.
- Compound index `(status, published_at DESC)` for the public listing page default sort.
- FULLTEXT index on `vehicle_translations(title, description)` for search.
- `leads(status, created_at DESC)` for admin inbox.
- `leads(vehicle_id, created_at DESC)` for per-vehicle lead history.
- `settings(key)` is PK.
- `translations(namespace, key, locale)` UNIQUE composite.

## 5. ENUMs

```
users.role                      = ('admin','staff','customer')   -- customer reserved
vehicles.transmission           = ('manual','automatic','dct','cvt')
vehicles.fuel_type              = ('petrol','diesel','hybrid','phev','electric','lpg')
vehicles.drivetrain             = ('fwd','rwd','awd','4wd')
vehicles.listing_type           = ('sale','auction')             -- auction reserved
vehicles.status                 = ('draft','available','reserved','sold','archived')
inspection_reports.accident_history = ('none','minor','major','unknown')
leads.lead_type                 = ('inquiry','quotation','reservation','whatsapp')
leads.status                    = ('new','contacted','qualified','negotiating','won','lost')
leads.source                    = ('vehicle_page','listing','homepage','contact','direct')
vehicle_videos.provider         = ('local','youtube','vimeo')
settings.type                   = ('string','int','float','bool','json')
```

## 6. Data Volume Assumptions

| Table                  | Year-1 estimate | Year-3 estimate |
|------------------------|-----------------|-----------------|
| vehicles               | 200–500         | 2k–5k           |
| vehicle_images         | 5k              | 50k             |
| leads                  | 2k              | 30k             |
| audit_logs             | 10k             | 200k            |
| whatsapp_click_events  | 20k             | 500k            |

All within comfortable MySQL InnoDB territory on a single box.

## 7. Soft Delete Strategy

V1 uses **hard deletes** for vehicles and images (admin must confirm) but **archive** is the recommended path (`status='archived'`) so SEO history is preserved. We do not add a `deleted_at` column in v1; if needed in v2 the migration is trivial.
