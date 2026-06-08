/* =============================================================
   Cookie consent — minimal, GDPR-style, locale-aware (text comes
   from the rendered partial).
   Storage: localStorage["kae_cookies"] = { necessary, analytics, ts }
   Side-effect: dispatches a "kae:consent" CustomEvent on document
   so analytics loaders can listen and decide to inject scripts.
   ============================================================= */
(function () {
    'use strict';

    var STORAGE_KEY = 'kae_cookies';
    var TTL_DAYS    = 180;

    var banner = document.getElementById('kae-cookie-banner');
    var modal  = document.getElementById('kae-cookie-modal');
    var analyticsToggle = document.getElementById('kae-cc-analytics');
    if (!banner) return;

    function read() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return null;
            var v = JSON.parse(raw);
            var ageDays = (Date.now() - (v.ts || 0)) / 86400000;
            if (ageDays > TTL_DAYS) return null;
            return v;
        } catch (e) { return null; }
    }

    function write(state) {
        state.ts = Date.now();
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch (e) {}
        dispatch(state);
    }

    function dispatch(state) {
        document.dispatchEvent(new CustomEvent('kae:consent', { detail: state }));
    }

    function hideBanner() { banner.hidden = true; }

    function showBanner() {
        banner.hidden = false;
        banner.classList.add('is-visible');
    }

    function applyToModal(state) {
        if (!analyticsToggle) return;
        analyticsToggle.checked = !!(state && state.analytics);
    }

    // Initial state
    var current = read();
    if (current === null) {
        showBanner();
    } else {
        dispatch(current);
    }
    applyToModal(current);

    // Delegated click handler
    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-cookie-action]');
        if (!t) return;
        var action = t.dataset.cookieAction;
        if (action === 'accept-all') {
            write({ necessary: true, analytics: true });
            hideBanner();
        } else if (action === 'reject') {
            write({ necessary: true, analytics: false });
            hideBanner();
        } else if (action === 'save') {
            write({
                necessary: true,
                analytics: !!(analyticsToggle && analyticsToggle.checked),
            });
            hideBanner();
            if (window.bootstrap && modal) {
                var inst = window.bootstrap.Modal.getInstance(modal);
                if (inst) inst.hide();
            }
        }
    });

    // When the customize/cookie-settings link opens the modal, pre-set the toggle
    if (modal) {
        modal.addEventListener('show.bs.modal', function () {
            applyToModal(read());
        });
    }

    // Expose for analytics scripts
    window.KAE_CONSENT = read;
})();
