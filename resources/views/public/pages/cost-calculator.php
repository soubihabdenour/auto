<?php
/**
 * @var \App\Core\View $this
 * @var string     $currency   'usd' | 'krw'  (krw mode now reads as 만원)
 * @var float|null $price_in   raw input in the selected currency unit (USD or 만원)
 * @var float|null $price_usd  normalised to USD
 * @var float|null $price_krw  paired KRW figure in raw KRW (display divides by 10,000)
 * @var array|null $estimate
 * @var array      $rates
 */
$this->extends('layouts/public');
$activeUsd  = $currency === 'usd';
$manwonRate = $rates['fx_usd_to_krw'] / 10000;   // 1380 KRW → 0.138 만원 per USD → display as "138 만원 / 1 USD"
?>
<?php $this->section('content'); ?>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="text-center mb-4">
                <span class="kae-eyebrow"><?= e(t('pages.cost_calculator.eyebrow')) ?></span>
                <h1 class="h2 fw-bold mt-2 mb-2"><?= e(t('pages.cost_calculator.title')) ?></h1>
                <p class="text-muted lead mb-0"><?= e(t('pages.cost_calculator.subtitle')) ?></p>
            </div>

            <div class="row g-4">
                <!-- INPUT -->
                <div class="col-12 col-md-5">
                    <form method="GET" action="<?= e(locale_url('/cost-calculator')) ?>" class="kae-card p-3 p-md-4 h-100">
                        <!-- Currency toggle -->
                        <div class="btn-group w-100 mb-3" role="group" aria-label="Currency">
                            <input type="radio" class="btn-check" name="currency" id="cc-cur-usd" value="usd" <?= $activeUsd ? 'checked' : '' ?>>
                            <label class="btn btn-outline-dark" for="cc-cur-usd">USD ($)</label>
                            <input type="radio" class="btn-check" name="currency" id="cc-cur-krw" value="krw" <?= $activeUsd ? '' : 'checked' ?>>
                            <label class="btn btn-outline-dark" for="cc-cur-krw">만원 (Manwon)</label>
                        </div>

                        <label class="form-label fw-semibold" for="kae-cc-price">
                            <?= e(t('pages.cost_calculator.input_label')) ?>
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text" id="kae-cc-symbol"><?= $activeUsd ? '$' : '₩' ?></span>
                            <input type="number" min="0" step="<?= $activeUsd ? '100' : '10' ?>" inputmode="numeric"
                                   name="price" id="kae-cc-price" class="form-control"
                                   value="<?= $price_in !== null ? e((string) (int) $price_in) : '' ?>"
                                   placeholder="<?= $activeUsd ? '20000' : '2700' ?>">
                            <span class="input-group-text" id="kae-cc-unit"><?= $activeUsd ? 'USD' : '만원' ?></span>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <?= e(t('pages.cost_calculator.input_hint')) ?>
                        </small>

                        <!-- Live conversion line under input -->
                        <div class="small mt-2" id="kae-cc-converted"
                             <?= ($price_usd !== null && $price_krw !== null) ? '' : 'style="display:none"' ?>>
                            <span class="text-muted"><?= e(t('pages.cost_calculator.equivalent')) ?>:</span>
                            <strong id="kae-cc-converted-value">
                                <?php if ($price_usd !== null && $price_krw !== null): ?>
                                    <?= $activeUsd
                                        ? e(number_format($price_krw / 10000, 0)) . ' 만원'
                                        : '$' . e(number_format($price_usd, 0)) ?>
                                <?php endif; ?>
                            </strong>
                        </div>

                        <hr class="my-3">

                        <h2 class="h6 fw-bold mb-2"><?= e(t('pages.cost_calculator.rates_title')) ?></h2>
                        <dl class="row mb-0 small">
                            <dt class="col-7 text-muted"><?= e(t('pages.cost_calculator.rates.shipping')) ?></dt>
                            <dd class="col-5 text-end">$<?= e(number_format($rates['shipping_base_usd'], 0)) ?></dd>
                            <dt class="col-7 text-muted"><?= e(t('pages.cost_calculator.rates.customs')) ?></dt>
                            <dd class="col-5 text-end"><?= e(number_format($rates['customs_rate'] * 100, 1)) ?>%</dd>
                            <dt class="col-7 text-muted"><?= e(t('pages.cost_calculator.rates.tva')) ?></dt>
                            <dd class="col-5 text-end"><?= e(number_format($rates['tva_rate'] * 100, 1)) ?>%</dd>
                            <dt class="col-7 text-muted"><?= e(t('pages.cost_calculator.rates.service_flat')) ?></dt>
                            <dd class="col-5 text-end">$<?= e(number_format($rates['service_fee_flat_usd'], 0)) ?></dd>
                            <dt class="col-7 text-muted"><?= e(t('pages.cost_calculator.rates.service_pct')) ?></dt>
                            <dd class="col-5 text-end"><?= e(number_format($rates['service_fee_percent'] * 100, 1)) ?>%</dd>
                            <dt class="col-7 text-muted"><?= e(t('pages.cost_calculator.rates.fx_dzd')) ?></dt>
                            <dd class="col-5 text-end"><?= e(number_format($rates['fx_usd_to_dzd'], 2)) ?> DA</dd>
                            <dt class="col-7 text-muted"><?= e(t('pages.cost_calculator.rates.fx_krw')) ?></dt>
                            <dd class="col-5 text-end"><?= e(number_format($manwonRate, 2)) ?> 만원</dd>
                        </dl>

                        <noscript>
                            <button type="submit" class="btn btn-primary mt-3 w-100">
                                <?= e(t('pages.cost_calculator.submit')) ?>
                            </button>
                        </noscript>
                    </form>
                </div>

                <!-- RESULT -->
                <div class="col-12 col-md-7">
                    <div class="kae-card p-3 p-md-4 h-100" id="kae-cc-result">

                        <div id="kae-cc-empty" <?= $price_usd === null ? '' : 'style="display:none"' ?>>
                            <p class="text-muted mb-0">
                                <?= e(t('pages.cost_calculator.empty')) ?>
                            </p>
                        </div>

                        <div id="kae-cc-body" <?= $price_usd === null ? 'style="display:none"' : '' ?>>
                            <h2 class="h6 fw-bold mb-3"><?= e(t('pages.cost_calculator.breakdown_title')) ?></h2>
                            <dl class="mb-0">
                                <div class="d-flex justify-content-between py-2">
                                    <dt class="text-muted fw-normal"><?= e(t('vehicle.detail.cost.vehicle')) ?></dt>
                                    <dd class="mb-0 fw-semibold" data-row="vehicle_usd">
                                        <?= $estimate ? e(format_price((float) $estimate['vehicle_usd'])) : '—' ?>
                                    </dd>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-top">
                                    <dt class="text-muted fw-normal"><?= e(t('vehicle.detail.cost.shipping')) ?></dt>
                                    <dd class="mb-0 fw-semibold" data-row="shipping_usd">
                                        <?= $estimate ? e(format_price((float) $estimate['shipping_usd'])) : '—' ?>
                                    </dd>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-top">
                                    <dt class="text-muted fw-normal"><?= e(t('vehicle.detail.cost.customs')) ?></dt>
                                    <dd class="mb-0 fw-semibold" data-row="customs_usd">
                                        <?= $estimate ? e(format_price((float) $estimate['customs_usd'])) : '—' ?>
                                    </dd>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-top">
                                    <dt class="text-muted fw-normal"><?= e(t('vehicle.detail.cost.service_fee')) ?></dt>
                                    <dd class="mb-0 fw-semibold" data-row="service_fee_usd">
                                        <?= $estimate ? e(format_price((float) $estimate['service_fee_usd'])) : '—' ?>
                                    </dd>
                                </div>
                                <div class="d-flex justify-content-between py-3 border-top mt-2">
                                    <dt class="fw-bold"><?= e(t('vehicle.detail.cost.total_usd')) ?></dt>
                                    <dd class="mb-0 fw-bold fs-4 text-primary" data-row="total_usd">
                                        <?= $estimate ? e(format_price((float) $estimate['total_usd'])) : '—' ?>
                                    </dd>
                                </div>
                                <div class="d-flex justify-content-between py-2">
                                    <dt class="text-muted fw-normal"><?= e(t('vehicle.detail.cost.total_dzd')) ?></dt>
                                    <dd class="mb-0 fw-semibold" data-row="total_dzd">
                                        <?= $estimate ? e(format_price((float) $estimate['total_dzd'], 'DZD')) : '—' ?>
                                    </dd>
                                </div>
                                <div class="d-flex justify-content-between py-2">
                                    <dt class="text-muted fw-normal"><?= e(t('pages.cost_calculator.rows.total_manwon')) ?></dt>
                                    <dd class="mb-0 fw-semibold" data-row="total_manwon">
                                        <?php if ($estimate): ?>
                                            <?= e(number_format((float) $estimate['total_usd'] * $rates['fx_usd_to_krw'] / 10000, 0)) ?> 만원
                                        <?php else: ?>—<?php endif; ?>
                                    </dd>
                                </div>
                            </dl>

                            <p class="text-muted small mt-3 mb-0">
                                <?= e(t('vehicle.detail.cost.disclaimer')) ?>
                            </p>

                            <div class="d-grid gap-2 mt-4">
                                <a href="<?= e(locale_url('/vehicles')) ?>" class="btn btn-outline-dark">
                                    <?= e(t('pages.cost_calculator.cta_browse')) ?>
                                </a>
                                <a href="<?= e(locale_url('/request-vehicle')) ?>" class="btn btn-primary">
                                    <?= e(t('pages.cost_calculator.cta_request')) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ QUICK FX CONVERTER (no fees) ============ -->
            <div class="kae-card p-3 p-md-4 mt-4" id="kae-fx">
                <div class="d-flex justify-content-between align-items-baseline mb-2 flex-wrap gap-2">
                    <h2 class="h6 fw-bold mb-0"><?= e(t('pages.cost_calculator.fx_title')) ?></h2>
                    <small class="text-muted">
                        1 USD = <?= e(number_format($manwonRate, 2)) ?> 만원
                        · 1 USD = <?= e(number_format($rates['fx_usd_to_dzd'], 0)) ?> DA
                    </small>
                </div>
                <p class="small text-muted mb-3"><?= e(t('pages.cost_calculator.fx_subtitle')) ?></p>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold" for="fx-manwon">만원 (Manwon)</label>
                        <input type="number" min="0" step="10" inputmode="numeric"
                               id="fx-manwon" class="form-control" placeholder="2700">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold" for="fx-usd">$ USD</label>
                        <input type="number" min="0" step="100" inputmode="numeric"
                               id="fx-usd" class="form-control" placeholder="20000">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold" for="fx-dzd">DA (DZD)</label>
                        <input type="number" min="0" step="10000" inputmode="numeric"
                               id="fx-dzd" class="form-control" placeholder="2700000">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    const rates    = <?= json_encode($rates, JSON_UNESCAPED_SLASHES) ?>;
    const localeJs = <?= json_encode(current_locale()) ?>;
    const priceEl  = document.getElementById('kae-cc-price');
    const result   = document.getElementById('kae-cc-result');
    const empty    = document.getElementById('kae-cc-empty');
    const body     = document.getElementById('kae-cc-body');
    const conv     = document.getElementById('kae-cc-converted');
    const convVal  = document.getElementById('kae-cc-converted-value');
    const symbolEl = document.getElementById('kae-cc-symbol');
    const unitEl   = document.getElementById('kae-cc-unit');
    const curRadios = document.querySelectorAll('input[name="currency"]');
    if (!priceEl || !result) return;

    const intlLocale = localeJs === 'ar' ? 'ar-DZ' : (localeJs === 'fr' ? 'fr-DZ' : 'en-US');
    const fmtUsd     = new Intl.NumberFormat(intlLocale, { style: 'currency', currency: 'USD', maximumFractionDigits: 0 });
    const fmtDzd     = new Intl.NumberFormat(intlLocale, { style: 'currency', currency: 'DZD', maximumFractionDigits: 0 });
    const fmtNum     = new Intl.NumberFormat(intlLocale, { maximumFractionDigits: 0 });
    const fmtManwon  = (krw) => fmtNum.format(krw / 10000) + ' 만원';

    function currency() {
        const checked = document.querySelector('input[name="currency"]:checked');
        return checked ? checked.value : 'usd';
    }

    function estimate(vehicleUsd) {
        const vehicle  = Math.max(0, vehicleUsd);
        const shipping = rates.shipping_base_usd;
        let   customs  = (vehicle + shipping) * rates.customs_rate;
        const tva      = (vehicle + shipping + customs) * rates.tva_rate;
        customs += tva;
        const service  = rates.service_fee_flat_usd + (vehicle * rates.service_fee_percent);
        const totalUsd = vehicle + shipping + customs + service;
        return {
            vehicle_usd:     vehicle,
            shipping_usd:    shipping,
            customs_usd:     customs,
            service_fee_usd: service,
            total_usd:       totalUsd,
            total_dzd:       totalUsd * rates.fx_usd_to_dzd,
            total_krw:       totalUsd * rates.fx_usd_to_krw,
        };
    }

    function render() {
        const raw = parseFloat(priceEl.value);
        if (!Number.isFinite(raw) || raw <= 0) {
            empty.style.display = '';
            body.style.display  = 'none';
            conv.style.display  = 'none';
            return;
        }
        const cur = currency();
        // 'krw' mode: raw is in 만원, convert to KRW for math
        const krw = cur === 'krw' ? raw * 10000                  : raw * rates.fx_usd_to_krw;
        const usd = cur === 'krw' ? krw / rates.fx_usd_to_krw    : raw;

        conv.style.display  = '';
        convVal.textContent = cur === 'krw' ? fmtUsd.format(usd) : fmtManwon(krw);

        empty.style.display = 'none';
        body.style.display  = '';
        const e = estimate(usd);
        result.querySelector('[data-row="vehicle_usd"]').textContent     = fmtUsd.format(e.vehicle_usd);
        result.querySelector('[data-row="shipping_usd"]').textContent    = fmtUsd.format(e.shipping_usd);
        result.querySelector('[data-row="customs_usd"]').textContent     = fmtUsd.format(e.customs_usd);
        result.querySelector('[data-row="service_fee_usd"]').textContent = fmtUsd.format(e.service_fee_usd);
        result.querySelector('[data-row="total_usd"]').textContent       = fmtUsd.format(e.total_usd);
        result.querySelector('[data-row="total_dzd"]').textContent       = fmtDzd.format(e.total_dzd);
        result.querySelector('[data-row="total_manwon"]').textContent    = fmtManwon(e.total_krw);
    }

    function applyCurrencyUI() {
        const cur = currency();
        if (symbolEl) symbolEl.textContent = cur === 'usd' ? '$' : '₩';
        if (unitEl)   unitEl.textContent   = cur === 'usd' ? 'USD' : '만원';
        priceEl.placeholder = cur === 'usd' ? '20000' : '2700';
        priceEl.step        = cur === 'usd' ? '100'   : '10';
    }

    let timer = null;
    priceEl.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(render, 120);
    });
    curRadios.forEach(r => r.addEventListener('change', () => { applyCurrencyUI(); render(); }));

    applyCurrencyUI();
    if (priceEl.value !== '') render();
})();

