# Korea Auto Export

A modern, production-ready web platform that helps Algerian customers discover and buy used Korean cars imported from South Korea.

> **Status:** Phase 0 — Architecture & Design. No application code yet.

---

## 1. Vision

Korea Auto Export presents Korean vehicles to Algerian buyers in the most attractive and trustworthy way possible, with the goal of generating qualified leads (WhatsApp inquiries, quotation requests, reservations).

This is **not** a transactional marketplace in v1. Payments happen offline. The platform's job is to **build trust** and **convert visitors into leads**.

## 2. Target Audience

- Geographic: Algeria (primary), Maghreb diaspora (secondary)
- Languages: Arabic (RTL, primary), French, English
- Devices: mobile-first (most Algerian e-commerce traffic is mobile)
- Buying behavior: research-heavy, WhatsApp-driven, trust matters more than UX polish

## 3. Tech Stack

| Layer        | Choice                                |
| ------------ | ------------------------------------- |
| Language     | PHP 8.3                               |
| Database     | MySQL 8 (utf8mb4)                     |
| Frontend     | HTML5, CSS3, JS (vanilla), Bootstrap 5 RTL |
| Architecture | Custom MVC (no framework)             |
| Web server   | Apache 2.4 + mod_rewrite              |
| Caching      | Filesystem (v1), Redis-ready (v2)     |
| Images       | Local FS with `StorageInterface` (S3-swappable) |

## 4. Documentation Map

All architecture-phase documents live in `/docs`:

| # | Document                                             | Purpose                              |
|---|------------------------------------------------------|--------------------------------------|
| 1 | [01-architecture.md](docs/01-architecture.md)        | System architecture, MVC layout, security, i18n |
| 2 | [02-database-erd.md](docs/02-database-erd.md)        | Entity-Relationship Diagram          |
| 3 | [03-database-schema.sql](docs/03-database-schema.sql)| Full DDL with indexes, FKs, seed data|
| 4 | [04-wireframes.md](docs/04-wireframes.md)            | UI wireframes for every key page     |
| 5 | [05-page-flows.md](docs/05-page-flows.md)            | User journeys and admin flows        |
| 6 | [06-roadmap.md](docs/06-roadmap.md)                  | Phased delivery plan with estimates  |
| 7 | [07-design-system.md](docs/07-design-system.md)      | Colors, typography, spacing, components |

## 5. Next Steps

After sign-off on Phase 0, implementation proceeds in the order defined in `docs/06-roadmap.md`.
