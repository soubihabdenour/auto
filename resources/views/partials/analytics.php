<?php
/**
 * Analytics loader — consent-gated.
 * Reads measurement IDs from settings; injects nothing when neither is set
 * or when the user hasn't accepted analytics cookies.
 *
 * Settings keys (all optional):
 *   analytics_plausible_domain   e.g. "koreaautoexport.dz"
 *   analytics_ga4_id             e.g. "G-XXXXXXXXXX"
 *   search_console_verification  e.g. "8q-xC8...AbCd"  (rendered as <meta>)
 */
try {
    $settings = app(\App\Services\Setting\SettingService::class);
    $plausible = (string) $settings->get('analytics_plausible_domain', '');
    $ga4       = (string) $settings->get('analytics_ga4_id', '');
    $sc        = (string) $settings->get('search_console_verification', '');
} catch (\Throwable) {
    return;
}
?>
<?php if ($sc !== ''): ?>
    <meta name="google-site-verification" content="<?= e($sc) ?>">
<?php endif; ?>

<?php if ($plausible !== '' || $ga4 !== ''): ?>
<script>
(function () {
    'use strict';
    var plausibleDomain = <?= json_encode($plausible) ?>;
    var ga4Id           = <?= json_encode($ga4) ?>;

    function load() {
        var consent = (window.KAE_CONSENT && window.KAE_CONSENT()) || null;
        if (!consent || !consent.analytics) return;
        if (window.__kae_analytics_loaded) return;
        window.__kae_analytics_loaded = true;

        if (plausibleDomain) {
            var p = document.createElement('script');
            p.defer = true;
            p.dataset.domain = plausibleDomain;
            p.src = 'https://plausible.io/js/script.js';
            document.head.appendChild(p);
        }

        if (ga4Id) {
            var g = document.createElement('script');
            g.async = true;
            g.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(ga4Id);
            document.head.appendChild(g);

            window.dataLayer = window.dataLayer || [];
            function gtag(){ dataLayer.push(arguments); }
            window.gtag = gtag;
            gtag('js', new Date());
            gtag('config', ga4Id, {
                anonymize_ip: true,
                allow_google_signals: false,
                allow_ad_personalization_signals: false
            });
        }
    }

    // Load now if consent already given; otherwise wait for the event
    if (window.KAE_CONSENT && window.KAE_CONSENT()) load();
    document.addEventListener('kae:consent', load);
})();
</script>
<?php endif; ?>
