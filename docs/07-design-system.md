# 07 — Design System

Premium, trustworthy, Korean-tech-brand feel. Restrained palette, generous whitespace, strong typography.

---

## 1. Brand DNA

- **Premium** — minimal ornament, large imagery, strict alignment.
- **Trustworthy** — Korean-red accents used sparingly; deep navy as anchor; high-contrast text.
- **Modern** — sans-serif everywhere, no skeuomorphism, soft 12 px radii.
- **Fast** — design that survives slow networks (small images, no heavy hero videos by default).

## 2. Color Palette

| Role            | Token              | Hex       | Use                                        |
|-----------------|--------------------|-----------|--------------------------------------------|
| Brand primary   | `--c-navy`         | `#0A1A2F` | Header, footer, primary buttons (dark)     |
| Brand primary 2 | `--c-navy-700`     | `#142B4A` | Hover, deeper surfaces                     |
| Accent          | `--c-red`          | `#CD2E3A` | Primary CTAs, badges, key highlights       |
| Accent hover    | `--c-red-700`      | `#A6212B` | Button hover                               |
| Surface         | `--c-bg`           | `#FFFFFF` | Page background                            |
| Surface alt     | `--c-bg-soft`      | `#F6F8FB` | Sections, cards                            |
| Border          | `--c-border`       | `#E3E7EE` | Card borders, dividers                     |
| Text default    | `--c-text`         | `#0E1726` | Body text                                  |
| Text muted      | `--c-text-muted`   | `#5B6877` | Captions, labels                           |
| Success         | `--c-success`      | `#1B8A5A` | Inspection scores 80+, "Available" badge   |
| Warning         | `--c-warning`      | `#C77A07` | Inspection scores 60–79                    |
| Danger          | `--c-danger`       | `#C0392B` | Errors, inspection scores <60              |
| Info            | `--c-info`         | `#2E5DAA` | Informational banners                      |

Contrast: every text/background pair meets WCAG AA (≥ 4.5:1 for body, 3:1 for large).

## 3. Typography

| Locale | Body / UI font                  | Display font     | Notes                          |
|--------|----------------------------------|------------------|--------------------------------|
| ar     | IBM Plex Sans Arabic             | IBM Plex Sans Arabic (700/800) | self-hosted woff2 |
| fr/en  | Inter                            | Inter (700/800)  | self-hosted woff2              |

Type scale (mobile / desktop):

| Token       | rem      | mobile px | desktop px | weight |
|-------------|----------|-----------|------------|--------|
| `--fs-xs`   | 0.75     | 12        | 12         | 400/500|
| `--fs-sm`   | 0.875    | 14        | 14         | 400/500|
| `--fs-base` | 1        | 16        | 16         | 400/500|
| `--fs-lg`   | 1.125    | 18        | 18         | 500/600|
| `--fs-xl`   | 1.25     | 20        | 22         | 600    |
| `--fs-2xl`  | 1.5      | 24        | 28         | 700    |
| `--fs-3xl`  | 2        | 32        | 36         | 700/800|
| `--fs-4xl`  | 2.5      | 40        | 56         | 800    |
| `--fs-hero` | 3        | 36        | 64         | 800    |

Line-height: 1.5 for body, 1.15 for display. Letter-spacing: 0 default, −0.02em for display sizes.

## 4. Spacing Scale

4 px baseline grid.

| Token   | px |
|---------|----|
| `--s-0` | 0  |
| `--s-1` | 4  |
| `--s-2` | 8  |
| `--s-3` | 12 |
| `--s-4` | 16 |
| `--s-5` | 24 |
| `--s-6` | 32 |
| `--s-7` | 48 |
| `--s-8` | 64 |
| `--s-9` | 96 |

Section vertical rhythm: 64 px mobile / 96 px desktop between major sections.

## 5. Radius, Elevation, Motion

- **Radii**: `--r-sm 6px` (inputs), `--r-md 12px` (cards, buttons), `--r-lg 20px` (hero card, modals), `--r-full` (pills).
- **Elevation**:
  - `--shadow-1`: `0 1px 2px rgb(10 26 47 / 0.06)` — cards default
  - `--shadow-2`: `0 4px 12px rgb(10 26 47 / 0.08)` — card hover, dropdowns
  - `--shadow-3`: `0 12px 32px rgb(10 26 47 / 0.14)` — modals, lightbox
