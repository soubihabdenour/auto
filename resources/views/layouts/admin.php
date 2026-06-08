<?php
/**
 * @var \App\Core\View $this
 * @var string|null $page_title
 * @var array|null $current_user
 */
?><!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0A1A2F">
    <meta name="robots" content="noindex,nofollow">
    <title><?= e($page_title ?? 'Admin · Korea Auto Export') ?></title>

    <link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg') ?>">

    <link rel="preload" as="font" type="font/woff2" crossorigin
          href="<?= asset('fonts/inter/Inter-Latin.woff2') ?>">
    <link rel="stylesheet" href="<?= asset('css/fonts.css') ?>">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">

    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">

    <?= $this->yield('head_extras') ?>
</head>
<body class="kae-admin">
<?php if (! empty($current_user)): ?>
    <div class="kae-admin-shell">
        <?= $this->partial('admin/_sidebar', ['current_user' => $current_user]) ?>
        <div class="kae-admin-main">
            <?= $this->partial('admin/_topbar', ['current_user' => $current_user]) ?>
            <main class="kae-admin-content py-4">
                <?= $this->yield('content') ?>
            </main>
        </div>
    </div>
<?php else: ?>
    <main class="kae-admin-auth">
        <?= $this->yield('content') ?>
    </main>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous" defer></script>
</body>
</html>
