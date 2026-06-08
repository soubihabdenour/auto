<?php
/**
 * @var \App\Core\View $this
 * @var array $brands
 * @var array $models
 * @var array $body_types
 * @var \App\Repositories\VehicleSearchCriteria $criteria
 */
?>
<aside class="kae-filters" id="kae-filters">
    <form id="kae-filter-form" method="GET" action="<?= e(locale_url('/vehicles')) ?>" class="bg-white rounded-3 border p-3">
        <h2 class="h6 text-uppercase fw-bold text-muted mb-3"><?= e(t('vehicle.filter.title')) ?></h2>

        <div class="mb-3">
            <label class="form-label small" for="f-q"><?= e(t('vehicle.filter.search')) ?></label>
            <input type="search" name="q" id="f-q" value="<?= e((string) ($criteria->search ?? '')) ?>" class="form-control form-control-sm">
        </div>

        <div class="mb-3">
            <label class="form-label small" for="f-brand"><?= e(t('vehicle.filter.brand')) ?></label>
            <select name="brand_id" id="f-brand" class="form-select form-select-sm">
                <option value=""><?= e(t('vehicle.filter.any')) ?></option>
                <?php foreach ($brands as $b): ?>
                    <option value="<?= e((string) $b['id']) ?>" <?= $criteria->brandId === (int) $b['id'] ? 'selected' : '' ?>>
                        <?= e((string) $b['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label small" for="f-model"><?= e(t('vehicle.filter.model')) ?></label>
            <select name="model_id" id="f-model" class="form-select form-select-sm">
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

        <div class="mb-3">
            <label class="form-label small" for="f-body"><?= e(t('vehicle.filter.body_type')) ?></label>
            <select name="body_type_id" id="f-body" class="form-select form-select-sm">
                <option value=""><?= e(t('vehicle.filter.any')) ?></option>
                <?php foreach ($body_types as $bt): ?>
                    <option value="<?= e((string) $bt['id']) ?>" <?= $criteria->bodyTypeId === (int) $bt['id'] ? 'selected' : '' ?>>
                        <?= e((string) $bt['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row g-2 mb-3">
            <div class="col">
                <label class="form-label small" for="f-ymin"><?= e(t('vehicle.filter.year_min')) ?></label>
                <input type="number" min="2000" max="2030" name="year_min" id="f-ymin"
                       value="<?= e((string) ($criteria->yearMin ?? '')) ?>"
                       class="form-control form-control-sm">
            </div>
            <div class="col">
                <label class="form-label small" for="f-ymax"><?= e(t('vehicle.filter.year_max')) ?></label>
                <input type="number" min="2000" max="2030" name="year_max" id="f-ymax"
                       value="<?= e((string) ($criteria->yearMax ?? '')) ?>"
                       class="form-control form-control-sm">
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col">
                <label class="form-label small" for="f-pmin"><?= e(t('vehicle.filter.price_min')) ?></label>
                <input type="number" min="0" step="100" name="price_min" id="f-pmin"
                       value="<?= e((string) ($criteria->priceMinUsd ?? '')) ?>"
                       class="form-control form-control-sm">
            </div>
            <div class="col">
                <label class="form-label small" for="f-pmax"><?= e(t('vehicle.filter.price_max')) ?></label>
                <input type="number" min="0" step="100" name="price_max" id="f-pmax"
                       value="<?= e((string) ($criteria->priceMaxUsd ?? '')) ?>"
                       class="form-control form-control-sm">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small" for="f-mmax"><?= e(t('vehicle.filter.mileage_max')) ?></label>
            <input type="number" min="0" step="5000" name="mileage_max" id="f-mmax"
                   value="<?= e((string) ($criteria->mileageMax ?? '')) ?>"
                   class="form-control form-control-sm">
        </div>

        <div class="mb-3">
            <label class="form-label small" for="f-fuel"><?= e(t('vehicle.filter.fuel')) ?></label>
            <select name="fuel" id="f-fuel" class="form-select form-select-sm">
                <option value=""><?= e(t('vehicle.filter.any')) ?></option>
                <?php foreach (['petrol', 'diesel', 'hybrid', 'phev', 'electric', 'lpg'] as $fuel): ?>
                    <option value="<?= e($fuel) ?>" <?= $criteria->fuelType === $fuel ? 'selected' : '' ?>>
                        <?= e(t('vehicle.fuel.' . $fuel)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label small" for="f-trans"><?= e(t('vehicle.filter.transmission')) ?></label>
            <select name="transmission" id="f-trans" class="form-select form-select-sm">
                <option value=""><?= e(t('vehicle.filter.any')) ?></option>
                <?php foreach (['manual', 'automatic', 'dct', 'cvt'] as $tr): ?>
                    <option value="<?= e($tr) ?>" <?= $criteria->transmission === $tr ? 'selected' : '' ?>>
                        <?= e(t('vehicle.transmission.' . $tr)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-sm"><?= e(t('vehicle.filter.apply')) ?></button>
            <a href="<?= e(locale_url('/vehicles')) ?>" class="btn btn-link btn-sm"><?= e(t('vehicle.filter.reset')) ?></a>
        </div>
    </form>
</aside>
