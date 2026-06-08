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
?>
<?php $this->section('content'); ?>

<section class="kae-page-hero py-4 bg-light border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb" class="small">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= e(locale_url('/')) ?>"><?= e(t('vehicle.detail.breadcrumb.home')) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e(t('vehicle.detail.breadcrumb.vehicles')) ?></li>
            </ol>
        </nav>
        <h1 class="h2 fw-bold mb-1 mt-2"><?= e(t('vehicle.list.title')) ?></h1>
        <p class="text-muted mb-0"><?= e(t('vehicle.list.subtitle')) ?></p>
    </div>
</section>

<section class="container py-4">
    <div class="row g-4">
        <!-- Filters sidebar -->
        <div class="col-12 col-lg-3">
            <!-- Mobile collapse trigger -->
            <button class="btn btn-outline-dark d-lg-none w-100 mb-3" type="button"
                    data-bs-toggle="collapse" data-bs-target="#kae-filters-collapse">
                <?= e(t('vehicle.filter.title')) ?>
            </button>
            <div class="collapse d-lg-block" id="kae-filters-collapse">
                <?= $this->partial('partials/vehicle-filters', [
                    'criteria'   => $criteria,
                    'brands'     => $brands,
                    'models'     => $models,
                    'body_types' => $body_types,
                ]) ?>
            </div>
        </div>

        <!-- Results column -->
        <div class="col-12 col-lg-9">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div class="text-muted small">
                    <?= e(t('vehicle.list.count', ['count' => (string) $total])) ?>
                </div>

                <form class="d-flex align-items-center gap-2" id="kae-sort-form" method="GET" action="<?= e(locale_url('/vehicles')) ?>">
                    <?php foreach ($criteria->toQueryArray() as $k => $v): ?>
                        <?php if ($k === 'sort' || $k === 'page') continue; ?>
                        <input type="hidden" name="<?= e($k) ?>" value="<?= e((string) $v) ?>">
                    <?php endforeach; ?>
                    <label for="kae-sort" class="form-label small text-muted mb-0">
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
</section>

<script src="<?= e(asset('js/vehicle-filter.js')) ?>" defer></script>
<?php $this->endSection(); ?>
