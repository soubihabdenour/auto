# 08 — Brand Identity

The definitive book for how Korea Auto Export looks and reads. Everything here overrides what came before in `07-design-system.md`; the design system file documented the *raw token system*, this document fixes the *brand*.

---

## 1. Brand Essence

| Attribute     | Value                                                                |
|---------------|----------------------------------------------------------------------|
| Personality   | Premium · Trustworthy · Modern · Quiet confidence                    |
| Promise       | Korean cars, imported to Algeria with documented quality and honest pricing |
| Audience      | Algerian buyers — mobile-first, WhatsApp-driven, research-heavy       |
| Anti-pattern  | Loud · Pushy · Discount-banners · Generic stock-photo dealership      |

When a design decision is unclear, ask: _"would Genesis or Audi do it this way?"_. If yes, lean in; if no, simplify.

---

## 2. Logo System

### 2.1 Files

| File                                | Use                                                | Min size |
|-------------------------------------|----------------------------------------------------|----------|
| `assets/img/logo.svg`               | Primary horizontal lockup, on light backgrounds    | 140 px wide |
| `assets/img/logo-light.svg`         | Same lockup inverted for dark backgrounds          | 140 px wide |
| `assets/img/logo-stacked.svg`       | Vertical stacked variant for narrow / square use   | 80 px wide |
| `assets/img/logo-mark.svg`          | Monogram only (square), dark background variant   | 24 px square |
| `assets/img/logo-mark-light.svg`    | Monogram only, white background variant            | 24 px square |
| `assets/img/favicon.svg`            | 32×32 simplified mark                              | 16 px |
| `assets/img/og-default.svg`         | Default Open Graph / Twitter card                  | 1200×630 |

### 2.2 Construction

The monogram is a stylised **K** built from three elements:

1. **White vertical bar** — the spine
2. **White upper diagonal** — the rising stroke (forward motion, future)
3. **Korean-red lower diagonal** — the grounding stroke (origin, flag accent)

The bar/diagonal ratio is 5:36 (bar height to total height) — a deliberate vertical proportion that reads as confidence, not aggression. The mark sits inside a 14 px-radius rounded square: just enough softness to feel modern, sharp enough to feel automotive.

### 2.3 Wordmark Rules

| Element           | Spec                                                       |
|-------------------|------------------------------------------------------------|
| Wordmark line 1   | "KOREA AUTO" — Inter 800, letter-spacing 0.02em            |
| Wordmark line 2   | "EXPORT" — Inter 500, letter-spacing 0.32em, muted grey    |
| Clear space       | Equal to the **height of the K bar** on all four sides     |
| Forbidden uses    | Don't recolour the diagonal · don't outline the wordmark · don't drop-shadow · don't squeeze · don't rotate |
| Minimum mark size | 24 px square; below that, drop the wordmark, keep the mark |

### 2.4 When to use which lockup

- **Primary horizontal (`logo.svg`)** — desktop header, footer, invoices, email signatures
- **Horizontal light (`logo-light.svg`)** — dark hero, dark footer, dark UI
- **Stacked (`logo-stacked.svg`)** — mobile header centred, social profile, app icon contexts where horizontal won't fit
- **Mark only (`logo-mark*.svg`)** — favicons, social avatars, in-line bullet uses, watermarks
- **Favicon (`favicon.svg`)** — browser tab; simplified construction (no inner stroke) so it reads at 16 px

---

## 3. Colour Palette

### 3.1 Core

| Token             | Hex       | Purpose                                                   |
|-------------------|-----------|-----------------------------------------------------------|
| `--c-navy`        | `#0A1A2F` | Brand anchor. Header, footer, primary surfaces, headlines |
| `--c-navy-700`    | `#142B4A` | Hover-deepened navy, gradient endpoints                   |
| `--c-navy-900`    | `#06101D` | Inkiest navy — modal scrims, text on white at small sizes |
| `--c-red`         | `#CD2E3A` | Korean flag red. Primary CTAs, badges, key accents        |
| `--c-red-700`     | `#A6212B` | Hover state of red                                         |
| `--c-red-50`      | `#FBEAEC` | Red-tinted backgrounds (alerts, highlight strips)         |

### 3.2 Surfaces & Borders

| Token           | Hex       | Use                                          |
|-----------------|-----------|----------------------------------------------|
| `--c-bg`        | `#FFFFFF` | Page background                              |
| `--c-bg-soft`   | `#F6F8FB` | Section background, vehicle card fallback    |
| `--c-bg-tinted` | `#EEF2F7` | Sticky filter sidebar, table-striping        |
| `--c-border`    | `#E3E7EE` | Default border                                |
| `--c-border-strong` | `#CDD4DF` | Inputs, dividers needing weight            |

