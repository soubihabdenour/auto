<?php
/** @var \App\Core\View $this  @var string $locale  @var array $available_locales */
?>
<header class="kae-header" id="kae-header">
    <nav class="container d-flex align-items-center justify-content-between py-3">
        <a href="<?= e(locale_url('/')) ?>" class="kae-brand text-decoration-none" aria-label="<?= e(t('common.brand.name')) ?>">
            <img src="<?= asset('img/logo.svg') ?>" alt="<?= e(t('common.brand.name')) ?>"
                 class="kae-brand-mark kae-brand-mark--full d-none d-md-block" width="280" height="64">
            <img src="<?= asset('img/logo-mark.svg') ?>" alt="<?= e(t('common.brand.name')) ?>"
                 class="kae-brand-mark d-md-none" width="40" height="40">
        </a>

        <ul class="nav d-none d-lg-flex align-items-center gap-1 m-0">
            <li class="nav-item"><a class="nav-link" href="<?= e(locale_url('/')) ?>"><?= e(t('common.nav.home')) ?></a></li>
            <li class="nav-item"><a class="nav-link" href="<?= e(locale_url('/vehicles')) ?>"><?= e(t('common.nav.vehicles')) ?></a></li>
            <li class="nav-item"><a class="nav-link" href="<?= e(locale_url('/why-korea')) ?>"><?= e(t('common.nav.why_korea')) ?></a></li>
            <li class="nav-item"><a class="nav-link" href="<?= e(locale_url('/import-process')) ?>"><?= e(t('common.nav.process')) ?></a></li>
            <li class="nav-item"><a class="nav-link" href="<?= e(locale_url('/contact')) ?>"><?= e(t('common.nav.contact')) ?></a></li>
        </ul>

        <div class="d-flex align-items-center gap-2">
            <a href="<?= e(locale_url('/request-vehicle')) ?>" class="btn btn-primary d-none d-md-inline-flex">
                <?= e(t('common.cta.request_vehicle')) ?>
            </a>
            <?= $this->partial('partials/lang-switcher') ?>
        </div>
    </nav>
</header>

<script>
// Subtle elevation when scrolled past the hero
(function () {
    var h = document.getElementById('kae-header');
    if (!h) return;
    function onScroll() {
        h.classList.toggle('is-scrolled', window.scrollY > 12);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();
</script>
