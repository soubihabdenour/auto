<?php
/**
 * @var \App\Core\View $this
 * @var array $brands
 * @var array $old
 * @var array $errors
 * @var string|null $success
 */
$this->extends('layouts/public');
?>
<?php $this->section('content'); ?>

<?= $this->partial('partials/page-hero', [
    'eyebrow'  => t('pages.request_vehicle.eyebrow') ?: 'Tell us what you want',
    'title'    => t('pages.request_vehicle.title'),
    'subtitle' => t('pages.request_vehicle.subtitle'),
    'crumbs'   => [
        ['label' => t('vehicle.detail.breadcrumb.home'), 'url' => locale_url('/')],
        ['label' => t('pages.request_vehicle.title'),    'url' => null],
    ],
]) ?>

<section class="kae-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8" data-reveal>
                <div class="kae-contact-card">

                    <?php if (! empty($success)): ?>
                        <div class="alert alert-success"><?= e((string) $success) ?></div>
                    <?php endif; ?>
                    <?php if (! empty($errors['_global'][0])): ?>
                        <div class="alert alert-danger"><?= e((string) $errors['_global'][0]) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?= e(locale_url('/request-vehicle')) ?>" novalidate>
                <?= csrf_field() ?>
                <input type="text" name="_website" tabindex="-1" autocomplete="off"
                       style="position:absolute;left:-9999px;" aria-hidden="true">

                <h2 class="h4 fw-bold mb-4"><?= e(t('pages.request_vehicle.form_title')) ?></h2>

                <div class="row g-3">
                    <?php foreach (['name','phone','whatsapp','city'] as $f): ?>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="rv_<?= e($f) ?>">
                                <?= e(t('pages.request_vehicle.fields.' . $f)) ?>
                                <?php if (in_array($f, ['name','phone'], true)): ?><span class="text-danger">*</span><?php endif; ?>
                            </label>
                            <input type="<?= ($f === 'phone' || $f === 'whatsapp') ? 'tel' : 'text' ?>"
                                   name="<?= e($f) ?>" id="rv_<?= e($f) ?>"
                                   value="<?= e((string) ($old[$f] ?? '')) ?>"
                                   class="form-control <?= isset($errors[$f]) ? 'is-invalid' : '' ?>">
                            <?php if (isset($errors[$f][0])): ?>
                                <div class="invalid-feedback"><?= e($errors[$f][0]) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="rv_brand"><?= e(t('pages.request_vehicle.fields.brand')) ?></label>
                        <select name="brand" id="rv_brand" class="form-select">
                            <option value="">—</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= e((string) $b['slug']) ?>"
                                        <?= ($old['brand'] ?? '') === $b['slug'] ? 'selected' : '' ?>>
                                    <?= e((string) $b['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label" for="rv_year_min"><?= e(t('pages.request_vehicle.fields.year_min')) ?></label>
                        <input type="number" min="2000" max="2030" name="year_min" id="rv_year_min"
                               value="<?= e((string) ($old['year_min'] ?? '')) ?>" class="form-control">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="rv_year_max"><?= e(t('pages.request_vehicle.fields.year_max')) ?></label>
                        <input type="number" min="2000" max="2030" name="year_max" id="rv_year_max"
                               value="<?= e((string) ($old['year_max'] ?? '')) ?>" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="rv_budget"><?= e(t('pages.request_vehicle.fields.budget_usd')) ?></label>
                        <input type="number" min="1000" step="100" name="budget_usd" id="rv_budget"
                               value="<?= e((string) ($old['budget_usd'] ?? '')) ?>" class="form-control">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="rv_fuel"><?= e(t('pages.request_vehicle.fields.fuel')) ?></label>
                        <select name="fuel" id="rv_fuel" class="form-select">
                            <option value="">—</option>
                            <?php foreach (['petrol','diesel','hybrid','phev','electric'] as $opt): ?>
                                <option value="<?= e($opt) ?>" <?= ($old['fuel'] ?? '') === $opt ? 'selected' : '' ?>>
                                    <?= e(t('vehicle.fuel.' . $opt)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="rv_trans"><?= e(t('pages.request_vehicle.fields.transmission')) ?></label>
                        <select name="transmission" id="rv_trans" class="form-select">
                            <option value="">—</option>
                            <?php foreach (['manual','automatic','dct','cvt'] as $opt): ?>
                                <option value="<?= e($opt) ?>" <?= ($old['transmission'] ?? '') === $opt ? 'selected' : '' ?>>
                                    <?= e(t('vehicle.transmission.' . $opt)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="rv_notes"><?= e(t('pages.request_vehicle.fields.notes')) ?></label>
                        <textarea name="notes" id="rv_notes" rows="4" class="form-control"><?= e((string) ($old['notes'] ?? '')) ?></textarea>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="consent" id="rv_consent" value="1"
                                   class="form-check-input" <?= ! empty($old['consent']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="rv_consent">
                                <?= e(t('pages.request_vehicle.fields.consent')) ?>
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <?= e(t('pages.request_vehicle.submit')) ?>
                        </button>
                    </div>
                </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $this->endSection(); ?>
