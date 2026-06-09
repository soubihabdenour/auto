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
                <a href="/admin/proposals/vehicle/<?= (int) $vehicle['id'] ?>"
                   target="_blank" class="btn btn-primary btn-sm">📄 Proposal PDF</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (! empty($errors['_global'][0])): ?>
        <div class="alert alert-danger"><?= e($errors['_global'][0]) ?></div>
    <?php endif; ?>
    <?php if (! empty($errors) && empty($errors['_global'])): ?>
        <?php
        // Friendly label + tab map for each rule we validate against.
        $fieldLabels = [
            'brand_id'        => ['label' => 'Brand',          'tab' => 'tab-info'],
            'model_id'        => ['label' => 'Model',          'tab' => 'tab-info'],
            'year'            => ['label' => 'Year',           'tab' => 'tab-info'],
            'mileage_km'      => ['label' => 'Mileage (km)',   'tab' => 'tab-info'],
            'transmission'    => ['label' => 'Transmission',   'tab' => 'tab-info'],
            'fuel_type'       => ['label' => 'Fuel',           'tab' => 'tab-info'],
            'drivetrain'      => ['label' => 'Drivetrain',     'tab' => 'tab-info'],
            'price_usd'       => ['label' => 'Price (USD)',    'tab' => 'tab-info'],
            'status'          => ['label' => 'Status',         'tab' => 'tab-info'],
            'body_type_id'    => ['label' => 'Body type',      'tab' => 'tab-info'],
            'vin'             => ['label' => 'VIN',            'tab' => 'tab-info'],
            'engine_cc'       => ['label' => 'Engine (cc)',    'tab' => 'tab-info'],
            'engine_power_hp' => ['label' => 'Power (hp)',     'tab' => 'tab-info'],
            'exterior_color'  => ['label' => 'Exterior color', 'tab' => 'tab-info'],
            'interior_color'  => ['label' => 'Interior color', 'tab' => 'tab-info'],
            'doors'           => ['label' => 'Doors',          'tab' => 'tab-info'],
            'seats'           => ['label' => 'Seats',          'tab' => 'tab-info'],
            'location'        => ['label' => 'Location',       'tab' => 'tab-info'],
            'title_ar'        => ['label' => 'Title (AR)',     'tab' => 'tab-trans'],
            'title_fr'        => ['label' => 'Title (FR)',     'tab' => 'tab-trans'],
            'title_en'        => ['label' => 'Title (EN)',     'tab' => 'tab-trans'],
        ];
        ?>
        <div class="alert alert-warning">
            <strong>Please fix the highlighted errors below:</strong>
            <ul class="mb-0 mt-1">
                <?php foreach ($errors as $field => $messages): ?>
                    <?php
                    if ($field === '_global') continue;
                    $info = $fieldLabels[$field] ?? ['label' => $field, 'tab' => 'tab-info'];
                    $msg  = is_array($messages) ? ($messages[0] ?? 'Invalid value.') : (string) $messages;
                    ?>
                    <li>
                        <a href="#<?= e($info['tab']) ?>" data-kae-jump="<?= e($field) ?>" class="alert-link"><?= e($info['label']) ?></a>:
                        <?= e($msg) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
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
                            <div class="input-group">
                                <input type="text" name="vin" id="vin" maxlength="40"
                                       value="<?= e((string) $val('vin')) ?>" class="form-control"
                                       autocapitalize="characters" autocomplete="off">
                                <button type="button" class="btn btn-outline-dark" id="kae-vin-decode"
                                        title="Auto-fill from VIN (NHTSA)">Decode</button>
                            </div>
                            <small class="text-muted d-block mt-1" id="kae-vin-feedback"></small>
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
                                        <?php if ($loc === 'en'): ?>
                                            <div class="input-group">
                                                <input type="text" name="title_<?= e($loc) ?>" id="title_<?= e($loc) ?>"
                                                       value="<?= e((string) $val('title_' . $loc)) ?>"
                                                       class="form-control <?= isset($errors['title_'.$loc])?'is-invalid':'' ?>">
                                                <button type="button" class="btn btn-outline-dark" id="kae-title-autofill"
                                                        title="Generate from brand, model, year, fuel, drivetrain">Auto-fill</button>
                                            </div>
                                            <small class="text-muted d-block mt-1">Example: <em>Hyundai Tucson 2021 Petrol AWD</em></small>
                                        <?php else: ?>
                                            <input type="text" name="title_<?= e($loc) ?>" id="title_<?= e($loc) ?>"
                                                   value="<?= e((string) $val('title_' . $loc)) ?>"
                                                   class="form-control <?= isset($errors['title_'.$loc])?'is-invalid':'' ?>">
                                        <?php endif; ?>
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
// Auto-jump to the tab (and nested pill) containing the first invalid field
// on page load. Also wires the error-list links to do the same on click.
(function () {
    const activatePaneAncestors = (el) => {
        if (!el || !window.bootstrap?.Tab) return;
        const panes = [];
        let cur = el.closest('.tab-pane');
        while (cur) {
            panes.unshift(cur);
            cur = cur.parentElement?.closest('.tab-pane');
        }
        panes.forEach(pane => {
            const btn = document.querySelector(`[data-bs-target="#${pane.id}"]`);
            if (btn) bootstrap.Tab.getOrCreateInstance(btn).show();
        });
    };

    // 1. On load: jump to the first .is-invalid field.
    const firstInvalid = document.querySelector('.is-invalid');
    if (firstInvalid) {
        activatePaneAncestors(firstInvalid);
        try { firstInvalid.focus({ preventScroll: false }); } catch (_) {}
    }

    // 2. Click on an error-list link: jump to that field.
    document.querySelectorAll('[data-kae-jump]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const field = link.dataset.kaeJump;
            const target = document.getElementById(field) || document.querySelector(`[name="${field}"]`);
            if (target) {
                activatePaneAncestors(target);
                try { target.focus({ preventScroll: false }); } catch (_) {}
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
})();

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
    window.__kaeRefreshModelCascade = refresh;
})();

