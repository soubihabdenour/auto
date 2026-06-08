<?php
/** @var \App\Core\View $this */
$this->extends('layouts/public');
$steps = t_arr('pages.process.steps');
?>
<?php $this->section('content'); ?>

<!-- Hero -->
<section class="kae-section kae-section--soft text-center">
    <div class="container">
        <span class="kae-eyebrow"><?= e(t('pages.process.eyebrow') ?: 'Import process') ?></span>
        <h1 class="kae-section-title"><?= e(t('pages.process.title')) ?></h1>
        <p class="kae-section-subtitle mx-auto"><?= e(t('pages.process.subtitle')) ?></p>
    </div>
</section>

<!-- Six-step grid (numbered) -->
<section class="kae-section">
    <div class="container">
        <div class="kae-step-grid">
            <?php foreach ($steps as $i => $s): ?>
                <div class="kae-step">
                    <div class="kae-step-num"><?= e((string) ($s['n'] ?? (string) ($i + 1))) ?></div>
                    <h3 class="kae-step-title"><?= e((string) ($s['title'] ?? '')) ?></h3>
                    <p class="kae-step-body"><?= e((string) ($s['body'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?= e(locale_url('/vehicles')) ?>" class="btn btn-primary btn-lg">
                <?= e(t('pages.process.cta')) ?> →
            </a>
        </div>
    </div>
</section>
<?php $this->endSection(); ?>
