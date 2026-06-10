<?php
/** @var \App\Core\View $this  @var array $current_user */
$path = $_SERVER['REQUEST_URI'] ?? '/admin';
$active = fn(string $p) => str_starts_with($path, $p) ? 'is-active' : '';
?>
<aside class="kae-admin-sidebar">
    <a href="/admin" class="kae-admin-brand">
        <img src="<?= asset('img/logo-light.svg') ?>" alt="ADY Motors" width="200" height="48">
    </a>

    <nav class="kae-admin-nav" aria-label="primary">
        <a class="kae-admin-nav-link <?= $active('/admin') === 'is-active' && $path === '/admin' ? 'is-active' : '' ?>" href="/admin">
            <span class="kae-admin-nav-icon">▦</span> Dashboard
        </a>
        <a class="kae-admin-nav-link <?= $active('/admin/vehicles') ?>" href="/admin/vehicles">
            <span class="kae-admin-nav-icon">🚗</span> Vehicles
        </a>
        <a class="kae-admin-nav-link <?= $active('/admin/leads') ?>" href="/admin/leads">
            <span class="kae-admin-nav-icon">📨</span> Leads
        </a>
        <a class="kae-admin-nav-link <?= $active('/admin/reservations') ?>" href="/admin/reservations">
            <span class="kae-admin-nav-icon">📌</span> Reservations
        </a>
        <a class="kae-admin-nav-link <?= $active('/admin/testimonials') ?>" href="/admin/testimonials">
            <span class="kae-admin-nav-icon">★</span> Testimonials
        </a>
        <a class="kae-admin-nav-link <?= $active('/admin/regulations') ?>" href="/admin/regulations">
            <span class="kae-admin-nav-icon">📖</span> Regulations
        </a>
        <a class="kae-admin-nav-link <?= $active('/admin/settings') ?>" href="/admin/settings">
            <span class="kae-admin-nav-icon">⚙</span> Settings
        </a>
    </nav>

    <div class="kae-admin-sidebar-footer">
        <a href="/" target="_blank" class="kae-admin-link-out">
            View public site ↗
        </a>
    </div>
</aside>