- **Motion**:
  - Durations: `--t-fast 120ms`, `--t-base 200ms`, `--t-slow 320ms`
  - Easing: `--e-out cubic-bezier(.2,.8,.2,1)`, `--e-in-out cubic-bezier(.4,0,.2,1)`
  - Respect `prefers-reduced-motion`

## 6. Grid & Breakpoints

Standard Bootstrap 5 breakpoints:

| Token | min-width |
|-------|-----------|
| xs    | 0         |
| sm    | 576       |
| md    | 768       |
| lg    | 992       |
| xl    | 1200      |
| xxl   | 1400      |

Container max: 1320 px. Mobile-first: design at 360 px first, then enhance.

## 7. Component Specs

### Buttons

| Variant     | Use                  | Background     | Text         | Border |
|-------------|----------------------|----------------|--------------|--------|
| `.btn-primary` | Main CTA (Browse, Submit) | `--c-red`   | `#fff`       | none   |
| `.btn-dark`    | Secondary CTA      | `--c-navy`     | `#fff`       | none   |
| `.btn-outline` | Tertiary           | transparent    | `--c-navy`   | `--c-navy` 1px |
| `.btn-ghost`   | Subtle inline      | transparent    | `--c-text-muted` | none |

- Height: 44 px (mobile), 48 px (desktop) — generous tap target.
- Min width: 120 px.
- Padding: 12px 24px.
- Radius: `--r-md`.
- Hover: lift + shadow-2, color shift to `-700` token.
- Disabled: 40% opacity, cursor not-allowed.
- Loading state: replace label with spinner, keep width.

### Vehicle Card

```
┌────────────────────────────────────┐
│                                    │
│  ▢ cover image (16:10, lazy)       │
│                                    │
│  ★ Featured (chip, top-left)       │
├────────────────────────────────────┤
│  Hyundai Tucson 2022               │ ← 18px / 600
│  Diesel · Automatic · 35,420 km    │ ← 13px / muted
│                                    │
│  $19,500          📍 Busan         │ ← 20px / 700  ·  12px muted
│                                    │
│  [ View vehicle → ]                │ ← btn-outline full width
└────────────────────────────────────┘
```

- Background `--c-bg`, border `--c-border`, radius `--r-md`, shadow-1.
- Hover: shadow-2 + 2px upward translate (200ms).
- Image: object-cover, aspect-ratio 16/10, lazy + decoding async.
- Click target = entire card.

### Hero

- Full-width media (image or short loop), max height 80vh desktop, 60vh mobile.
- Overlay: linear-gradient(180deg, transparent 0%, rgba(10,26,47,0.55) 100%).
- Headline: white, `--fs-hero`, 800 weight, max-width 800 px.
- Subheadline: white-80, `--fs-lg`.
- CTAs side-by-side desktop, stacked mobile (12 px gap).

### Forms

- Labels above inputs, `--fs-sm`, weight 500, color `--c-text`.
- Inputs: 44–48 px height, border `--c-border` 1px, radius `--r-sm`, focus ring `--c-info` 3px outline.
- Error: red border + red helper text below, `aria-invalid="true"`.
- Required asterisk in `--c-red`.
- Disabled: bg `--c-bg-soft`, text muted.

### Modals (Quote / Reserve)

- Width: 480 px desktop, full-screen mobile.
- Backdrop: `rgba(10,26,47,0.55)` + blur 4px.
- Radius `--r-lg`, shadow-3.
- Top close button (top-end, 12px from edge).
- Sticky footer with primary + cancel CTA.

### Inspection Score Bar

```
Engine          ●●●●●●●●●●  98
Exterior        ●●●●●●●●●○  90
```

- Filled circle = solid `--c-success` if ≥80, `--c-warning` if 60–79, `--c-danger` if <60.
- Empty circle = `--c-border`.
- Numeric value `--fs-sm`, weight 700.
- Use `<progress>` semantically + aria-label.

### Sticky Lead Bar (mobile)

- Position: fixed bottom, `env(safe-area-inset-bottom)`.
- 64 px height, 3 buttons side-by-side, equal flex.
- Background `--c-navy`, text white, divider lines `rgba(255,255,255,0.1)`.
- Icons only on narrow widths, icons + label on ≥360 px.

## 8. Iconography

