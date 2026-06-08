<?php
/**
 * @var \App\Core\View $this
 * @var array|null $vehicle
 * @var array $translations  locale → row
 * @var array|null $inspection
 * @var array $images
 * @var array $old
 * @var array $errors
 * @var array $brands
 * @var array $models
 * @var array $body_types
 * @var array $statuses
 * @var array $fuel_types
 * @var array $transmissions
 * @var array $drivetrains
 * @var array $accident
 * @var array $locales
 */
$this->extends('layouts/admin');
$isEdit = $vehicle !== null;
$action = $isEdit ? '/admin/vehicles/' . (int) $vehicle['id'] : '/admin/vehicles';

// Helper: get value with priority old > db > default
$val = function (string $key, mixed $default = '') use ($old, $vehicle, $translations, $inspection) {
    if (array_key_exists($key, $old)) return $old[$key];
    if ($vehicle && array_key_exists($key, $vehicle)) return $vehicle[$key];
    // Translation fields: title_en, description_en, etc
    if (preg_match('/^(title|description|meta_title|meta_description)_(ar|fr|en)$/', $key, $m)) {
        return $translations[$m[2]][$m[1]] ?? $default;
    }
    if (preg_match('/^notes_(ar|fr|en)$/', $key, $m)) {
        return $inspection[$key] ?? $default;
    }
    if ($inspection && array_key_exists($key, $inspection)) return $inspection[$key];
    return $default;
};
?>
<?php $this->section('content'); ?>
<div class="container-fluid">

    <div class="kae-page-head">
        <div>
            <h1><?= $isEdit ? 'Edit vehicle' : 'New vehicle' ?>
                <?php if ($isEdit): ?>
                    <span class="kae-status kae-status-<?= e($vehicle['status']) ?> ms-2"><?= e($vehicle['status']) ?></span>
                <?php endif; ?>
            </h1>
            <?php if ($isEdit): ?>
                <p class="text-muted small mb-0">
                    Slug: <code><?= e((string) $vehicle['slug']) ?></code> ·
                    Created <?= e(date('M j, Y', strtotime((string) $vehicle['created_at']))) ?>
                </p>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/vehicles" class="btn btn-link btn-sm">← Back</a>
            <?php if ($isEdit): ?>
                <a href="/<?= e(config('locales.default')) ?>/vehicles/<?= e((string) $vehicle['slug']) ?>"
                   target="_blank" class="btn btn-outline-dark btn-sm">View public ↗</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (! empty($errors['_global'][0])): ?>
        <div class="alert alert-danger"><?= e($errors['_global'][0]) ?></div>
    <?php endif; ?>
    <?php if (! empty($errors) && empty($errors['_global'])): ?>
        <div class="alert alert-warning">Please fix the highlighted errors below.</div>
    <?php endif; ?>

    <ul class="nav nav-tabs kae-admin-tabs" id="vForm-tabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info">Info</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-trans">Translations</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-media" id="media-tab">Media</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-insp">Inspection</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-seo">SEO</button></li>
    </ul>

    <form method="POST" action="<?= e($action) ?>" novalidate>
        <?= csrf_field() ?>
        <?php if ($isEdit): ?><?= method_field('PUT') ?><?php endif; ?>

        <div class="tab-content">
            <!-- ============ INFO ============ -->
            <div class="tab-pane fade show active" id="tab-info">
                <div class="kae-form-section">
                    <h2>Vehicle basics</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="brand_id">Brand *</label>
                            <select name="brand_id" id="brand_id" class="form-select <?= isset($errors['brand_id'])?'is-invalid':'' ?>" required>
                                <option value="">—</option>
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= (int) $b['id'] ?>" <?= (int) $val('brand_id') === (int) $b['id'] ? 'selected' : '' ?>>
                                        <?= e((string) $b['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="model_id">Model *</label>
                            <select name="model_id" id="model_id" class="form-select <?= isset($errors['model_id'])?'is-invalid':'' ?>" required>
                                <option value="">—</option>
                                <?php foreach ($models as $m): ?>
                                    <option value="<?= (int) $m['id'] ?>" data-brand="<?= (int) $m['brand_id'] ?>"
                                            <?= (int) $val('model_id') === (int) $m['id'] ? 'selected' : '' ?>>
                                        <?= e((string) $m['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="year">Year *</label>
                            <input type="number" min="1990" max="2030" name="year" id="year"
                                   value="<?= e((string) $val('year')) ?>"
                                   class="form-control <?= isset($errors['year'])?'is-invalid':'' ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="mileage_km">Mileage (km) *</label>
                            <input type="number" min="0" name="mileage_km" id="mileage_km"
                                   value="<?= e((string) $val('mileage_km')) ?>"
                                   class="form-control <?= isset($errors['mileage_km'])?'is-invalid':'' ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="vin">VIN</label>
                            <input type="text" name="vin" id="vin" maxlength="40"
                                   value="<?= e((string) $val('vin')) ?>" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="body_type_id">Body type</label>
                            <select name="body_type_id" id="body_type_id" class="form-select">
                                <option value="">—</option>
                                <?php foreach ($body_types as $bt): ?>
                                    <option value="<?= (int) $bt['id'] ?>" <?= (int) $val('body_type_id') === (int) $bt['id'] ? 'selected' : '' ?>>
                                        <?= e((string) $bt['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="fuel_type">Fuel *</label>
                            <select name="fuel_type" id="fuel_type" class="form-select <?= isset($errors['fuel_type'])?'is-invalid':'' ?>" required>
                                <?php foreach ($fuel_types as $ft): ?>
                                    <option value="<?= e($ft) ?>" <?= $val('fuel_type') === $ft ? 'selected' : '' ?>><?= e($ft) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="transmission">Transmission *</label>
                            <select name="transmission" id="transmission" class="form-select <?= isset($errors['transmission'])?'is-invalid':'' ?>" required>
                                <?php foreach ($transmissions as $tr): ?>
                                    <option value="<?= e($tr) ?>" <?= $val('transmission') === $tr ? 'selected' : '' ?>><?= e($tr) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="drivetrain">Drivetrain</label>
                            <select name="drivetrain" id="drivetrain" class="form-select">
                                <?php foreach ($drivetrains as $dt): ?>
                                    <option value="<?= e($dt) ?>" <?= ($val('drivetrain') ?: 'fwd') === $dt ? 'selected' : '' ?>><?= e($dt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="status">Status *</label>
                            <select name="status" id="status" class="form-select <?= isset($errors['status'])?'is-invalid':'' ?>" required>
                                <?php foreach ($statuses as $s): ?>
                                    <option value="<?= e($s) ?>" <?= $val('status') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="engine_cc">Engine (cc)</label>
                            <input type="number" name="engine_cc" id="engine_cc" value="<?= e((string) $val('engine_cc')) ?>" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="engine_power_hp">Power (hp)</label>
                            <input type="number" name="engine_power_hp" id="engine_power_hp" value="<?= e((string) $val('engine_power_hp')) ?>" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="doors">Doors</label>
                            <input type="number" min="2" max="6" name="doors" id="doors" value="<?= e((string) $val('doors')) ?>" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="seats">Seats</label>
                            <input type="number" min="2" max="9" name="seats" id="seats" value="<?= e((string) $val('seats')) ?>" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="exterior_color">Exterior color</label>
                            <input type="text" name="exterior_color" id="exterior_color" value="<?= e((string) $val('exterior_color')) ?>" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="interior_color">Interior color</label>
                            <input type="text" name="interior_color" id="interior_color" value="<?= e((string) $val('interior_color')) ?>" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="location">Location</label>
                            <input type="text" name="location" id="location" value="<?= e((string) $val('location')) ?>" class="form-control" placeholder="Busan, Korea">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="origin_country">Origin country</label>
                            <input type="text" name="origin_country" id="origin_country" value="<?= e((string) ($val('origin_country') ?: 'South Korea')) ?>" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="kae-form-section">
                    <h2>Pricing & visibility</h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="price_usd">Price (USD) *</label>
                            <input type="number" min="0" step="1" name="price_usd" id="price_usd"
                                   value="<?= e((string) $val('price_usd')) ?>"
                                   class="form-control <?= isset($errors['price_usd'])?'is-invalid':'' ?>" required>
                        </div>
                        <div class="col-md-4 align-self-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1"
                                       <?= ! empty($val('is_featured')) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_featured">Featured on homepage</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ TRANSLATIONS ============ -->
            <div class="tab-pane fade" id="tab-trans">
                <ul class="nav nav-pills mb-3" role="tablist">
                    <?php foreach ($locales as $i => $loc): ?>
                        <li class="nav-item">
                            <button class="nav-link <?= $i===0?'active':'' ?>" data-bs-toggle="pill" data-bs-target="#trans-<?= e($loc) ?>"><?= strtoupper($loc) ?></button>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="tab-content">
                    <?php foreach ($locales as $i => $loc): ?>
                        <div class="tab-pane fade <?= $i===0?'show active':'' ?>" id="trans-<?= e($loc) ?>">
                            <div class="kae-form-section">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label" for="title_<?= e($loc) ?>">Title (<?= strtoupper($loc) ?>) <?= $loc==='en' ? '*' : '' ?></label>
                                        <input type="text" name="title_<?= e($loc) ?>" id="title_<?= e($loc) ?>"
                                               value="<?= e((string) $val('title_' . $loc)) ?>"
                                               class="form-control <?= isset($errors['title_'.$loc])?'is-invalid':'' ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="description_<?= e($loc) ?>">Description</label>
                                        <textarea name="description_<?= e($loc) ?>" id="description_<?= e($loc) ?>" rows="5"
                                                  class="form-control"><?= e((string) $val('description_' . $loc)) ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ============ MEDIA ============ -->
            <div class="tab-pane fade" id="tab-media">
                <div class="kae-form-section">
                    <h2>Photos</h2>
                    <?php if (! $isEdit): ?>
                        <p class="text-muted">Save the vehicle first, then upload images here.</p>
                    <?php else: ?>
                        <div id="kae-image-uploader" data-vehicle-id="<?= (int) $vehicle['id'] ?>" data-csrf="<?= e(csrf_token()) ?>">
                            <label class="kae-uploader d-block">
                                <div>📁 Click or drop images here (jpeg / png / webp, up to 12 MB each)</div>
                                <input type="file" name="image" accept="image/*" multiple>
                            </label>
                            <div class="kae-thumb-grid mt-3" id="kae-thumb-grid">
                                <?php foreach ($images as $img): ?>
                                    <div class="kae-thumb <?= ! empty($img['is_cover']) ? 'is-cover' : '' ?>" data-image-id="<?= (int) $img['id'] ?>">
                                        <img src="<?= e(image_url((string) $img['path'])) ?>" alt="">
                                        <?php if (! empty($img['is_cover'])): ?>
                                            <span class="kae-thumb-cover-badge">cover</span>
                                        <?php endif; ?>
                                        <div class="kae-thumb-actions">
                                            <button type="button" data-action="set-cover" title="Set as cover">★</button>
                                            <button type="button" data-action="delete" title="Delete">✕</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <script src="<?= e(asset('js/admin-vehicle.js')) ?>" defer></script>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ============ INSPECTION ============ -->
            <div class="tab-pane fade" id="tab-insp">
                <div class="kae-form-section">
                    <h2>Inspection report</h2>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label" for="overall_score">Overall (0–100)</label>
                            <input type="number" min="0" max="100" name="overall_score" id="overall_score"
                                   value="<?= e((string) $val('overall_score')) ?>" class="form-control">
                        </div>
                        <?php foreach (['engine_score'=>'Engine','exterior_score'=>'Exterior','interior_score'=>'Interior',
                                       'tires_score'=>'Tires','brakes_score'=>'Brakes','electrical_score'=>'Electrical'] as $col => $label): ?>
                            <div class="col-md-2">
                                <label class="form-label" for="<?= $col ?>"><?= e($label) ?></label>
                                <input type="number" min="0" max="100" name="<?= $col ?>" id="<?= $col ?>"
                                       value="<?= e((string) $val($col)) ?>" class="form-control">
                            </div>
                        <?php endforeach; ?>
                        <div class="col-md-4">
                            <label class="form-label" for="accident_history">Accident history</label>
                            <select name="accident_history" id="accident_history" class="form-select">
                                <?php foreach ($accident as $a): ?>
                                    <option value="<?= e($a) ?>" <?= ($val('accident_history') ?: 'unknown') === $a ? 'selected' : '' ?>><?= e($a) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="inspector_name">Inspector</label>
                            <input type="text" name="inspector_name" id="inspector_name" value="<?= e((string) $val('inspector_name')) ?>" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="inspected_at">Inspected on</label>
                            <input type="date" name="inspected_at" id="inspected_at" value="<?= e((string) $val('inspected_at')) ?>" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ SEO ============ -->
            <div class="tab-pane fade" id="tab-seo">
                <?php foreach ($locales as $loc): ?>
                    <div class="kae-form-section">
                        <h2>SEO (<?= strtoupper($loc) ?>)</h2>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="meta_title_<?= e($loc) ?>">Meta title</label>
                                <input type="text" name="meta_title_<?= e($loc) ?>" id="meta_title_<?= e($loc) ?>"
                                       value="<?= e((string) $val('meta_title_' . $loc)) ?>" maxlength="220" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="meta_description_<?= e($loc) ?>">Meta description</label>
                                <textarea name="meta_description_<?= e($loc) ?>" id="meta_description_<?= e($loc) ?>" maxlength="320" rows="2"
                                          class="form-control"><?= e((string) $val('meta_description_' . $loc)) ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="d-flex gap-2 align-items-center sticky-bottom bg-white border-top pt-3 pb-3">
            <button type="submit" class="btn btn-primary">Save vehicle</button>
            <a href="/admin/vehicles" class="btn btn-link">Cancel</a>
            <?php if ($isEdit): ?>
                <div class="ms-auto">
                    <button type="button" class="btn btn-outline-warning btn-sm"
                            onclick="if(confirm('Archive this vehicle? Public pages will return 404.')){ document.getElementById('archive-form').submit(); }">
                        Archive
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($isEdit): ?>
        <form method="POST" action="/admin/vehicles/<?= (int) $vehicle['id'] ?>" id="archive-form" class="d-none">
            <?= csrf_field() ?>
            <?= method_field('DELETE') ?>
        </form>
    <?php endif; ?>
</div>

<script>
// Brand → Model dependent cascade
(function () {
    const brand = document.getElementById('brand_id');
    const model = document.getElementById('model_id');
    if (!brand || !model) return;
    const opts = Array.from(model.querySelectorAll('option[data-brand]'));
    function refresh() {
        const b = brand.value;
        opts.forEach(o => o.hidden = b !== '' && o.dataset.brand !== b);
    }
    brand.addEventListener('change', () => { model.value = ''; refresh(); });
    refresh();
})();
</script>
<?php $this->endSection(); ?>
