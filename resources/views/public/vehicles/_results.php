<?php
/**
 * @var \App\Core\View $this
 * @var array $results
 * @var int   $total
 * @var int   $pages
 * @var \App\Repositories\VehicleSearchCriteria $criteria
 */
?>
<?php if (empty($results)): ?>
    <div class="kae-empty text-center py-5">
        <p class="text-muted mb-3"><?= e(t('vehicle.list.no_results')) ?></p>
        <a href="<?= e(locale_url('/vehicles')) ?>" class="btn btn-outline-dark">
            <?= e(t('vehicle.list.reset')) ?>
        </a>
    </div>
<?php else: ?>
    <div class="row g-3 g-md-4">
        <?php foreach ($results as $vehicle): ?>
            <div class="col-12 col-sm-6 col-xl-4">
                <?= $this->partial('partials/vehicle-card', ['vehicle' => $vehicle]) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
        <nav class="kae-pagination mt-4" aria-label="pagination">
            <ul class="pagination justify-content-center">
                <?php
                $base = locale_url('/vehicles');
                $query = $criteria->toQueryArray();
                $pageUrl = static function (int $p) use ($base, $query): string {
                    $q = $query;
                    unset($q['page']);
                    if ($p > 1) $q['page'] = $p;
                    return $base . ($q !== [] ? '?' . http_build_query($q) : '');
                };
                $window = 2;
                $start  = max(1, $criteria->page - $window);
                $end    = min($pages, $criteria->page + $window);
                ?>

                <li class="page-item <?= $criteria->page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e($pageUrl(max(1, $criteria->page - 1))) ?>"
                       data-page="<?= max(1, $criteria->page - 1) ?>">«</a>
                </li>
                <?php if ($start > 1): ?>
                    <li class="page-item"><a class="page-link" href="<?= e($pageUrl(1)) ?>" data-page="1">1</a></li>
                    <?php if ($start > 2): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                <?php endif; ?>
                <?php for ($p = $start; $p <= $end; $p++): ?>
                    <li class="page-item <?= $p === $criteria->page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= e($pageUrl($p)) ?>" data-page="<?= $p ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($end < $pages): ?>
                    <?php if ($end < $pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a class="page-link" href="<?= e($pageUrl($pages)) ?>" data-page="<?= $pages ?>"><?= $pages ?></a></li>
                <?php endif; ?>
                <li class="page-item <?= $criteria->page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e($pageUrl(min($pages, $criteria->page + 1))) ?>"
                       data-page="<?= min($pages, $criteria->page + 1) ?>">»</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>