// English title builder — fills #title_en from brand/model/year/fuel/drivetrain.
// Pattern matches the demo titles: "Hyundai Tucson 2022 Diesel AWD".
// FWD is the default and gets omitted; everything else is shown.
function kaeBuildEnglishTitle() {
    const sel = (id) => {
        const el = document.getElementById(id);
        if (!el || el.value === '') return '';
        if (el.tagName === 'SELECT') {
            const opt = el.options[el.selectedIndex];
            return opt ? opt.text.trim() : '';
        }
        return el.value.trim();
    };
    const fuelLabels = {
        petrol: 'Petrol', diesel: 'Diesel', hybrid: 'Hybrid',
        phev: 'PHEV', electric: 'Electric', lpg: 'LPG',
    };
    const driveLabels = { rwd: 'RWD', awd: 'AWD', '4wd': '4WD' };

    const brand = sel('brand_id');
    const model = sel('model_id');
    const year  = document.getElementById('year')?.value?.trim() || '';
    const fuel  = document.getElementById('fuel_type')?.value || '';
    const drive = document.getElementById('drivetrain')?.value || '';

    if (!brand || !model || !year) return null;
    const parts = [brand, model, year];
    if (fuelLabels[fuel])  parts.push(fuelLabels[fuel]);
    if (driveLabels[drive]) parts.push(driveLabels[drive]);   // 'fwd' falls through and is skipped
    return parts.join(' ');
}

// Hook: Auto-fill button next to Title (EN).
(function () {
    const btn = document.getElementById('kae-title-autofill');
    const titleEn = document.getElementById('title_en');
    if (!btn || !titleEn) return;
    btn.addEventListener('click', () => {
        const t = kaeBuildEnglishTitle();
        if (!t) {
            alert('Pick a brand, model and year first.');
            return;
        }
        titleEn.value = t;
        titleEn.classList.remove('is-invalid');
        titleEn.dispatchEvent(new Event('input',  { bubbles: true }));
        titleEn.dispatchEvent(new Event('change', { bubbles: true }));
    });
})();

