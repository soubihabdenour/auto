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

<?= $this->partial('partials/page-hero', [
    'eyebrow'  => t('pages.contact.eyebrow') ?: 'Contact',
    'title'    => t('pages.contact.title'),
    'subtitle' => t('pages.contact.subtitle'),
    'crumbs'   => [
        ['label' => t('vehicle.detail.breadcrumb.home'), 'url' => locale_url('/')],
        ['label' => t('pages.contact.title'),            'url' => null],
    ],
]) ?>

<section class="kae-section">
    <div class="container">
        <div class="row g-4 g-lg-5 align-items-stretch">

            <!-- Dark info card (left) -->
            <div class="col-12 col-lg-5" data-reveal>
                <div class="kae-contact-info h-100">
                    <h2><?= e(t('pages.contact.info_title')) ?></h2>
                    <ul class="kae-contact-info-list">
                        <?php if (! empty($contact_phone)): ?>
                            <li>
                                <span class="kae-contact-icon">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                </span>
                                <a href="tel:<?= e(preg_replace('/\s+/', '', $contact_phone)) ?>"><?= e($contact_phone) ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if (! empty($whatsapp_number)): ?>
                            <li>
                                <span class="kae-contact-icon">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.5 3.5A11.4 11.4 0 0 0 12.3 0C5.9 0 .7 5.2.7 11.6c0 2.1.6 4.1 1.6 5.9L.6 24l6.7-1.8a11.6 11.6 0 0 0 5 1.2h.1c6.4 0 11.6-5.2 11.6-11.6 0-3.1-1.2-6-3.4-8.3zM12.3 21.4c-1.7 0-3.3-.4-4.7-1.3l-.3-.2-3.5.9.9-3.5-.2-.4a9.6 9.6 0 0 1-1.5-5.2C3 6.5 7.2 2.3 12.3 2.3c2.5 0 4.8 1 6.5 2.7a9.3 9.3 0 0 1 2.7 6.6c0 5.1-4.1 9.3-9.2 9.3z"/></svg>
                                </span>
                                <a href="https://wa.me/<?= e(\App\Services\Phone::forWhatsapp($whatsapp_number)) ?>" target="_blank" rel="noopener">WhatsApp</a>
                            </li>
                        <?php endif; ?>
                        <?php if (! empty($contact_email)): ?>
                            <li>
                                <span class="kae-contact-icon">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22 6 12 13 2 6"/></svg>
                                </span>
                                <a href="mailto:<?= e($contact_email) ?>"><?= e($contact_email) ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <div class="kae-contact-info-hours">
                        <div class="kae-contact-info-hours-label"><?= e(t('pages.contact.hours_title')) ?></div>
                        <div class="kae-contact-info-hours-value"><?= e(t('pages.contact.hours_value')) ?></div>
                    </div>
                </div>
            </div>

            <!-- Form card (right) -->
            <div class="col-12 col-lg-7" data-reveal data-reveal-delay="120">
                <div class="kae-contact-card h-100">
                    <h2 class="kae-section-title" style="font-size: var(--fs-xl);"><?= e(t('pages.contact.form_title')) ?></h2>

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

                        <div class="row g-3">
                            <?php foreach (['name','phone','whatsapp','email'] as $f): ?>
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="contact_<?= e($f) ?>">
                                        <?= e(t('pages.contact.fields.' . $f)) ?>
                                        <?php if (in_array($f, ['name','phone'], true)): ?> *<?php endif; ?>
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

                            <div class="col-12">
                                <label class="form-label" for="contact_message">
                                    <?= e(t('pages.contact.fields.message')) ?> *
                                </label>
                                <textarea name="message" id="contact_message" rows="5"
                                          class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>"><?= e((string) ($old['message'] ?? '')) ?></textarea>
                                <?php if (isset($errors['message'][0])): ?>
                                    <div class="invalid-feedback"><?= e($errors['message'][0]) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12 mt-2">
                                <button type="submit" class="btn btn-primary btn-lg w-100 w-md-auto">
                                    <?= e(t('pages.contact.submit')) ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $this->endSection(); ?>
