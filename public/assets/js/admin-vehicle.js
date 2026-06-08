/* =============================================================
   Admin vehicle edit — drag-drop image uploader + cover + delete
   ============================================================= */
(function () {
    'use strict';
    const root = document.getElementById('kae-image-uploader');
    if (!root) return;

    const vid     = root.dataset.vehicleId;
    const csrf    = root.dataset.csrf;
    const dropEl  = root.querySelector('.kae-uploader');
    const fileEl  = root.querySelector('input[type="file"]');
    const grid    = document.getElementById('kae-thumb-grid');

    // ----- Drag & drop -----
    ['dragenter','dragover'].forEach(ev =>
        dropEl.addEventListener(ev, e => { e.preventDefault(); dropEl.classList.add('is-active'); })
    );
    ['dragleave','drop'].forEach(ev =>
        dropEl.addEventListener(ev, e => { e.preventDefault(); dropEl.classList.remove('is-active'); })
    );
    dropEl.addEventListener('drop', e => uploadAll(e.dataTransfer?.files || []));
    fileEl.addEventListener('change', e => uploadAll(e.target.files || []));

    async function uploadAll(files) {
        for (const file of files) {
            await uploadOne(file);
        }
        fileEl.value = '';
    }

    async function uploadOne(file) {
        if (!file.type.startsWith('image/')) {
            alert('Skipping non-image: ' + file.name);
            return;
        }
        const placeholder = document.createElement('div');
        placeholder.className = 'kae-thumb';
        placeholder.style.opacity = '0.4';
        placeholder.innerHTML = '<div class="d-flex h-100 align-items-center justify-content-center text-muted small">uploading…</div>';
        grid.appendChild(placeholder);

        try {
            const fd = new FormData();
            fd.append('image', file);
            fd.append('_csrf', csrf);
            const res = await fetch(`/admin/vehicles/${vid}/images`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrf },
                body: fd,
            });
            if (!res.ok) {
                const err = await res.json().catch(() => ({ error: 'Upload failed' }));
                placeholder.remove();
                alert('Upload failed: ' + (err.error || res.status));
                return;
            }
            const data = await res.json();
            placeholder.dataset.imageId = data.id;
            placeholder.style.opacity = '1';
            placeholder.innerHTML = `
                <img src="${data.thumb_url}" alt="">
                <div class="kae-thumb-actions">
                    <button type="button" data-action="set-cover" title="Set as cover">★</button>
                    <button type="button" data-action="delete" title="Delete">✕</button>
                </div>
            `;
        } catch (e) {
            placeholder.remove();
            alert('Upload error: ' + e.message);
        }
    }

    // ----- Thumb actions: delegated -----
    grid.addEventListener('click', async e => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const thumb = btn.closest('.kae-thumb');
        const id = thumb?.dataset.imageId;
        if (!id) return;

        if (btn.dataset.action === 'set-cover') {
            const res = await fetch(`/admin/vehicles/${vid}/images/cover`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrf,
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `_csrf=${encodeURIComponent(csrf)}&image_id=${encodeURIComponent(id)}`,
            });
            if (res.ok) {
                grid.querySelectorAll('.kae-thumb').forEach(t => {
                    t.classList.toggle('is-cover', t === thumb);
                    const badge = t.querySelector('.kae-thumb-cover-badge');
                    if (t === thumb && !badge) {
                        const b = document.createElement('span');
                        b.className = 'kae-thumb-cover-badge';
                        b.textContent = 'cover';
                        t.appendChild(b);
                    } else if (t !== thumb && badge) {
                        badge.remove();
                    }
                });
            } else {
                alert('Failed to set cover');
            }
        }

        if (btn.dataset.action === 'delete') {
            if (!confirm('Delete this image?')) return;
            const res = await fetch(`/admin/vehicles/images/${id}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrf,
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `_csrf=${encodeURIComponent(csrf)}&_method=DELETE`,
            });
            if (res.ok) {
                thumb.remove();
            } else {
                alert('Delete failed');
            }
        }
    });
})();
