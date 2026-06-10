<?php
/**
 * @var \App\Core\View $this
 * @var array  $errors
 * @var array  $old
 * @var string|null $flash
 */
$this->extends('layouts/admin');
?>
<?php $this->section('content'); ?>
<div class="kae-admin-login">
    <div class="kae-admin-login-card">
        <div class="text-center mb-4">
            <img src="<?= asset('img/logo.svg') ?>" alt="ADY Motors" width="220" height="50">
            <p class="text-muted small mt-3 mb-0">Admin sign-in</p>
        </div>

        <?php if (! empty($flash)): ?>
            <div class="alert alert-success"><?= e((string) $flash) ?></div>
        <?php endif; ?>
        <?php if (! empty($errors['_global'][0])): ?>
            <div class="alert alert-danger"><?= e($errors['_global'][0]) ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/login" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" name="email" id="email"
                       value="<?= e((string) ($old['email'] ?? '')) ?>"
                       class="form-control form-control-lg <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       autocomplete="username" autofocus required>
                <?php if (isset($errors['email'][0])): ?>
                    <div class="invalid-feedback"><?= e($errors['email'][0]) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" name="password" id="password"
                       class="form-control form-control-lg <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                       autocomplete="current-password" required>
                <?php if (isset($errors['password'][0])): ?>
                    <div class="invalid-feedback"><?= e($errors['password'][0]) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">Sign in</button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
            <a href="/" class="text-muted text-decoration-none">← Back to public site</a>
        </p>
    </div>
</div>
<?php $this->endSection(); ?>