### 3.3 Text

| Token              | Hex       | Use                              |
|--------------------|-----------|----------------------------------|
| `--c-text`         | `#0E1726` | Body text, headlines on white    |
| `--c-text-muted`   | `#5B6877` | Captions, labels, secondary text |
| `--c-text-subtle`  | `#8B95A4` | Disabled labels, helpers         |

### 3.4 Semantic

| Token          | Hex       | Use                                            |
|----------------|-----------|------------------------------------------------|
| `--c-success`  | `#1B8A5A` | Available status, scores ≥ 80, valid           |
| `--c-warning`  | `#C77A07` | Reserved status, scores 60–79                  |
| `--c-danger`   | `#C0392B` | Sold status, scores <60, errors                |
| `--c-info`     | `#2E5DAA` | Informational banners, focus rings             |
| `--c-wa`       | `#25D366` | WhatsApp button only — never elsewhere         |

### 3.5 Contrast pairings

| Foreground       | Background    | WCAG  |
|------------------|---------------|-------|
| `--c-text` on `--c-bg` | white  | AAA (15.6:1) |
| `--c-text-muted` on `--c-bg` | white | AA (5.2:1) |
| `#FFFFFF` on `--c-red` | red    | AA (4.6:1) |
| `#FFFFFF` on `--c-navy`| navy   | AAA (17.0:1) |

---

## 4. Typography

### 4.1 Type families

| Locale     | Family                      | Source       | Weights loaded |
|------------|-----------------------------|--------------|----------------|
| AR         | **IBM Plex Sans Arabic**    | Google Fonts | 400, 500, 600, 700 |
| FR / EN    | **Inter**                   | Google Fonts | 400, 500, 600, 700, 800 |

Loading is locale-aware in `layouts/public.php`: an Arabic locale loads the IBM Plex stylesheet, Latin locales load Inter only. Latin numerals are used in every locale (Algerian convention).

### 4.2 Type scale

| Token       | rem      | Use                                      |
|-------------|----------|------------------------------------------|
| `--fs-xs`   | 0.75     | Eyebrows, badges, tabular small numbers  |
| `--fs-sm`   | 0.875    | Captions, labels, helper text            |
| `--fs-base` | 1        | Body                                     |
| `--fs-lg`   | 1.125    | Card titles, large body                  |
| `--fs-xl`   | 1.25     | Subheadings                              |
| `--fs-2xl`  | 1.5      | Section titles (mobile)                  |
| `--fs-3xl`  | 2        | Section titles (desktop)                 |
| `--fs-4xl`  | clamp(2.25, 3.5vw + 1rem, 3.5) | Page hero h1   |
| `--fs-hero` | clamp(2.5, 4.5vw + 1rem, 4.5)  | Homepage hero h1 |

### 4.3 Type rules

- **Line-height** 1.5 body, 1.15 display
- **Letter-spacing** −0.02em for display sizes, 0 for body, +0.30em for tracked labels
- **Tabular numerals**: prices, mileages, dates use `font-variant-numeric: tabular-nums` so numbers in tables and cards align cleanly
- **Headings**: never centre-align long-form text; centre is reserved for hero, section-titles, and short calls-to-action
- **Italics**: avoided in AR (typographic awkwardness); use weight or colour for emphasis instead

---

## 5. Spacing, Radius, Elevation

Unchanged from `07-design-system.md` — see that doc for the raw token list.

Production overrides:
- Section vertical rhythm tightened on mobile: **48px** between major sections; **96px** desktop
- Card radius standardised at `--r-md` (12px); modals at `--r-lg` (20px); pill buttons use `--r-full`
- Three elevation tiers only: subtle (cards), raised (hover, dropdowns), floating (modals, lightbox). Don't invent more.

---

## 6. Motion

- **Durations** — `--t-fast 120ms`, `--t-base 200ms`, `--t-slow 320ms`
- **Easings** — `--e-out cubic-bezier(.2,.8,.2,1)` for entry, `--e-in-out cubic-bezier(.4,0,.2,1)` for bidirectional
- **Card hover** — 2px upward translate + shadow ramp in 200ms ease-out
- **Inspection-bar fill** — 400ms ease-out on first render
- **Page transitions** — none. Don't add SPA-feel animations to a server-rendered site; it just makes it feel slow.
- Always honour `@media (prefers-reduced-motion: reduce)` — every transition shorter than 200ms is OK; longer animations are disabled.

