<?php
/**
 * @var \App\Core\View $this
 * @var string|null $whatsapp_number
 * @var string|null $contact_email
 * @var string|null $contact_phone
 * @var array       $old
 * @var array       $errors
 * @var string|null $success
 */
$this->extends('layouts/public');
?>
<?php $this->section('content'); ?>
<section class="kae-section kae-section--soft text-center">
    <div class="container">
        <span class="kae-eyebrow"><?= e(t('pages.contact.eyebrow') ?: 'Contact') ?></span>
        <h1 class="kae-section-title"><?= e(t('pages.contact.title')) ?></h1>
        <p class="kae-section-subtitle mx-auto"><?= e(t('pages.contact.subtitle')) ?></p>
    </div>
</section>

<section class="container py-5">
    <div class="row g-5">
        <div class="col-12 col-md-5">
            <h2 class="h4 fw-bold mb-3"><?= e(t('pages.contact.info_title')) ?></h2>
            <ul class="list-unstyled text-muted">
                <?php if (! empty($contact_phone)): ?>
                    <li class="mb-2">📞 <a class="text-decoration-none text-reset" href="tel:<?= e(preg_replace('/\s+/', '', $contact_phone)) ?>"><?= e($contact_phone) ?></a></li>
                <?php endif; ?>
                <?php if (! empty($whatsapp_number)): ?>
                    <li class="mb-2">💬 <a class="text-decoration-none text-reset" href="https://wa.me/<?= e(\App\Services\Phone::forWhatsapp($whatsapp_number)) ?>" target="_blank" rel="noopener">WhatsApp</a></li>
                <?php endif; ?>
                <?php if (! empty($contact_email)): ?>
                    <li class="mb-2">✉ <a class="text-decoration-none text-reset" href="mailto:<?= e($contact_email) ?>"><?= e($contact_email) ?></a></li>
                <?php endif; ?>
            </ul>
            <h3 class="h6 fw-bold mt-4 mb-2"><?= e(t('pages.contact.hours_title')) ?></h3>
            <p class="text-muted"><?= e(t('pages.contact.hours_value')) ?></p>
        </div>

        <div class="col-12 col-md-7">
            <h2 class="h4 fw-bold mb-3"><?= e(t('pages.contact.form_title')) ?></h2>

            <?php if (! empty($success)): ?>
                <div class="alert alert-success"><?= e((string) $success) ?></div>
            <?php endif; ?>
            <?php if (! empty($errors['_global'][0])): ?>
                <div class="alert alert-danger"><?= e((string) $errors['_global'][0]) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= e(locale_url('/contact')) ?>" novalidate>
                <?= csrf_field() ?>
                <input type="text" name="_website" tabindex="-1" autocomplete="off"
                       style="position:absolute;left:-9999px;" aria-hidden="true">

                <?php foreach (['name','phone','whatsapp','email'] as $f): ?>
                    <div class="mb-3">
                        <label class="form-label" for="contact_<?= e($f) ?>">
                            <?= e(t('pages.contact.fields.' . $f)) ?>
                            <?php if (in_array($f, ['name','phone'], true)): ?><span class="text-danger">*</span><?php endif; ?>
                        </label>
                        <input type="<?= $f === 'email' ? 'email' : ($f === 'phone' || $f === 'whatsapp' ? 'tel' : 'text') ?>"
                               name="<?= e($f) ?>" id="contact_<?= e($f) ?>"
                               value="<?= e((string) ($old[$f] ?? '')) ?>"
                               class="form-control <?= isset($errors[$f]) ? 'is-invalid' : '' ?>">
                        <?php if (isset($errors[$f][0])): ?>
                            <div class="invalid-feedback"><?= e($errors[$f][0]) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="mb-3">
                    <label class="form-label" for="contact_message">
                        <?= e(t('pages.contact.fields.message')) ?> <span class="text-danger">*</span>
                    </label>
                    <textarea name="message" id="contact_message" rows="5"
                              class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>"><?= e((string) ($old['message'] ?? '')) ?></textarea>
                    <?php if (isset($errors['message'][0])): ?>
                        <div class="invalid-feedback"><?= e($errors['message'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">
                    <?= e(t('pages.contact.submit')) ?>
                </button>
            </form>
        </div>
    </div>
</section>
<?php $this->endSection(); ?>