// VIN decoder (NHTSA vPIC via /admin/vehicles/decode-vin)
(function () {
    const btn      = document.getElementById('kae-vin-decode');
    const vinInput = document.getElementById('vin');
    const feedback = document.getElementById('kae-vin-feedback');
    if (!btn || !vinInput) return;
    const csrf = document.querySelector('input[name="_csrf"]')?.value || '';

    const setField = (id, value) => {
        if (value === null || value === undefined || value === '') return false;
        const el = document.getElementById(id);
        if (!el) return false;
        // Don't clobber non-empty fields the admin may have already filled.
        if (el.value && el.value.trim() !== '' && el.value !== '0') return false;
        el.value = String(value);
        el.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
    };

    const setSelect = (id, value) => {
        if (value === null || value === undefined || value === '') return false;
        const el = document.getElementById(id);
        if (!el) return false;
        if (el.value && el.value !== '') return false;
        const v = String(value);
        if (Array.from(el.options).some(o => o.value === v)) {
            el.value = v;
            el.dispatchEvent(new Event('change', { bubbles: true }));
            return true;
        }
        return false;
    };

    btn.addEventListener('click', async () => {
        const vin = (vinInput.value || '').trim();
        if (!vin) { feedback.textContent = 'Enter a VIN first.'; feedback.className = 'text-warning d-block mt-1'; return; }

        btn.disabled = true;
        const original = btn.textContent;
        btn.textContent = 'Decoding…';
        feedback.textContent = '';
        feedback.className   = 'text-muted d-block mt-1';

        try {
            const form = new FormData();
            form.append('_csrf', csrf);
            form.append('vin', vin);
            const res = await fetch('/admin/vehicles/decode-vin', {
                method: 'POST',
                body: form,
                credentials: 'same-origin',
            });
            const data = await res.json();
            if (!res.ok) {
                feedback.textContent = data.error || 'Decode failed.';
                feedback.className   = 'text-danger d-block mt-1';
                return;
            }

            const filled = [];
            if (setSelect('brand_id', data.brand_id))           filled.push('brand');
            // Brand change has refreshed the cascade; THEN set model.
            if (setSelect('model_id', data.model_id))           filled.push('model');
            if (setField('year',           data.year))          filled.push('year');
            if (setSelect('fuel_type',     data.fuel_type))     filled.push('fuel');
            if (setSelect('transmission',  data.transmission))  filled.push('transmission');
            if (setSelect('drivetrain',    data.drivetrain))    filled.push('drivetrain');
            if (setField('engine_cc',      data.engine_cc))     filled.push('engine cc');
            if (setField('engine_power_hp',data.engine_power_hp)) filled.push('hp');
            if (setField('doors',          data.doors))         filled.push('doors');
            if (setField('seats',          data.seats))         filled.push('seats');

            const warnings = [];
            if (data.raw_make && data.brand_id === null) {
                warnings.push(`Brand "${data.raw_make}" — not in your DB, add it under brands first.`);
            }
            if (data.raw_model && data.model_id === null && data.brand_id !== null) {
                warnings.push(`Model "${data.raw_model}" — not in your DB under that brand, add it under models first.`);
            }

            // If the EN title is still empty and we have enough data, generate one.
            const titleEn = document.getElementById('title_en');
            if (titleEn && titleEn.value.trim() === '') {
                const generated = kaeBuildEnglishTitle();
                if (generated) {
                    titleEn.value = generated;
                    titleEn.classList.remove('is-invalid');
                    titleEn.dispatchEvent(new Event('input',  { bubbles: true }));
                    titleEn.dispatchEvent(new Event('change', { bubbles: true }));
                    filled.push('title (EN)');
                }
            }

            const summary = filled.length
                ? `Filled: ${filled.join(', ')}.`
                : 'Nothing pre-fillable was returned.';
            feedback.textContent = warnings.length ? `${summary} ${warnings.join(' ')}` : summary;
            feedback.className   = warnings.length ? 'text-warning d-block mt-1' : 'text-success d-block mt-1';
        } catch (e) {
            feedback.textContent = 'Network error while decoding VIN.';
            feedback.className   = 'text-danger d-block mt-1';
        } finally {
            btn.disabled    = false;
            btn.textContent = original;
        }
    });
})();
</script>
<?php $this->endSection(); ?>
