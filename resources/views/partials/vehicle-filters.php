<?php
/**
 * @var \App\Core\View $this
 * @var array $brands
 * @var array $models
 * @var array $body_types
 * @var \App\Repositories\VehicleSearchCriteria $criteria
 * @var int   $active_filters
 */
$active_filters = $active_filters ?? 0;
?>
<aside class="kae-filters" id="kae-filters-aside">
    <form id="kae-filter-form" method="GET" action="<?= e(locale_url('/vehicles')) ?>">
        <div class="kae-filters-head">
            <h2 class="kae-filters-title"><?= e(t('vehicle.filter.title')) ?></h2>
            <?php if ($active_filters > 0): ?>
                <a href="<?= e(locale_url('/vehicles')) ?>" class="kae-filters-reset-link">
                    <?= e(t('vehicle.filter.reset')) ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="kae-filter-group">
            <label class="form-label" for="f-q"><?= e(t('vehicle.filter.search')) ?></label>
            <input type="search" name="q" id="f-q" value="<?= e((string) ($criteria->search ?? '')) ?>"
                   class="form-control" placeholder="VIN, model, color…">
        </div>

        <div class="kae-filter-group">
            <label class="form-label" for="f-brand"><?= e(t('vehicle.filter.brand')) ?></label>
            <select name="brand_id" id="f-brand" class="form-select">
                <option value=""><?= e(t('vehicle.filter.any')) ?></option>
                <?php foreach ($brands as $b): ?>
                    <option value="<?= e((string) $b['id']) ?>" <?= $criteria->brandId === (int) $b['id'] ? 'selected' : '' ?>>
                        <?= e((string) $b['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="kae-filter-group">
            <label class="form-label" for="f-model"><?= e(t('vehicle.filter.model')) ?></label>
            <select name="model_id" id="f-model" class="form-select">
                <option value=""><?= e(t('vehicle.filter.any')) ?></option>
                <?php foreach ($models as $m): ?>
                    <option value="<?= e((string) $m['id']) ?>"
                            data-brand="<?= e((string) $m['brand_id']) ?>"
                            <?= $criteria->modelId === (int) $m['id'] ? 'selected' : '' ?>>
                        <?= e((string) $m['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="kae-filter-group">
            <label class="form-label" for="f-body"><?= e(t('vehicle.filter.body_type')) ?></label>
            <select name="body_type_id" id="f-body" class="form-select">
                <option value=""><?= e(t('vehicle.filter.any')) ?></option>
                <?php foreach ($body_types as $bt): ?>
                    <option value="<?= e((string) $bt['id']) ?>" <?= $criteria->bodyTypeId === (int) $bt['id'] ? 'selected' : '' ?>>
                        <?= e((string) $bt['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="kae-filter-divider"></div>

        <div class="kae-filter-group">
            <label class="form-label"><?= e(t('vehicle.filter.year_min')) ?> – <?= e(t('vehicle.filter.year_max')) ?></label>
            <div class="row g-2">
                <div class="col">
                    <input type="number" min="2000" max="2030" name="year_min" id="f-ymin"
                           value="<?= e((string) ($criteria->yearMin ?? '')) ?>"
                           placeholder="2018"
                           class="form-control">
                </div>
                <div class="col">
                    <input type="number" min="2000" max="2030" name="year_max" id="f-ymax"
                           value="<?= e((string) ($criteria->yearMax ?? '')) ?>"
                           placeholder="2025"
                           class="form-control">
                </div>
            </div>
        </div>

        <div class="kae-filter-group">
            <label class="form-label"><?= e(t('vehicle.filter.price_min')) ?> – <?= e(t('vehicle.filter.price_max')) ?> (USD)</label>
            <div class="row g-2">
                <div class="col">
                    <input type="number" min="0" step="100" name="price_min" id="f-pmin"
                           value="<?= e((string) ($criteria->priceMinUsd ?? '')) ?>"
                           placeholder="5,000"
                           class="form-control">
                </div>
                <div class="col">
                    <input type="number" min="0" step="100" name="price_max" id="f-pmax"
                           value="<?= e((string) ($criteria->priceMaxUsd ?? '')) ?>"
                           placeholder="40,000"
                           class="form-control">
                </div>
            </div>
        </div>

        <div class="kae-filter-group">
            <label class="form-label" for="f-mmax"><?= e(t('vehicle.filter.mileage_max')) ?></label>
            <input type="number" min="0" step="5000" name="mileage_max" id="f-mmax"
                   value="<?= e((string) ($criteria->mileageMax ?? '')) ?>"
                   placeholder="80,000 km"
                   class="form-control">
        </div>

        <div class="kae-filter-divider"></div>

        <div class="kae-filter-group">
            <label class="form-label" for="f-fuel"><?= e(t('vehicle.filter.fuel')) ?></label>
            <select name="fuel" id="f-fuel" class="form-select">
                <option value=""><?= e(t('vehicle.filter.any')) ?></option>
                <?php foreach (['petrol', 'diesel', 'hybrid', 'phev', 'electric', 'lpg'] as $fuel): ?>
                    <option value="<?= e($fuel) ?>" <?= $criteria->fuelType === $fuel ? 'selected' : '' ?>>
                        <?= e(t('vehicle.fuel.' . $fuel)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="kae-filter-group">
            <label class="form-label" for="f-trans"><?= e(t('vehicle.filter.transmission')) ?></label>
            <select name="transmission" id="f-trans" class="form-select">
                <option value=""><?= e(t('vehicle.filter.any')) ?></option>
                <?php foreach (['manual', 'automatic', 'dct', 'cvt'] as $tr): ?>
                    <option value="<?= e($tr) ?>" <?= $criteria->transmission === $tr ? 'selected' : '' ?>>
                        <?= e(t('vehicle.transmission.' . $tr)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="kae-filter-actions">
            <button type="submit" class="btn btn-primary w-100"><?= e(t('vehicle.filter.apply')) ?></button>
            <?php if ($active_filters > 0): ?>
                <a href="<?= e(locale_url('/vehicles')) ?>" class="btn btn-link w-100"><?= e(t('vehicle.filter.reset')) ?></a>
            <?php endif; ?>
        </div>
    </form>
</aside>
