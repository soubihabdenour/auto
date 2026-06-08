<?php
/** @var \App\Core\View $this  @var array $groups  @var array $values */
$this->extends('layouts/admin');
$errors = flash('_errors') ?? [];
?>
<?php $this->section('content'); ?>
<div class="container-fluid">
    <div class="kae-page-head">
        <div>
            <h1>Settings</h1>
            <p class="text-muted mb-0 small">Site-wide configuration. Changes apply immediately to the public site.</p>
        </div>
    </div>

    <?php if (! empty($errors['_global'][0])): ?>
        <div class="alert alert-danger"><?= e($errors['_global'][0]) ?></div>
    <?php endif; ?>

    <form method="POST" action="/admin/settings">
        <?= csrf_field() ?><?= method_field('PUT') ?>

        <?php foreach ($groups as $title => $keys): ?>
            <div class="kae-form-section">
                <h2><?= e($title) ?></h2>
                <div class="row g-3">
                    <?php foreach ($keys as $key): ?>
                        <?php
                        $current = $values[$key]['value'] ?? '';
                        $type    = $values[$key]['type']  ?? 'string';
                        $colSize = in_array($type, ['int','float','bool'], true) ? 'col-md-4' : 'col-md-6';
                        $isLong  = str_contains($key, 'message') || str_contains($key, 'tagline');
                        ?>
                        <div class="col-12 <?= $isLong ? '' : $colSize ?>">
                            <label class="form-label" for="s-<?= e($key) ?>">
                                <code class="text-muted small"><?= e($key) ?></code>
                            </label>
                            <?php if ($isLong): ?>
                                <textarea name="<?= e($key) ?>" id="s-<?= e($key) ?>" rows="2" class="form-control"><?= e((string) $current) ?></textarea>
                            <?php else: ?>
                                <input type="<?= in_array($type, ['int','float'], true) ? 'number' : 'text' ?>"
                                       <?= $type === 'float' ? 'step="0.01"' : '' ?>
                                       name="<?= e($key) ?>" id="s-<?= e($key) ?>"
                                       value="<?= e((string) $current) ?>"
                                       class="form-control">
                            <?php endif; ?>
                            <small class="text-muted">type: <?= e($type) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="d-flex gap-2 sticky-bottom bg-white border-top pt-3 pb-3">
            <button type="submit" class="btn btn-primary">Save settings</button>
            <a href="/admin" class="btn btn-link">Cancel</a>
        </div>
    </form>
</div>
<?php $this->endSection(); ?>
