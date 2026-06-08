<?php
/**
 * @var \App\Core\View $this
 * @var string         $locale
 * @var string         $dir
 * @var array          $available_locales
 * @var string|null    $page_title
 * @var string|null    $meta_desc
 */
$siteUrl  = rtrim((string) config('app.url', ''), '/');
$ogImage  = $siteUrl . '/assets/img/og-default.svg';
$isArabic = $locale === 'ar';

// hreflang + canonical: strip the leading "/{locale}" from the current path
$rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$stripped = (string) preg_replace(
    '#^/(' . implode('|', $available_locales) . ')(/|$)#',
    '/',
    $rawPath
);
$stripped = $stripped === '' ? '/' : $stripped;
$canonical = $siteUrl . '/' . $locale . ($stripped === '/' ? '' : $stripped);

$metaDesc = $meta_desc ?? null;
if ($metaDesc === null || $metaDesc === '') {
    $metaDesc = t('home.hero.subheadline');
}

try {
    $orgSchema = app(\App\Services\Seo\OrganizationSchemaBuilder::class)->build();
} catch (\Throwable) {
    $orgSchema = null;
}
?><!DOCTYPE html>
<html lang="<?= e($locale) ?>" dir="<?= e($dir) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0A1A2F">
    <title><?= e($page_title ?? t('common.brand.name')) ?></title>
    <meta name="description" content="<?= e((string) $metaDesc) ?>">

    <link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg') ?>">
    <link rel="apple-touch-icon" href="<?= asset('img/favicon.svg') ?>">

    <!-- Canonical + hreflang alternates -->
    <link rel="canonical" href="<?= e($canonical) ?>">
    <?php foreach ($available_locales as $altLoc): ?>
        <link rel="alternate" hreflang="<?= e($altLoc) ?>"
              href="<?= e($siteUrl . '/' . $altLoc . ($stripped === '/' ? '' : $stripped)) ?>">
    <?php endforeach; ?>
    <link rel="alternate" hreflang="x-default"
          href="<?= e($siteUrl . '/' . ($available_locales[0] ?? 'ar') . ($stripped === '/' ? '' : $stripped)) ?>">

    <!-- Open Graph / Twitter -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($page_title ?? t('common.brand.name')) ?>">
    <meta property="og:description" content="<?= e((string) $metaDesc) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:site_name" content="<?= e(t('common.brand.name')) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:locale" content="<?= e($locale === 'ar' ? 'ar_DZ' : ($locale === 'fr' ? 'fr_DZ' : 'en_US')) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($page_title ?? t('common.brand.name')) ?>">
    <meta name="twitter:description" content="<?= e((string) $metaDesc) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">

    <!-- Self-hosted fonts -->
    <link rel="preload" as="font" type="font/woff2" crossorigin
          href="<?= asset('fonts/inter/Inter-Latin.woff2') ?>">
    <?php if ($isArabic): ?>
        <link rel="preload" as="font" type="font/woff2" crossorigin
              href="<?= asset('fonts/ibm-plex-sans-arabic/IBMPlexSansArabic-Regular.woff2') ?>">
        <link rel="preload" as="font" type="font/woff2" crossorigin
              href="<?= asset('fonts/ibm-plex-sans-arabic/IBMPlexSansArabic-Bold.woff2') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= asset('css/fonts.css') ?>">

    <?php if ($dir === 'rtl'): ?>
        <link rel="stylesheet"
              href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
              integrity="sha384-dpuaG1suU0eT09tx5plTaGMLBsfDLzUCCUXOY2j/LSvXYuG6Bqs43ALlhIqAJVRb"
              crossorigin="anonymous">
    <?php else: ?>
        <link rel="stylesheet"
              href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
              integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
              crossorigin="anonymous">
    <?php endif; ?>

    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">

    <!-- Site-wide Organization schema -->
    <?php if ($orgSchema !== null): ?>
        <script type="application/ld+json"><?= json_encode($orgSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>

    <!-- BreadcrumbList schema (rendered when the controller passes $breadcrumb) -->
    <?php if (! empty($breadcrumb) && is_array($breadcrumb)): ?>
        <?php $bc = \App\Services\Seo\BreadcrumbBuilder::build($breadcrumb); ?>
        <script type="application/ld+json"><?= json_encode($bc, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>

    <?= $this->yield('head_extras') ?>
</head>
<body>
    <?= $this->partial('partials/header') ?>

    <main class="kae-main">
        <?= $this->yield('content') ?>
    </main>

    <?= $this->partial('partials/footer') ?>

    <?= $this->partial('partials/cookie-banner') ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous" defer></script>

    <?= $this->partial('partials/analytics') ?>
</body>
</html>
