<?php
/**
 * @var \App\Core\View $this
 * @var array $images    list from vehicle_images, cover-first
 * @var string $alt_lang  'alt_ar' | 'alt_fr' | 'alt_en'
 * @var string $title
 */
$cover = $images[0] ?? null;
?>
<div class="kae-gallery" id="kae-gallery">
    <?php if ($cover === null): ?>
        <div class="kae-gallery-main kae-gallery-placeholder">
            <img src="<?= e(image_url(null)) ?>" alt="" class="img-fluid">
        </div>
        <p class="text-center text-muted small mt-2"><?= e(t('vehicle.detail.no_images')) ?></p>
    <?php else: ?>
        <div class="kae-gallery-main"
             data-current="0"
             data-total="<?= count($images) ?>">
            <button type="button" class="kae-gallery-arrow kae-gallery-arrow-prev" aria-label="prev">‹</button>
            <button type="button" class="kae-gallery-arrow kae-gallery-arrow-next" aria-label="next">›</button>
            <img id="kae-gallery-main-img"
                 src="<?= e(image_url((string) $cover['path'])) ?>"
                 alt="<?= e((string) ($cover[$alt_lang] ?? $title)) ?>"
                 class="img-fluid"
                 loading="eager"
                 decoding="async">
            <span class="kae-gallery-counter">
                <span id="kae-gallery-counter-current">1</span>/<?= count($images) ?>
            </span>
        </div>

        <?php if (count($images) > 1): ?>
            <ul class="kae-gallery-thumbs list-unstyled mt-3" role="tablist">
                <?php foreach ($images as $i => $img): ?>
                    <li>
                        <button type="button" role="tab"
                                class="kae-gallery-thumb <?= $i === 0 ? 'is-active' : '' ?>"
                                data-index="<?= $i ?>"
                                aria-label="image <?= $i + 1 ?>">
                            <img src="<?= e(image_url((string) $img['path'])) ?>"
                                 alt=""
                                 loading="lazy"
                                 decoding="async">
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- Lightbox -->
        <div class="kae-lightbox" id="kae-lightbox" hidden role="dialog" aria-modal="true">
            <button type="button" class="kae-lightbox-close" aria-label="close">×</button>
            <button type="button" class="kae-gallery-arrow kae-gallery-arrow-prev" aria-label="prev">‹</button>
            <button type="button" class="kae-gallery-arrow kae-gallery-arrow-next" aria-label="next">›</button>
            <img id="kae-lightbox-img" alt="">
            <span class="kae-gallery-counter">
                <span id="kae-lightbox-counter">1</span>/<?= count($images) ?>
            </span>
        </div>

        <script type="application/json" id="kae-gallery-data"><?=
            json_encode(array_map(
                fn ($img) => [
                    'src' => image_url((string) ($img['path'] ?? '')),
                    'alt' => (string) ($img[$alt_lang] ?? $title),
                ],
                $images
            ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>
</div>
