<?php
/**
 * @var \App\Core\View $this
 * @var string $tagline
 * @var array  $featured
 * @var array  $testimonials
 * @var array  $brands
 * @var string $wa_link
 */
$this->extends('layouts/public');
$why_reasons   = t_arr('pages.why_korea.reasons');
$process_steps = t_arr('pages.process.steps');
$faq_items     = t_arr('home.faq.items');
$stats         = t_arr('home.stats.items');
$vs_korea      = t_arr('home.vs.korea');
$vs_local      = t_arr('home.vs.local');
?>
<?php $this->section('content'); ?>

<!-- ============================================================
     HERO — cinematic dark, decorative geometry, 3 CTAs, scroll cue
     ============================================================ -->
<section class="kae-hero" aria-labelledby="kae-hero-title">

    <!-- Decorative perspective grid (pure CSS+SVG, ~1KB) -->
    <div class="kae-hero-decor" aria-hidden="true">
        <svg viewBox="0 0 1440 800" preserveAspectRatio="xMidYMax slice" focusable="false">
            <defs>
                <linearGradient id="kae-hero-grad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%"   stop-color="#0066FF" stop-opacity="0"/>
                    <stop offset="100%" stop-color="#0066FF" stop-opacity="0.55"/>
                </linearGradient>
                <linearGradient id="kae-hero-grad-2" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0"/>
                    <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0.18"/>
                </linearGradient>
            </defs>
            <!-- vanishing-point road lines suggesting motion / import journey -->
            <g stroke="url(#kae-hero-grad-2)" stroke-width="1" fill="none" opacity="0.55">
                <path d="M -400 800 L 720 320"/>
                <path d="M -200 800 L 720 320"/>
                <path d="M    0 800 L 720 320"/>
                <path d="M  200 800 L 720 320"/>
                <path d="M  400 800 L 720 320"/>
                <path d="M  600 800 L 720 320"/>
                <path d="M  720 800 L 720 320"/>
                <path d="M  840 800 L 720 320"/>
                <path d="M 1040 800 L 720 320"/>
                <path d="M 1240 800 L 720 320"/>
                <path d="M 1440 800 L 720 320"/>
                <path d="M 1640 800 L 720 320"/>
                <path d="M 1840 800 L 720 320"/>
            </g>
            <!-- horizon glow -->
            <ellipse cx="720" cy="320" rx="380" ry="70" fill="url(#kae-hero-grad)" opacity="0.55"/>
        </svg>
        <div class="kae-hero-vignette"></div>
    </div>

    <div class="container kae-hero-content">
        <span class="kae-eyebrow" data-reveal data-reveal-delay="0">
            <?= e(t('home.hero.eyebrow')) ?>
        </span>

        <h1 id="kae-hero-title" class="kae-hero-title" data-reveal data-reveal-delay="120">
            <?= e(t('home.hero.headline')) ?>
        </h1>

        <p class="kae-hero-subtitle" data-reveal data-reveal-delay="240">
            <?= e(t('home.hero.subheadline')) ?>
        </p>

        <div class="kae-hero-cta-group" data-reveal data-reveal-delay="360">
            <a href="<?= e(locale_url('/vehicles')) ?>" class="btn btn-primary btn-lg">
                <?= e(t('home.hero.cta_browse')) ?>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="margin-inline-start: 6px;"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
            <a href="<?= e(locale_url('/request-vehicle')) ?>" class="btn btn-outline-light btn-lg">
                <?= e(t('home.hero.cta_quote')) ?>
            </a>
            <a href="<?= e($wa_link) ?>" target="_blank" rel="noopener" class="btn btn-success btn-lg kae-hero-wa" data-track-wa="hero">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="margin-inline-end: 6px;"><path d="M20.5 3.5A11.4 11.4 0 0 0 12.3 0C5.9 0 .7 5.2.7 11.6c0 2.1.6 4.1 1.6 5.9L.6 24l6.7-1.8a11.6 11.6 0 0 0 5 1.2h.1c6.4 0 11.6-5.2 11.6-11.6 0-3.1-1.2-6-3.4-8.3zM12.3 21.4c-1.7 0-3.3-.4-4.7-1.3l-.3-.2-3.5.9.9-3.5-.2-.4a9.6 9.6 0 0 1-1.5-5.2C3 6.5 7.2 2.3 12.3 2.3c2.5 0 4.8 1 6.5 2.7a9.3 9.3 0 0 1 2.7 6.6c0 5.1-4.1 9.3-9.2 9.3zm5-7c-.3-.1-1.7-.8-1.9-.9-.3-.1-.4-.1-.6.1-.2.3-.7.9-.8 1.1-.2.2-.3.2-.6.1-.3-.1-1.2-.4-2.3-1.4-.9-.8-1.5-1.7-1.6-2-.2-.3 0-.5.1-.6.1-.1.3-.3.4-.5l.3-.4c.1-.2.1-.3 0-.5-.1-.1-.6-1.5-.8-2-.2-.5-.4-.5-.6-.5h-.5c-.2 0-.5.1-.7.3-.3.3-1 1-1 2.3 0 1.4 1 2.7 1.2 2.9.1.2 2 3.1 4.9 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.6-.1 1.7-.7 1.9-1.3.2-.7.2-1.3.2-1.4-.1-.1-.3-.2-.6-.3z"/></svg>
                <?= e(t('home.hero.cta_whatsapp')) ?>
            </a>
        </div>

        <!-- Trust strip -->
        <ul class="kae-hero-trust" data-reveal data-reveal-delay="500" aria-label="<?= e(t('home.hero.eyebrow')) ?>">
            <li>
                <span class="kae-trust-dot"></span>
                <?= e(t('home.hero.trust_inspected')) ?>
            </li>
            <li>
                <span class="kae-trust-dot"></span>
                <?= e(t('home.hero.trust_korea')) ?>
            </li>
            <li>
                <span class="kae-trust-dot"></span>
                <?= e(t('home.hero.trust_quote')) ?>
            </li>
        </ul>
    </div>

    <!-- Scroll cue -->
    <a href="#kae-featured" class="kae-hero-scroll" aria-label="<?= e(t('home.hero.scroll_cue')) ?>">
        <span class="kae-scroll-cue"></span>
        <span class="kae-hero-scroll-label"><?= e(t('home.hero.scroll_cue')) ?></span>
    </a>
