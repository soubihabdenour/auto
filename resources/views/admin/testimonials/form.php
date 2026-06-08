<?php
/** @var \App\Core\View $this  @var array|null $row  @var array $translations  @var array $old $errors $locales */
$this->extends('layouts/admin');
$isEdit = $row !== null;
$action = $isEdit ? '/admin/testimonials/' . (int) $row['id'] : '/admin/testimonials';
$v = fn (string $k, mixed $def='') => $old[$k] ?? $row[$k] ?? $def;
?>
<?php $this->section('content'); ?>
<div class="container-fluid">
    <div class="kae-page-head">
        <div><h1><?= $isEdit ? 'Edit testimonial' : 'New testimonial' ?></h1></div>
        <a href="/admin/testimonials" class="btn btn-link btn-sm">← Back</a>
    </div>

    <?php if (! empty($errors['_global'][0])): ?>
        <div class="alert alert-danger"><?= e($errors['_global'][0]) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= e($action) ?>">
        <?= csrf_field() ?><?php if ($isEdit): ?><?= method_field('PUT') ?><?php endif; ?>

        <div class="kae-form-section">
            <h2>Customer</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="customer_name">Name *</label>
                    <input type="text" name="customer_name" id="customer_name" value="<?= e((string) $v('customer_name')) ?>"
                           class="form-control <?= isset($errors['customer_name'])?'is-invalid':'' ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="customer_city">City</label>
                    <input type="text" name="customer_city" id="customer_city" value="<?= e((string) $v('customer_city')) ?>" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="vehicle_purchased">Vehicle purchased</label>
                    <input type="text" name="vehicle_purchased" id="vehicle_purchased" value="<?= e((string) $v('vehicle_purchased')) ?>" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="rating">Rating *</label>
                    <input type="number" min="1" max="5" name="rating" id="rating" value="<?= e((string) ($v('rating') ?: 5)) ?>" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="sort_order">Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="<?= e((string) ($v('sort_order') ?: 0)) ?>" class="form-control">
                </div>
                <div class="col-md-2 align-self-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1"
                               <?= ! empty($v('is_published')) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_published">Published</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="kae-form-section">
            <h2>Body translations</h2>
            <ul class="nav nav-pills mb-3">
                <?php foreach ($locales as $i => $loc): ?>
                    <li class="nav-item"><button type="button" class="nav-link <?= $i===0?'active':'' ?>" data-bs-toggle="pill" data-bs-target="#body-<?= e($loc) ?>"><?= strtoupper($loc) ?></button></li>
                <?php endforeach; ?>
            </ul>
            <div class="tab-content">
                <?php foreach ($locales as $i => $loc): ?>
                    <div class="tab-pane fade <?= $i===0?'show active':'' ?>" id="body-<?= e($loc) ?>">
                        <label class="form-label" for="body_<?= e($loc) ?>">Body (<?= strtoupper($loc) ?>) <?= $loc==='en'?'*':'' ?></label>
                        <textarea name="body_<?= e($loc) ?>" id="body_<?= e($loc) ?>" rows="4"
                                  class="form-control <?= isset($errors['body_'.$loc])?'is-invalid':'' ?>"><?= e((string) ($old['body_'.$loc] ?? $translations[$loc] ?? '')) ?></textarea>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="d-flex gap-2 sticky-bottom bg-white border-top pt-3 pb-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="/admin/testimonials" class="btn btn-link">Cancel</a>
            <?php if ($isEdit): ?>
                <div class="ms-auto">
                    <button type="button" class="btn btn-outline-danger btn-sm"
                            onclick="if(confirm('Delete this testimonial?')){ document.getElementById('del-form').submit(); }">
                        Delete
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($isEdit): ?>
        <form method="POST" action="/admin/testimonials/<?= (int) $row['id'] ?>" id="del-form" class="d-none">
            <?= csrf_field() ?><?= method_field('DELETE') ?>
        </form>
    <?php endif; ?>
</div>
<?php $this->endSection(); ?>
