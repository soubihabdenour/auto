<?php
/** @var \App\Core\View $this  @var array $brands */
?>
<form action="<?= e(locale_url('/vehicles')) ?>" method="GET" class="kae-quick-search shadow-sm bg-white rounded-3 p-3 p-md-4">
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md">
            <label class="form-label small text-uppercase text-muted mb-1" for="qs-brand">
                <?= e(t('home.quick_search.brand')) ?>
            </label>
            <select name="brand_id" id="qs-brand" class="form-select form-select-lg">
                <option value=""><?= e(t('home.quick_search.any')) ?></option>
                <?php foreach (($brands ?? []) as $b): ?>
                    <option value="<?= e((string) $b['id']) ?>"><?= e((string) $b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small text-uppercase text-muted mb-1" for="qs-budget">
                <?= e(t('home.quick_search.budget')) ?>
            </label>
            <input type="number" name="price_max" id="qs-budget" placeholder="50000" class="form-control form-control-lg">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small text-uppercase text-muted mb-1" for="qs-year">
                <?= e(t('home.quick_search.year_min')) ?>
            </label>
            <input type="number" name="year_min" id="qs-year" placeholder="2018" min="2000" max="2030" class="form-control form-control-lg">
        </div>
        <div class="col-12 col-md-auto">
            <button type="submit" class="btn btn-primary btn-lg w-100">
                <?= e(t('home.quick_search.go')) ?>
            </button>
        </div>
    </div>
</form>
