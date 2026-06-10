<?php
/** @var \App\Core\View $this  @var string $locale  @var array $available_locales */
$nativeNames = (array) config('locales.native', []);
$contactPhone    = (string) (function_exists('app') ? '' : '');
?>
<footer class="kae-footer">
    <div class="container">

        <!-- Brand band: large mark + tagline + social -->
        <div class="kae-footer-brand-block">
            <div class="row align-items-end g-4">
                <div class="col-12 col-lg-7">
                    <a href="<?= e(locale_url('/')) ?>" class="kae-footer-brand d-inline-flex align-items-center mb-3" style="text-decoration: none;">
                        <img src="<?= asset('img/logo-light.svg') ?>"
                             alt="<?= e(t('common.brand.name')) ?>"
                             style="height: 44px; width: auto; display: block;">
                    </a>
                    <p class="kae-footer-tagline mb-0">
                        <?= e(t('common.footer.tagline')) ?>
                    </p>
                </div>
                <div class="col-12 col-lg-5">
                    <div class="d-flex flex-wrap justify-content-lg-end align-items-center gap-3">
                        <a href="<?= e(locale_url('/request-vehicle')) ?>" class="btn btn-primary">
                            <?= e(t('common.cta.request_vehicle')) ?>
                        </a>
                        <nav class="kae-footer-social" aria-label="Social">
                            <a href="#" aria-label="Facebook" rel="noopener" target="_blank">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.3-1.5 1.5-1.5h1.6V3.6c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.4H7.7v3.1h2.7V21h3.1z"/></svg>
                            </a>
                            <a href="#" aria-label="Instagram" rel="noopener" target="_blank">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="4"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>
                            </a>
                            <a href="#" aria-label="TikTok" rel="noopener" target="_blank">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.5 3c.3 1.6 1.2 3 2.6 3.8.7.4 1.5.6 2.4.6v3c-1.6 0-3.1-.4-4.5-1.2v6.6c0 3.4-2.7 6.1-6.1 6.1s-6.1-2.7-6.1-6.1S7.5 9.7 10.9 9.7c.3 0 .7 0 1 .1v3.1c-.3-.1-.7-.2-1-.2-1.7 0-3 1.4-3 3s1.4 3 3 3 3-1.3 3-3V3h2.6z"/></svg>
                            </a>
                            <a href="#" aria-label="YouTube" rel="noopener" target="_blank">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23 7.2s-.2-1.6-.9-2.3c-.8-.9-1.8-.9-2.2-1C16.7 3.5 12 3.5 12 3.5s-4.7 0-7.9.4c-.4.1-1.4.1-2.2 1-.7.7-.9 2.3-.9 2.3S.8 9 .8 10.8v1.7C.8 14.3 1 16.1 1 16.1s.2 1.6.9 2.3c.8.9 2 .8 2.5.9 1.8.2 7.6.4 7.6.4s4.7 0 7.9-.4c.4-.1 1.4-.1 2.2-1 .7-.7.9-2.3.9-2.3s.2-1.8.2-3.6V11c0-1.8-.2-3.8-.2-3.8zM9.7 14.6V8.5l6.1 3.1-6.1 3z"/></svg>
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Link columns -->
        <div class="row g-4 g-lg-5">
            <div class="col-6 col-lg-3">
                <h5 class="footer-heading"><?= e(t('common.footer.quick_links')) ?></h5>
                <ul class="list-unstyled mb-0" style="font-size: var(--fs-sm); line-height: 2;">
                    <li><a href="<?= e(locale_url('/vehicles')) ?>"><?= e(t('common.nav.vehicles')) ?></a></li>
                    <li><a href="<?= e(locale_url('/request-vehicle')) ?>"><?= e(t('common.cta.request_vehicle')) ?></a></li>
                    <li><a href="<?= e(locale_url('/import-process')) ?>"><?= e(t('common.nav.process')) ?></a></li>
                    <li><a href="<?= e(locale_url('/cost-calculator')) ?>"><?= e(t('common.nav.cost_calculator')) ?></a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-3">
                <h5 class="footer-heading"><?= e(t('common.footer.company')) ?></h5>
                <ul class="list-unstyled mb-0" style="font-size: var(--fs-sm); line-height: 2;">
                    <li><a href="<?= e(locale_url('/about')) ?>"><?= e(t('common.nav.about')) ?></a></li>
                    <li><a href="<?= e(locale_url('/why-korea')) ?>"><?= e(t('common.nav.why_korea')) ?></a></li>
                    <li><a href="<?= e(locale_url('/testimonials')) ?>"><?= e(t('common.nav.testimonials')) ?></a></li>
                    <li><a href="<?= e(locale_url('/contact')) ?>"><?= e(t('common.nav.contact')) ?></a></li>
                </ul>
            </div>

            <div class="col-12 col-lg-3">
                <h5 class="footer-heading"><?= e(t('common.footer.contact')) ?></h5>
                <ul class="list-unstyled mb-0" style="font-size: var(--fs-sm); line-height: 2;">
                    <li><a href="<?= e(locale_url('/contact')) ?>"><?= e(t('common.nav.contact')) ?> →</a></li>
                    <li><a href="<?= e(locale_url('/cost-calculator')) ?>"><?= e(t('common.nav.cost_calculator')) ?> →</a></li>
                </ul>
            </div>

            <div class="col-12 col-lg-3">
                <h5 class="footer-heading"><?= e(t('common.footer.languages')) ?></h5>
                <ul class="list-inline mb-0" style="font-size: var(--fs-sm);">
                    <?php foreach ($available_locales as $code): ?>
                        <li class="list-inline-item me-2">
                            <a href="/<?= e($code) ?>/"
                               class="<?= $code === $locale ? 'fw-bold' : '' ?>"
                               style="<?= $code === $locale ? 'color: #fff;' : '' ?>">
                                <?= e($nativeNames[$code] ?? strtoupper($code)) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Bottom strip -->
        <div class="kae-footer-bottom d-flex flex-wrap justify-content-between gap-3 align-items-center">
            <span>&copy; <?= date('Y') ?> <?= e(t('common.brand.name')) ?>. All rights reserved.</span>
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
        </div>
    </div>
</footer>