</section>

<!-- ============================================================
     STATS STRIP — animated counters on dark band
     ============================================================ -->
<section id="kae-featured" class="kae-stats-strip">
    <div class="container">
        <div class="kae-stats">
            <?php foreach ($stats as $s):
                $raw = (string) ($s['value'] ?? '');
                // Extract leading digits for counter; keep prefix/suffix (e.g. "500+" → 500 / "+")
                preg_match('/^(\D*)(\d[\d\s,]*)(.*)$/', $raw, $m);
                $prefix = $m[1] ?? '';
                $digits = isset($m[2]) ? (int) preg_replace('/\D/', '', $m[2]) : 0;
                $suffix = $m[3] ?? '';
                ?>
                <div class="kae-stat" data-reveal>
                    <?php if ($digits > 0): ?>
                        <span class="kae-stat-value"
                              data-count="<?= (int) $digits ?>"
                              data-count-prefix="<?= e($prefix) ?>"
                              data-count-suffix="<?= e($suffix) ?>">
                            <?= e($prefix . '0' . $suffix) ?>
                        </span>
                    <?php else: ?>
                        <span class="kae-stat-value"><?= e($raw) ?></span>
                    <?php endif; ?>
                    <span class="kae-stat-label"><?= e((string) ($s['label'] ?? '')) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     FEATURED VEHICLES — luxury card grid with staggered reveal
     ============================================================ -->