---

## 7. Imagery

### 7.1 Vehicle photography
- Clean studio background (white or light grey), no logos / dealer plates / mannequins
- 3:2 or 16:10 crop; **landscape only** for cards, **vertical hero shot** allowed only for the gallery main image
- Strong even lighting; no HDR over-processing
- Minimum 1600 px wide for hero, 800 px wide for cards
- Filenames pattern: `{vin_short}-{role}-{n}.webp` e.g. `7A3F-cover-1.webp`

### 7.2 Lifestyle / editorial
- Cinematic, low-saturation
- People shown from behind or partially (avoid model-release issues)
- Algerian context preferred for trust pages (skyline, port, road) — Korean context for Why-Korea page

### 7.3 Illustrations
- Geometric, single-weight strokes
- Only the brand palette
- Never combine illustrations with photography in the same card

---

## 8. Voice & Tone

### 8.1 Voice (always true)
- Direct
- Specific (numbers, dates, processes)
- Calm, no hard-sell

### 8.2 Tone (varies by context)

| Context         | Tone shift                                                  |
|-----------------|-------------------------------------------------------------|
| Homepage hero   | Confident, aspirational                                     |
| Vehicle detail  | Factual, evidence-led                                       |
| Trust pages     | Warm, educational                                           |
| Forms           | Concise — labels and helpers only, no marketing copy        |
| Errors          | Apologetic without grovelling; offer a way forward          |
| Success         | Reassuring, give the next concrete action                   |
| WhatsApp prefill| Friendly, first-person, locale-native phrasing              |

### 8.3 Headline patterns
- Action verb first ("Import…", "Browse…", "Tell us…")
- Use "you" not "the customer"
- Never use exclamation marks in body copy; reserved for confirmation messages once per page

### 8.4 Microcopy bank

| Trigger              | AR                          | FR                                    | EN                              |
|----------------------|-----------------------------|---------------------------------------|---------------------------------|
| New lead success     | تم استلام طلبك              | Demande reçue                         | Request received                |
| Empty filter         | لا توجد سيارات              | Aucun véhicule                        | No vehicles match               |
| 404                  | الصفحة غير موجودة          | Page introuvable                      | Page not found                  |
| WhatsApp pre-fill   | مرحبا، أنا مهتم بـ:        | Bonjour, je suis intéressé par :      | Hello, I'm interested in:       |

---

## 9. Visual Patterns

### 9.1 Hero
Dark navy gradient (`--c-navy` → `--c-navy-700`) with a low-opacity Korean-red glow in one corner and a faint diagonal pinstripe pattern. Never use stock photography in the hero.

### 9.2 Vehicle card
White surface, soft border, 16:10 cover image, lift on hover. Price in red. Featured badge in red top-start. Status badge follows colour semantics.

### 9.3 Trust strip
Single horizontal row of checkmarks, muted text, no icons larger than 16 px. Always five items, evenly spaced.

### 9.4 Sticky CTA bar (mobile, detail page)
Navy background, three equal-width buttons: WhatsApp (green), Quote (red), Reserve (translucent white). Always 64 px tall, always respects `env(safe-area-inset-bottom)`.

### 9.5 Inspection bars
Horizontal bar with a fill that animates in. Colour ladder: green ≥ 80, amber 60–79, red < 60. Numeric value always displayed beside the bar — colour is supplementary, never sole information.

---

## 10. Brand Hierarchy on the Page

The eye should travel: **mark → headline → CTA → proof**. Anything competing with this priority is wrong.

On a vehicle detail page that means:
1. **Top** — header with mark and nav
2. **Above-the-fold** — gallery (proof of condition) + price + WhatsApp/Quote/Reserve CTAs
3. **Below-the-fold** — tabs with specs / inspection / cost
4. **Bottom** — similar vehicles, then sticky CTA bar reasserts the priority on mobile

---

## 11. What's NOT in this brand

- ❌ Discount banners ("50% OFF! HURRY!")
- ❌ Countdown timers
- ❌ Pop-up modals on page load
- ❌ Generic stock photos
- ❌ Multiple typefaces beyond the two listed
- ❌ Gradients in the colour palette beyond the navy hero gradient
- ❌ Emoji as functional icons (decorative only, sparingly)
- ❌ Auto-playing video with sound

Trust is built by what we refuse to do as much as by what we ship.
