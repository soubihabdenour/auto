<?php
/** @var \App\Core\View $this  @var string $locale  @var array $available_locales */

$nativeNames = (array) config('locales.native', []);
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
// Strip current locale prefix so we can swap it
$stripped = preg_replace('#^/(' . implode('|', $available_locales) . ')(/|$)#', '/', (string) $currentPath);
$stripped = $stripped === '' ? '/' : $stripped;
?>
<div class="dropdown kae-lang-switcher">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
            data-bs-toggle="dropdown" aria-expanded="false">
        <?= e($nativeNames[$locale] ?? strtoupper($locale)) ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <?php foreach ($available_locales as $code): ?>
            <?php $target = rtrim('/' . $code . rtrim($stripped, '/'), '/') ?: '/'; ?>
            <li>
                <a class="dropdown-item <?= $code === $locale ? 'active' : '' ?>"
                   href="<?= e($target) ?>">
                    <?= e($nativeNames[$code] ?? strtoupper($code)) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