// Stand-alone FX converter (manwon ⇄ USD ⇄ DZD), no fees applied.
(function () {
    const rates    = <?= json_encode($rates, JSON_UNESCAPED_SLASHES) ?>;
    const manwonEl = document.getElementById('fx-manwon');
    const usdEl    = document.getElementById('fx-usd');
    const dzdEl    = document.getElementById('fx-dzd');
    if (!manwonEl || !usdEl || !dzdEl) return;

    let updating = false;
    const setIf = (el, n) => {
        if (!Number.isFinite(n) || n <= 0) { el.value = ''; return; }
        el.value = Math.round(n);
    };

    // Source of truth is USD. Recompute the other two from whichever input changed.
    function recompute(source) {
        if (updating) return;
        updating = true;
        try {
            let usd = 0;
            if (source === 'manwon') {
                const manwon = parseFloat(manwonEl.value);
                usd = Number.isFinite(manwon) ? (manwon * 10000) / rates.fx_usd_to_krw : 0;
            } else if (source === 'usd') {
                usd = parseFloat(usdEl.value) || 0;
            } else if (source === 'dzd') {
                const dzd = parseFloat(dzdEl.value);
                usd = Number.isFinite(dzd) ? dzd / rates.fx_usd_to_dzd : 0;
            }
            if (source !== 'manwon') setIf(manwonEl, (usd * rates.fx_usd_to_krw) / 10000);
            if (source !== 'usd')    setIf(usdEl,    usd);
            if (source !== 'dzd')    setIf(dzdEl,    usd * rates.fx_usd_to_dzd);
        } finally {
            updating = false;
        }
    }

    manwonEl.addEventListener('input', () => recompute('manwon'));
    usdEl.addEventListener('input',    () => recompute('usd'));
    dzdEl.addEventListener('input',    () => recompute('dzd'));
})();
</script>
<?php $this->endSection(); ?>
