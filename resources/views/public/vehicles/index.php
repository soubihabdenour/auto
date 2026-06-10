<?php
/**
 * @var \App\Core\View $this
 * @var array  $results
 * @var int    $total
 * @var int    $pages
 * @var \App\Repositories\VehicleSearchCriteria $criteria
 * @var array  $brands
 * @var array  $models
 * @var array  $body_types
 */
$this->extends('layouts/public');

// Count active filters (ignoring sort/pagination keys)
$q = $criteria->toQueryArray();
unset($q['sort'], $q['page'], $q['perPage'], $q['perpage']);
$activeFilters = count(array_filter($q, static fn ($v) => $v !== null && $v !== '' && $v !== '0'));
?>
<?php $this->section('content'); ?>

<!-- ============ LISTING HERO — dark page band ============ -->
<section class="kae-list-hero">
    <div class="container">
        <nav aria-label="breadcrumb" class="kae-vd-crumbs mb-4">
            <a href="<?= e(locale_url('/')) ?>"><?= e(t('vehicle.detail.breadcrumb.home')) ?></a>
            <span aria-hidden="true">/</span>
            <span aria-current="page"><?= e(t('vehicle.detail.breadcrumb.vehicles')) ?></span>
        </nav>

        <div class="row align-items-end g-3">
            <div class="col-12 col-lg-8">
                <span class="kae-eyebrow" data-reveal><?= e(t('vehicle.list.title')) ?></span>
                <h1 class="kae-list-hero-title" data-reveal data-reveal-delay="100">
                    <?= e(t('vehicle.list.subtitle')) ?>
                </h1>
            </div>
            <div class="col-12 col-lg-4 text-lg-end" data-reveal data-reveal-delay="200">
                <div class="kae-list-hero-count">
                    <span class="kae-list-hero-count-num"><?= number_format($total) ?></span>
                    <span class="kae-list-hero-count-label"><?= e(t('vehicle.list.title')) ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ LISTING BODY ============ -->
<section class="kae-section pt-4 pt-lg-5">
    <div class="container">
        <div class="row g-4 g-lg-5">

            <!-- ============ FILTERS SIDEBAR ============ -->
            <aside class="col-12 col-lg-3" id="kae-filters">
                <button class="btn btn-outline-dark d-lg-none w-100 mb-3" type="button"
                        data-bs-toggle="collapse" data-bs-target="#kae-filters-collapse"
                        aria-expanded="false" aria-controls="kae-filters-collapse">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="margin-inline-end: 8px;"><line x1="4" y1="6" x2="20" y2="6"/><line x1="6" y1="12" x2="18" y2="12"/><line x1="9" y1="18" x2="15" y2="18"/></svg>
                    <?= e(t('vehicle.filter.title')) ?>
                    <?php if ($activeFilters > 0): ?>
                        <span class="kae-filter-chip-count"><?= (int) $activeFilters ?></span>
                    <?php endif; ?>
                </button>
                <div class="collapse d-lg-block" id="kae-filters-collapse">
                    <?= $this->partial('partials/vehicle-filters', [
                        'criteria'   => $criteria,
                        'brands'     => $brands,
                        'models'     => $models,
                        'body_types' => $body_types,
                        'active_filters' => $activeFilters,
                    ]) ?>
                </div>
            </aside>

            <!-- ============ RESULTS COLUMN ============ -->
            <div class="col-12 col-lg-9">

                <div class="kae-list-toolbar">
                    <div class="kae-list-toolbar-meta">
                        <?= e(t('vehicle.list.count', ['count' => (string) $total])) ?>
                        <?php if ($activeFilters > 0): ?>
                            <span class="kae-list-toolbar-pill">
                                <?= (int) $activeFilters ?> active
                                <a href="<?= e(locale_url('/vehicles')) ?>" class="kae-list-toolbar-pill-clear" aria-label="<?= e(t('vehicle.filter.reset')) ?>">×</a>
                            </span>
                        <?php endif; ?>
                    </div>

                    <form class="kae-list-toolbar-sort" id="kae-sort-form" method="GET" action="<?= e(locale_url('/vehicles')) ?>">
                        <?php foreach ($criteria->toQueryArray() as $k => $v): ?>
                            <?php if ($k === 'sort' || $k === 'page') continue; ?>
                            <input type="hidden" name="<?= e($k) ?>" value="<?= e((string) $v) ?>">
                        <?php endforeach; ?>
                        <label for="kae-sort" class="kae-list-toolbar-sort-label">
                            <?= e(t('vehicle.sort.label')) ?>
                        </label>
                        <select name="sort" id="kae-sort" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php foreach (['newest', 'price_asc', 'price_desc', 'mileage_asc', 'year_desc'] as $s): ?>
                                <option value="<?= e($s) ?>" <?= $criteria->sort === $s ? 'selected' : '' ?>>
                                    <?= e(t('vehicle.sort.' . $s)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <div id="kae-results" data-filter-endpoint="<?= e(locale_url('/vehicles/filter')) ?>">
                    <?= $this->partial('public/vehicles/_results', [
                        'results' => $results,
                        'total'   => $total,
                        'pages'   => $pages,
                        'criteria'=> $criteria,
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?= e(asset('js/vehicle-filter.js')) ?>" defer></script>
<?php $this->endSection(); ?>