<section class="kae-section">
    <div class="container">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-5">
            <div data-reveal>
                <span class="kae-eyebrow"><?= e(t('home.featured.eyebrow')) ?></span>
                <h2 class="kae-section-title"><?= e(t('home.featured.title')) ?></h2>
                <p class="kae-section-subtitle"><?= e(t('home.featured.subtitle')) ?></p>
            </div>
            <a href="<?= e(locale_url('/vehicles')) ?>" class="btn btn-outline-dark" data-reveal data-reveal-delay="100">
                <?= e(t('home.featured.view_all')) ?> →
            </a>
        </div>

        <?php if (empty($featured)): ?>
            <div class="kae-empty py-5 text-center">
                <p class="text-muted mb-0"><?= e(t('home.featured.empty')) ?></p>
            </div>
        <?php else: ?>
            <div class="row g-3 g-md-4">
                <?php foreach ($featured as $i => $vehicle): ?>
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3"
                         data-reveal data-reveal-delay="<?= ($i * 80) ?>">
                        <?= $this->partial('partials/vehicle-card', ['vehicle' => $vehicle]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================================
     WHY US — feature grid
     ============================================================ -->
<section class="kae-section kae-section--soft">
    <div class="container">
        <div class="text-center mb-5" data-reveal>
            <span class="kae-eyebrow"><?= e(t('home.why.eyebrow')) ?></span>
            <h2 class="kae-section-title"><?= e(t('home.why.title')) ?></h2>
            <p class="kae-section-subtitle mx-auto"><?= e(t('home.why.subtitle')) ?></p>
        </div>
        <div class="kae-feature-grid">
            <?php foreach (array_slice($why_reasons, 0, 4) as $i => $r): ?>
                <div class="kae-feature" data-reveal data-reveal-delay="<?= ($i * 100) ?>">
                    <div class="kae-feature-icon"><?= e((string) ($r['icon'] ?? '')) ?></div>
                    <h3 class="kae-feature-title"><?= e((string) ($r['title'] ?? '')) ?></h3>
                    <p class="kae-feature-body"><?= e((string) ($r['body'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     HOW IT WORKS — numbered step timeline
     ============================================================ -->
<section class="kae-section">
    <div class="container">
        <div class="text-center mb-5" data-reveal>
            <span class="kae-eyebrow"><?= e(t('home.process.eyebrow')) ?></span>
            <h2 class="kae-section-title"><?= e(t('home.process.title')) ?></h2>
            <p class="kae-section-subtitle mx-auto"><?= e(t('home.process.subtitle')) ?></p>
        </div>
        <div class="kae-step-grid">
            <?php foreach ($process_steps as $i => $s): ?>
                <div class="kae-step" data-reveal data-reveal-delay="<?= ($i * 100) ?>">
                    <div class="kae-step-num"><?= e((string) ($s['n'] ?? (string) ($i + 1))) ?></div>
                    <h3 class="kae-step-title"><?= e((string) ($s['title'] ?? '')) ?></h3>
                    <p class="kae-step-body"><?= e((string) ($s['body'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5" data-reveal>
            <a href="<?= e(locale_url('/import-process')) ?>" class="btn btn-outline-dark">
                <?= e(t('home.process.more')) ?> →
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     COMPARISON — Korea vs local market
     ============================================================ -->
<section class="kae-section kae-section--soft">
    <div class="container">
        <div class="text-center mb-5" data-reveal>
            <span class="kae-eyebrow"><?= e(t('home.vs.eyebrow')) ?></span>
            <h2 class="kae-section-title"><?= e(t('home.vs.title')) ?></h2>
            <p class="kae-section-subtitle mx-auto"><?= e(t('home.vs.subtitle')) ?></p>
        </div>
        <div class="kae-vs">
            <div class="kae-vs-card kae-vs-card--korea" data-reveal>
                <h3><?= e(t('home.vs.korea_title')) ?></h3>
                <ul class="kae-vs-list">
                    <?php foreach ($vs_korea as $item): ?>
                        <li><?= e((string) $item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="kae-vs-card kae-vs-card--local" data-reveal data-reveal-delay="150">
                <h3><?= e(t('home.vs.local_title')) ?></h3>
                <ul class="kae-vs-list">
                    <?php foreach ($vs_local as $item): ?>
                        <li><?= e((string) $item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="text-center mt-5" data-reveal>
            <a href="<?= e(locale_url('/why-korea')) ?>" class="btn btn-outline-dark">
                <?= e(t('home.why.more')) ?> →
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     TESTIMONIALS — CSS scroll-snap (no JS carousel needed)
     ============================================================ -->
<?php if (! empty($testimonials)): ?>
<section class="kae-section">
    <div class="container">
        <div class="text-center mb-5" data-reveal>
            <span class="kae-eyebrow"><?= e(t('home.testimonials.eyebrow')) ?></span>
            <h2 class="kae-section-title"><?= e(t('home.testimonials.title')) ?></h2>
            <p class="kae-section-subtitle mx-auto"><?= e(t('home.testimonials.subtitle')) ?></p>
        </div>
        <div class="kae-testimonials" role="list" data-reveal>
            <?php foreach ($testimonials as $tst): ?>
                <article class="kae-testimonial-card" role="listitem">
                    <div class="text-warning mb-2">
                        <?php for ($s = 0; $s < (int) ($tst['rating'] ?? 5); $s++) echo '★'; ?>
                    </div>
                    <p class="mb-3 lead"><?= e((string) ($tst['body'] ?? '')) ?></p>
                    <div class="fw-bold"><?= e((string) ($tst['customer_name'] ?? '')) ?></div>
                    <?php if (! empty($tst['customer_city'])): ?>
                        <div class="text-muted small"><?= e((string) $tst['customer_city']) ?></div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     FAQ — accordion
     ============================================================ -->
<section class="kae-section kae-section--soft">
    <div class="container">
        <div class="text-center mb-5">
            <span class="kae-eyebrow"><?= e(t('home.faq.eyebrow')) ?></span>
            <h2 class="kae-section-title"><?= e(t('home.faq.title')) ?></h2>
        </div>
        <div class="accordion col-md-9 mx-auto" id="kae-faq">
            <?php foreach ($faq_items as $i => $item): ?>
                <div class="accordion-item">
                    <h3 class="accordion-header" id="faq-h-<?= $i ?>">
                        <button class="accordion-button collapsed fw-semibold" type="button"
                                data-bs-toggle="collapse" data-bs-target="#faq-c-<?= $i ?>">
                            <?= e((string) ($item['q'] ?? '')) ?>
                        </button>
                    </h3>
                    <div id="faq-c-<?= $i ?>" class="accordion-collapse collapse"
                         data-bs-parent="#kae-faq">
                        <div class="accordion-body text-muted"><?= e((string) ($item['a'] ?? '')) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     FINAL CTA — full-bleed dark conversion band
     ============================================================ -->
<section class="kae-cta-band">
    <div class="container">
        <div class="kae-cta-band-inner">
            <span class="kae-eyebrow" data-reveal><?= e(t('home.final_cta.eyebrow')) ?></span>
            <h2 data-reveal data-reveal-delay="100"><?= e(t('home.final_cta.title')) ?></h2>
            <p data-reveal data-reveal-delay="200"><?= e(t('home.final_cta.subtitle')) ?></p>
            <div class="kae-cta-band-actions" data-reveal data-reveal-delay="300">
                <a href="<?= e($wa_link) ?>" target="_blank" rel="noopener" class="btn btn-success btn-lg" data-track-wa="cta-band">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="margin-inline-end: 6px;"><path d="M20.5 3.5A11.4 11.4 0 0 0 12.3 0C5.9 0 .7 5.2.7 11.6c0 2.1.6 4.1 1.6 5.9L.6 24l6.7-1.8a11.6 11.6 0 0 0 5 1.2h.1c6.4 0 11.6-5.2 11.6-11.6 0-3.1-1.2-6-3.4-8.3zM12.3 21.4c-1.7 0-3.3-.4-4.7-1.3l-.3-.2-3.5.9.9-3.5-.2-.4a9.6 9.6 0 0 1-1.5-5.2C3 6.5 7.2 2.3 12.3 2.3c2.5 0 4.8 1 6.5 2.7a9.3 9.3 0 0 1 2.7 6.6c0 5.1-4.1 9.3-9.2 9.3zm5-7c-.3-.1-1.7-.8-1.9-.9-.3-.1-.4-.1-.6.1-.2.3-.7.9-.8 1.1-.2.2-.3.2-.6.1-.3-.1-1.2-.4-2.3-1.4-.9-.8-1.5-1.7-1.6-2-.2-.3 0-.5.1-.6.1-.1.3-.3.4-.5l.3-.4c.1-.2.1-.3 0-.5-.1-.1-.6-1.5-.8-2-.2-.5-.4-.5-.6-.5h-.5c-.2 0-.5.1-.7.3-.3.3-1 1-1 2.3 0 1.4 1 2.7 1.2 2.9.1.2 2 3.1 4.9 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.6-.1 1.7-.7 1.9-1.3.2-.7.2-1.3.2-1.4-.1-.1-.3-.2-.6-.3z"/></svg>
                    <?= e(t('home.hero.cta_whatsapp')) ?>
                </a>
                <a href="<?= e(locale_url('/request-vehicle')) ?>" class="btn btn-primary btn-lg">
                    <?= e(t('home.final_cta.primary')) ?>
                </a>
            </div>
        </div>
    </div>
</section>

<?php $this->endSection(); ?>
