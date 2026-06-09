<?php
/** @var \App\Core\View $this  @var string $locale  @var array $available_locales */
$nativeNames = (array) config('locales.native', []);
?>
<footer class="kae-footer">
    <div class="container">
        <div class="row g-4">
            <!-- Brand column -->
            <div class="col-12 col-md-4">
                <a href="<?= e(locale_url('/')) ?>" class="kae-footer-brand d-inline-flex align-items-center text-decoration-none mb-3">
                    <img src="<?= asset('img/logo-light.svg') ?>" alt="<?= e(t('common.brand.name')) ?>"
                         width="240" height="56" style="height: 48px; width: auto;">
                </a>
                <p class="small text-white-50 mb-0" style="max-width: 36ch;">
                    <?= e(t('common.footer.tagline')) ?>
                </p>
            </div>

            <!-- Quick links -->
            <div class="col-6 col-md-2">
                <h5 class="text-uppercase small fw-bold text-white mb-3" style="letter-spacing: 0.12em;">
                    <?= e(t('common.footer.quick_links')) ?>
                </h5>
                <ul class="list-unstyled small">
                    <li class="mb-1"><a href="<?= e(locale_url('/vehicles')) ?>"><?= e(t('common.nav.vehicles')) ?></a></li>
                    <li class="mb-1"><a href="<?= e(locale_url('/request-vehicle')) ?>"><?= e(t('common.cta.request_vehicle')) ?></a></li>
                    <li class="mb-1"><a href="<?= e(locale_url('/import-process')) ?>"><?= e(t('common.nav.process')) ?></a></li>
                    <li class="mb-1"><a href="<?= e(locale_url('/cost-calculator')) ?>"><?= e(t('common.nav.cost_calculator')) ?></a></li>
                </ul>
            </div>

            <!-- Company -->
            <div class="col-6 col-md-2">
                <h5 class="text-uppercase small fw-bold text-white mb-3" style="letter-spacing: 0.12em;">
                    <?= e(t('common.footer.company')) ?>
                </h5>
                <ul class="list-unstyled small">
                    <li class="mb-1"><a href="<?= e(locale_url('/about')) ?>"><?= e(t('common.nav.about')) ?></a></li>
                    <li class="mb-1"><a href="<?= e(locale_url('/why-korea')) ?>"><?= e(t('common.nav.why_korea')) ?></a></li>
                    <li class="mb-1"><a href="<?= e(locale_url('/testimonials')) ?>"><?= e(t('common.nav.testimonials')) ?></a></li>
                    <li class="mb-1"><a href="<?= e(locale_url('/contact')) ?>"><?= e(t('common.nav.contact')) ?></a></li>
                </ul>
            </div>

            <!-- Languages -->
            <div class="col-12 col-md-4">
                <h5 class="text-uppercase small fw-bold text-white mb-3" style="letter-spacing: 0.12em;">
                    <?= e(t('common.footer.languages')) ?>
                </h5>
                <ul class="list-inline small mb-0">
                    <?php foreach ($available_locales as $code): ?>
                        <li class="list-inline-item me-2">
                            <a href="/<?= e($code) ?>/" class="<?= $code === $locale ? 'text-white fw-bold' : '' ?>">
                                <?= e($nativeNames[$code] ?? strtoupper($code)) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <hr class="my-4" style="border-color: rgba(255,255,255,0.15);">

        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center small text-white-50">
            <span>&copy; <?= date('Y') ?> <?= e(t('common.brand.name')) ?></span>
            <ul class="list-inline mb-0">
                <li class="list-inline-item me-3">
                    <a href="<?= e(locale_url('/privacy')) ?>"><?= e(t('common.footer.privacy')) ?></a>
                </li>
                <li class="list-inline-item me-3">
                    <a href="<?= e(locale_url('/terms')) ?>"><?= e(t('common.footer.terms')) ?></a>
                </li>
                <li class="list-inline-item">
                    <a href="#" id="kae-cookie-settings-trigger" data-bs-toggle="modal" data-bs-target="#kae-cookie-modal"><?= e(t('common.footer.cookie_settings')) ?></a>
                </li>
            </ul>
            <span><?= e(t('common.footer.scaffold_notice')) ?></span>
        </div>
    </div>
</footer>
