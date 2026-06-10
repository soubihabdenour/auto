<?php
/**
 * Shared dark page-hero band used across all secondary pages
 * (about, why-korea, import-process, contact, etc.).
 *
 * Smaller than the homepage / listing / vehicle-detail heroes
 * but visually consistent with the brand: dark band, blue eyebrow,
 * Manrope display title, optional subtitle, breadcrumb trail.
 *
 * @var \App\Core\View $this
 * @var string        $eyebrow
 * @var string        $title
 * @var string|null   $subtitle
 * @var array         $crumbs   list of [label => string, url => string|null]
 *                              The current page (last entry) should pass url=null.
 */
$eyebrow  = (string) ($eyebrow  ?? '');
$title    = (string) ($title    ?? '');
$subtitle = $subtitle ?? null;
$crumbs   = (array) ($crumbs ?? []);
?>
<section class="kae-page-hero" aria-label="page header">
    <div class="container">

        <?php if (! empty($crumbs)): ?>
            <nav aria-label="breadcrumb" class="kae-vd-crumbs mb-4">
                <?php foreach ($crumbs as $i => $c): ?>
                    <?php
                    $label = (string) ($c['label'] ?? '');
                    $url   = $c['url'] ?? null;
                    $last  = $i === count($crumbs) - 1;
                    ?>
                    <?php if ($url): ?>
                        <a href="<?= e($url) ?>"><?= e($label) ?></a>
                    <?php else: ?>
                        <span aria-current="page"><?= e($label) ?></span>
                    <?php endif; ?>
                    <?php if (! $last): ?><span aria-hidden="true">/</span><?php endif; ?>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <div class="kae-page-hero-text">
            <?php if ($eyebrow !== ''): ?>
                <span class="kae-eyebrow" data-reveal><?= e($eyebrow) ?></span>
            <?php endif; ?>
            <h1 class="kae-page-hero-title" data-reveal data-reveal-delay="100">
                <?= e($title) ?>
            </h1>
            <?php if ($subtitle !== null && $subtitle !== ''): ?>
                <p class="kae-page-hero-subtitle" data-reveal data-reveal-delay="200">
                    <?= e($subtitle) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>
