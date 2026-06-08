/* =============================================================
   Vehicle detail page
   - Gallery: thumb swap, prev/next arrows, keyboard nav, lightbox
   - Lead modal: switch title between Inquiry / Quote / Reservation
   - WhatsApp click event: best-effort POST to /events/whatsapp
   ============================================================= */
(function () {
    'use strict';

    /* ----------- Gallery ----------- */
    const galleryRoot = document.getElementById('kae-gallery');
    if (galleryRoot) {
        const data = (() => {
            try {
                return JSON.parse(document.getElementById('kae-gallery-data')?.textContent || '[]');
            } catch { return []; }
        })();
        const mainImg     = document.getElementById('kae-gallery-main-img');
        const mainCounter = document.getElementById('kae-gallery-counter-current');
        const thumbs      = Array.from(galleryRoot.querySelectorAll('.kae-gallery-thumb'));
        const mainBox     = galleryRoot.querySelector('.kae-gallery-main');
        const lightbox    = document.getElementById('kae-lightbox');
        const lightboxImg = document.getElementById('kae-lightbox-img');
        const lightboxCnt = document.getElementById('kae-lightbox-counter');

        let current = 0;
        const total = data.length || thumbs.length || 1;

        function showIndex(i, opts = {}) {
            current = ((i % total) + total) % total;
            const item = data[current];
            if (mainImg && item) {
                mainImg.src = item.src;
                mainImg.alt = item.alt || '';
            }
            if (mainCounter) mainCounter.textContent = String(current + 1);
            thumbs.forEach((t, idx) => t.classList.toggle('is-active', idx === current));
            if (opts.openLightbox && lightbox) {
                lightboxImg.src = item.src;
                lightboxImg.alt = item.alt || '';
                if (lightboxCnt) lightboxCnt.textContent = String(current + 1);
                lightbox.hidden = false;
                document.body.style.overflow = 'hidden';
            } else if (lightbox && !lightbox.hidden) {
                lightboxImg.src = item.src;
                lightboxImg.alt = item.alt || '';
                if (lightboxCnt) lightboxCnt.textContent = String(current + 1);
            }
        }

        thumbs.forEach((t) => t.addEventListener('click', () => showIndex(parseInt(t.dataset.index, 10))));
        mainBox?.querySelector('.kae-gallery-arrow-prev')?.addEventListener('click', () => showIndex(current - 1));
        mainBox?.querySelector('.kae-gallery-arrow-next')?.addEventListener('click', () => showIndex(current + 1));
        mainImg?.addEventListener('click', () => showIndex(current, { openLightbox: true }));

        if (lightbox) {
            lightbox.querySelector('.kae-lightbox-close')?.addEventListener('click', () => closeLightbox());
            lightbox.querySelector('.kae-gallery-arrow-prev')?.addEventListener('click', () => showIndex(current - 1));
            lightbox.querySelector('.kae-gallery-arrow-next')?.addEventListener('click', () => showIndex(current + 1));
            lightbox.addEventListener('click', (e) => {
                if (e.target === lightbox) closeLightbox();
            });
            function closeLightbox() {
                lightbox.hidden = true;
                document.body.style.overflow = '';
            }
            document.addEventListener('keydown', (e) => {
                if (lightbox.hidden) return;
                if (e.key === 'Escape') closeLightbox();
                else if (e.key === 'ArrowLeft') showIndex(current - 1);
                else if (e.key === 'ArrowRight') showIndex(current + 1);
            });
        }

        // Keyboard nav on the main image when not in lightbox
        document.addEventListener('keydown', (e) => {
            if (lightbox && !lightbox.hidden) return;
            if (!['ArrowLeft', 'ArrowRight'].includes(e.key)) return;
            if (e.target && /INPUT|TEXTAREA|SELECT/.test(e.target.tagName)) return;
            if (e.key === 'ArrowLeft')  showIndex(current - 1);
            if (e.key === 'ArrowRight') showIndex(current + 1);
        });
    }

    /* ----------- Lead modal type swap ----------- */
    const leadModal = document.getElementById('kae-lead-modal');
    if (leadModal) {
        const titleEl = document.getElementById('kae-lead-modal-title');
        const typeIn  = document.getElementById('kae-lead-type');
        leadModal.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            if (!trigger) return;
            const leadType = trigger.dataset.leadType || 'inquiry';
            if (typeIn) typeIn.value = leadType;
            if (titleEl && trigger.textContent) {
                titleEl.textContent = trigger.textContent.trim();
            }
        });
    }

    /* ----------- WhatsApp click logging ----------- */
    document.querySelectorAll('[data-track-wa]').forEach((el) => {
        el.addEventListener('click', () => {
            const vid = el.getAttribute('data-track-wa');
            try {
                navigator.sendBeacon?.(
                    '/events/whatsapp',
                    new Blob([JSON.stringify({ vehicle_id: vid })], { type: 'application/json' })
                );
            } catch { /* best-effort */ }
        });
    });
})();
