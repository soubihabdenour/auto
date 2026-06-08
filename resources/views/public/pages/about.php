<?php
/** @var \App\Core\View $this */
$this->extends('layouts/public');
$values = t_arr('pages.about.values');
?>
<?php $this->section('content'); ?>

<!-- Hero -->
<section class="kae-section kae-section--soft text-center">
    <div class="container">
        <span class="kae-eyebrow"><?= e(t('pages.about.eyebrow') ?: 'About us') ?></span>
        <h1 class="kae-section-title"><?= e(t('pages.about.title')) ?></h1>
        <p class="kae-section-subtitle mx-auto"><?= e(t('pages.about.subtitle')) ?></p>
    </div>
</section>

<!-- Body -->
<section class="kae-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 lead text-muted">
                <p><?= e(t('pages.about.body')) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Values feature grid -->
<section class="kae-section kae-section--soft">
    <div class="container">
        <div class="text-center mb-5">
            <span class="kae-eyebrow"><?= e(t('pages.about.values_eyebrow') ?: 'What we stand for') ?></span>
            <h2 class="kae-section-title"><?= e(t('pages.about.values_title')) ?></h2>
        </div>
        <div class="kae-feature-grid">
            <?php foreach ($values as $v): ?>
                <div class="kae-feature">
                    <h3 class="kae-feature-title"><?= e((string) ($v['title'] ?? '')) ?></h3>
                    <p class="kae-feature-body"><?= e((string) ($v['body'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php $this->endSection(); ?>
