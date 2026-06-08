<?php
/** @var \App\Core\View $this  @var array $current_user */
$flash = flash('flash');
?>
<header class="kae-admin-topbar">
    <div class="kae-admin-topbar-left">
        <?= e($current_user['name'] ?? 'Admin') ?>
        <span class="badge bg-secondary ms-2"><?= e($current_user['role'] ?? 'staff') ?></span>
    </div>
    <div class="kae-admin-topbar-right">
        <span class="text-muted small me-3"><?= e($current_user['email'] ?? '') ?></span>
        <form method="POST" action="/admin/logout" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-outline-secondary">Sign out</button>
        </form>
    </div>
</header>

<?php if ($flash): ?>
    <div class="container-fluid pt-3">
        <div class="alert alert-success mb-0"><?= e((string) $flash) ?></div>
    </div>
<?php endif; ?>
