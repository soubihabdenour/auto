<?php
/**
 * @var \App\Core\View $this
 * @var array|null $inspection
 * @var string $notes_lang  notes_ar | notes_fr | notes_en
 */
if ($inspection === null): ?>
    <div class="kae-inspection-empty p-3 bg-light rounded-3 text-center text-muted small">
        <?= e(t('vehicle.detail.inspection.missing')) ?>
    </div>
<?php return; endif;

$bar = static function (int|null $score): string {
    $score = max(0, min(100, (int) $score));
    $color = $score >= 80 ? 'var(--c-success, #1B8A5A)'
           : ($score >= 60 ? 'var(--c-warning, #C77A07)' : 'var(--c-danger, #C0392B)');
    return sprintf(
        '<div class="kae-bar"><span class="kae-bar-fill" style="width:%d%%;background:%s"></span></div>',
        $score,
        $color
    );
};

$axes = [
    'engine'     => 'vehicle.detail.inspection.engine',
    'exterior'   => 'vehicle.detail.inspection.exterior',
    'interior'   => 'vehicle.detail.inspection.interior',
    'tires'      => 'vehicle.detail.inspection.tires',
    'brakes'     => 'vehicle.detail.inspection.brakes',
    'electrical' => 'vehicle.detail.inspection.electrical',
];
?>
<div class="kae-inspection p-3 p-md-4 border rounded-3 bg-white">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h3 class="h5 fw-bold mb-1"><?= e(t('vehicle.detail.inspection.overall')) ?></h3>
            <?php if (! empty($inspection['inspector_name'])): ?>
                <small class="text-muted">
                    <?= e(t('vehicle.detail.inspection.inspector')) ?>:
                    <?= e((string) $inspection['inspector_name']) ?>
                    <?php if (! empty($inspection['inspected_at'])): ?>
                        · <?= e((string) $inspection['inspected_at']) ?>
                    <?php endif; ?>
                </small>
            <?php endif; ?>
        </div>
        <div class="kae-overall-score text-end">
            <span class="display-6 fw-bold"><?= (int) ($inspection['overall_score'] ?? 0) ?></span>
            <span class="text-muted">/100</span>
        </div>
    </div>

    <dl class="kae-axes mb-3">
        <?php foreach ($axes as $col => $label_key): ?>
            <?php $val = (int) ($inspection[$col . '_score'] ?? 0); ?>
            <dt class="text-muted small mb-1"><?= e(t($label_key)) ?></dt>
            <dd class="d-flex align-items-center gap-3 mb-2">
                <?= $bar($val) ?>
                <span class="fw-bold small" style="min-width:2.5rem;text-align:end"><?= $val ?></span>
            </dd>
        <?php endforeach; ?>
    </dl>

    <?php
        $acc = (string) ($inspection['accident_history'] ?? 'unknown');
        $accLabel = t('vehicle.detail.inspection.accident_' . $acc);
        $accClass = match ($acc) {
            'none'  => 'text-success',
            'minor' => 'text-warning',
            'major' => 'text-danger',
            default => 'text-muted',
        };
    ?>
    <div class="d-flex justify-content-between border-top pt-3">
        <span class="text-muted"><?= e(t('vehicle.detail.inspection.accident')) ?></span>
        <span class="fw-bold <?= e($accClass) ?>"><?= e($accLabel) ?></span>
    </div>

    <?php if (! empty($inspection[$notes_lang])): ?>
        <p class="text-muted small mt-3 mb-0"><?= e((string) $inspection[$notes_lang]) ?></p>
    <?php endif; ?>

    <?php if (! empty($inspection['report_pdf_path'])): ?>
        <a class="btn btn-outline-dark btn-sm mt-3" href="<?= e(image_url((string) $inspection['report_pdf_path'])) ?>" target="_blank" rel="noopener">
            📄 <?= e(t('vehicle.detail.inspection.download')) ?>
        </a>
    <?php endif; ?>
</div>
