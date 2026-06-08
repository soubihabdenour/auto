<?php
/** @var \App\Core\View $this */
$this->extends('layouts/public');
$sections = t_arr('pages.privacy.sections');
?>
<?php $this->section('content'); ?>
<section class="kae-section kae-section--soft text-center">
    <div class="container">
        <span class="kae-eyebrow"><?= e(t('pages.privacy.eyebrow')) ?></span>
        <h1 class="kae-section-title"><?= e(t('pages.privacy.title')) ?></h1>
        <p class="kae-section-subtitle mx-auto">
            <?= e(t('pages.privacy.last_updated', ['date' => '2026-06-08'])) ?>
        </p>
    </div>
</section>

<section class="kae-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php foreach ($sections as $i => $sec): ?>
                    <div class="mb-5">
                        <h2 class="h4 fw-bold mb-3"><?= ($i + 1) . '. ' . e((string) ($sec['title'] ?? '')) ?></h2>
                        <p class="text-muted lead fs-6 mb-0" style="white-space: pre-line"><?= e((string) ($sec['body'] ?? '')) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php $this->endSection(); ?>
