/* =============================================================
   Vehicle listing — AJAX filter, sort, pagination, brand → model
   cascade. Falls back to a normal form GET if JS is disabled.
   ============================================================= */
(function () {
    'use strict';

    const form        = document.getElementById('kae-filter-form');
    const resultsBox  = document.getElementById('kae-results');
    const sortForm    = document.getElementById('kae-sort-form');
    const brandSelect = document.getElementById('f-brand');
    const modelSelect = document.getElementById('f-model');
    if (!form || !resultsBox) return;

    const endpoint = resultsBox.dataset.filterEndpoint;
    let pending = null;
    let debounceId = null;

    // --- 1. Brand → Model dependent dropdown ---------------------
    const allModelOptions = modelSelect
        ? Array.from(modelSelect.querySelectorAll('option[data-brand]'))
        : [];

    function refreshModels() {
        if (!brandSelect || !modelSelect) return;
        const brandId = brandSelect.value;
        modelSelect.value = ''; // reset selection when brand changes
        allModelOptions.forEach((opt) => {
            opt.hidden = brandId !== '' && opt.dataset.brand !== brandId;
        });
    }
    if (brandSelect) {
        brandSelect.addEventListener('change', () => {
            refreshModels();
            scheduleFetch();
        });
    }
    refreshModels();

    // --- 2. Fetch + render --------------------------------------
    function serializeForm() {
        const fd = new FormData(form);
        const params = new URLSearchParams();
        for (const [k, v] of fd.entries()) {
            if (v !== '' && v !== null) params.append(k, v);
        }
        return params;
    }

    function buildResultsUrl() {
        return endpoint + '?' + serializeForm().toString();
    }

    function buildListingUrl() {
        const params = serializeForm();
        const here = window.location.pathname;
        return here + (params.toString() ? '?' + params.toString() : '');
    }

    async function fetchResults(updateHistory = true) {
        if (pending) pending.abort();
        const ac = new AbortController();
        pending = ac;
        resultsBox.classList.add('is-loading');
        try {
            const res = await fetch(buildResultsUrl(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                signal: ac.signal,
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            resultsBox.innerHTML = data.html;
            if (updateHistory) {
                window.history.replaceState({}, '', buildListingUrl());
            }
        } catch (e) {
            if (e.name !== 'AbortError') console.error(e);
        } finally {
            resultsBox.classList.remove('is-loading');
            pending = null;
        }
    }

    function scheduleFetch(delay = 350) {
        clearTimeout(debounceId);
        debounceId = setTimeout(() => fetchResults(true), delay);
    }

    // --- 3. Wire inputs ----------------------------------------
    form.addEventListener('input', (e) => {
        if (e.target.matches('input[type="search"], input[type="number"]')) {
            scheduleFetch(450);
        }
    });
    form.addEventListener('change', (e) => {
        if (e.target.matches('select')) scheduleFetch(50);
    });
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        scheduleFetch(0);
    });

    // Sort form already submits via onchange — intercept for AJAX
    if (sortForm) {
        sortForm.addEventListener('submit', (e) => {
            e.preventDefault();
            // Pull sort value into the main form before re-fetching
            const sortVal = sortForm.querySelector('select[name="sort"]')?.value;
            if (sortVal) {
                let hidden = form.querySelector('input[name="sort"]');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'sort';
                    form.appendChild(hidden);
                }
                hidden.value = sortVal;
            }
            scheduleFetch(0);
        });
        const sortSelect = sortForm.querySelector('select[name="sort"]');
        if (sortSelect) {
            // Override the inline onchange="this.form.submit()" path with our AJAX one
            sortSelect.addEventListener('change', (e) => {
                e.preventDefault();
                sortForm.dispatchEvent(new Event('submit', { cancelable: true }));
            });
        }
    }

    // --- 4. Pagination via event delegation ---------------------
    resultsBox.addEventListener('click', (e) => {
        const link = e.target.closest('a.page-link[data-page]');
        if (!link) return;
        e.preventDefault();
        const page = link.dataset.page;
        let hidden = form.querySelector('input[name="page"]');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'page';
            form.appendChild(hidden);
        }
        hidden.value = page;
        fetchResults(true);
        window.scrollTo({ top: resultsBox.offsetTop - 80, behavior: 'smooth' });
    });

    // Reset page when any filter (other than page) changes
    form.addEventListener('change', () => {
        const hidden = form.querySelector('input[name="page"]');
        if (hidden) hidden.value = '1';
    });
})();