- Lucide icons (open source, MIT) inlined as SVG, 24 × 24 default.
- Stroke 1.75 px, currentColor.
- For RTL: do NOT auto-flip generic icons; explicitly flip directional arrows (`arrow-right` ↔ `arrow-left`).

## 9. Imagery Guidelines

- Vehicle photos: clean background (white/grey studio), 3:2 or 16:10 crop.
- Avoid heavy logos / overlays on photos.
- Hero: cinematic, low-saturation, premium feel.
- WebP primary, JPEG fallback.
- All decorative images: `alt=""`.

## 10. Logo

Placeholder until brand defines. SVG wordmark + monogram. Light + dark variants. Min size 24 px height.

## 11. Voice & Tone

- **Headlines**: direct, confident — "Verified Korean cars, delivered to your door."
- **Body**: clear, factual, short sentences. No marketing fluff.
- **CTAs**: action verbs — "Browse cars", "Get a quote", "WhatsApp now".
- **Trust language**: numbers and specifics — "Inspected by our Busan team", "92/100 condition score".
- **Translations**: translated by native speakers, not auto. Vehicle titles localized.

## 12. RTL Specifics

- Use logical CSS properties when writing custom CSS: `margin-inline-start`, `padding-inline-end`, `text-align: start`.
- Bootstrap 5 RTL build loaded when `dir="rtl"`.
- Numerals: Latin (Western) numerals across all locales — Algerian preference.
- Currency formatting:
  - AR: `19,500 $` / `2,632,500 د.ج`
  - FR: `19 500 $` / `2 632 500 DA`
  - EN: `$19,500` / `2,632,500 DA`
- Date formatting per locale (`intlfmt` helper, fallback to PHP `IntlDateFormatter`).

## 13. Accessibility Targets

- All interactive elements keyboard-reachable, visible focus ring.
- Color contrast AA minimum (AAA for body text where possible).
- Form errors associated via `aria-describedby`.
- Modals: focus-trap, ESC closes, return focus to invoker.
- Image carousels: pause on hover/focus, screen-reader live region announces slide.
- Skip-to-content link at top of every page.
- No information conveyed by color alone (scores have number + bar + label).

## 14. Asset Pipeline (v1)

- CSS: hand-written, organized as `assets/css/{tokens,base,components,utilities,rtl-overrides}.css`. Loaded as separate files in dev, concatenated + minified for prod via a small Node script (Phase 4).
- JS: ES modules, `assets/js/{main,gallery,filter,modal,wa-tracker}.js`. Loaded with `type="module"` and `defer`.
- Bootstrap 5: only the components we use (grid, dropdown, modal, collapse, carousel) — purge in Phase 4.
- Fonts: `font-display: swap`, woff2 only, preloaded for primary weights.

## 15. CSS Token Implementation Sketch

```css
:root {
  --c-navy: #0A1A2F;
  --c-navy-700: #142B4A;
  --c-red: #CD2E3A;
  --c-red-700: #A6212B;
  --c-bg: #FFFFFF;
  --c-bg-soft: #F6F8FB;
  --c-border: #E3E7EE;
  --c-text: #0E1726;
  --c-text-muted: #5B6877;
  --c-success: #1B8A5A;
  --c-warning: #C77A07;
  --c-danger: #C0392B;
  --c-info: #2E5DAA;

  --fs-base: 1rem;     --fs-lg: 1.125rem;  --fs-xl: 1.25rem;
  --fs-2xl: 1.5rem;    --fs-3xl: 2rem;     --fs-hero: clamp(2.25rem, 4vw + 1rem, 4rem);

  --s-1: 4px;  --s-2: 8px;  --s-3: 12px;  --s-4: 16px;
  --s-5: 24px; --s-6: 32px; --s-7: 48px;  --s-8: 64px;

  --r-sm: 6px; --r-md: 12px; --r-lg: 20px;

  --shadow-1: 0 1px 2px rgb(10 26 47 / 0.06);
  --shadow-2: 0 4px 12px rgb(10 26 47 / 0.08);
  --shadow-3: 0 12px 32px rgb(10 26 47 / 0.14);

  --t-fast: 120ms; --t-base: 200ms; --t-slow: 320ms;
  --e-out: cubic-bezier(.2,.8,.2,1);
}
```

This token set will drive every component in Phase 2.
