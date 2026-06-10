/**
 * ADY Motors — motion primitives (Phase 1 foundation).
 *
 * Zero dependencies, < 2 KB minified. Two responsibilities:
 *   1. Toggle .is-scrolled on the public header once the user scrolls
 *      past a small threshold — drives the subtle backdrop / border.
 *   2. Reveal elements with [data-reveal] as they enter the viewport,
 *      one-shot. Supports stagger via [data-reveal-delay="120"] in ms.
 *
 * Respects (prefers-reduced-motion: reduce) by exiting early.
 */
(function () {
    'use strict';

    var prefersReduced = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- 1. Header scroll state ---------- */
    var header = document.querySelector('.kae-header');
    if (header) {
        var SCROLL_THRESHOLD = 8;
        var ticking = false;
        var update = function () {
            var scrolled = (window.scrollY || window.pageYOffset || 0) > SCROLL_THRESHOLD;
            header.classList.toggle('is-scrolled', scrolled);
            ticking = false;
        };
        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(update);
                ticking = true;
            }
        }, { passive: true });
        update();
    }

    /* ---------- 1b. Vehicle-detail sticky quick-bar ----------
     * Shows the floating dark bar once the page has scrolled past
     * the .kae-vd-hero. Uses IntersectionObserver on the hero — when
     * its bottom leaves the viewport, we reveal the bar.
     */
    var vdHero = document.querySelector('.kae-vd-hero');
    var vdBar  = document.querySelector('[data-kae-vd-stickybar]');
    if (vdHero && vdBar && typeof IntersectionObserver !== 'undefined') {
        var vdIo = new IntersectionObserver(function (entries) {
            for (var i = 0; i < entries.length; i++) {
                /* Show the bar when the hero is no longer intersecting (scrolled past it). */
                vdBar.classList.toggle('is-visible', !entries[i].isIntersecting);
                vdBar.setAttribute('aria-hidden', entries[i].isIntersecting ? 'true' : 'false');
            }
        }, { rootMargin: '-80px 0px 0px 0px', threshold: 0 });
        vdIo.observe(vdHero);
    }

    /* ---------- 2. Scroll reveal ---------- */
    var revealEls = document.querySelectorAll('[data-reveal]');
    if (!revealEls.length) return;

    // No IntersectionObserver (very old browsers) or reduced motion → reveal immediately.
    if (prefersReduced || typeof IntersectionObserver === 'undefined') {
        for (var i = 0; i < revealEls.length; i++) revealEls[i].classList.add('is-revealed');
        return;
    }

    var io = new IntersectionObserver(function (entries) {
        for (var i = 0; i < entries.length; i++) {
            var entry = entries[i];
            if (!entry.isIntersecting) continue;
            var el = entry.target;
            var delay = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
            if (delay > 0) el.style.setProperty('--reveal-delay', delay + 'ms');
            // Defer the class flip a frame so the transition-delay applies cleanly.
            window.requestAnimationFrame(function (e) {
                return function () { e.classList.add('is-revealed'); };
            }(el));
            io.unobserve(el);
        }
    }, {
        // Reveal slightly before fully in view — feels more cinematic.
        rootMargin: '0px 0px -10% 0px',
        threshold: 0.05,
    });

    for (var j = 0; j < revealEls.length; j++) io.observe(revealEls[j]);

    /* ---------- 3. Number counter (data-count="123") ----------
     * Counts up from 0 to the target the first time the element scrolls
     * into view. Respects reduced motion (just sets the final value).
     * Keeps any non-digit prefix/suffix from the original text intact
     * via data-count-prefix / data-count-suffix attributes.
     */
    var counters = document.querySelectorAll('[data-count]');
    if (counters.length === 0) return;

    var formatN = function (n, locale) {
        try { return new Intl.NumberFormat(locale || undefined).format(n); }
        catch (_) { return String(n); }
    };

    var animateCount = function (el) {
        var target = parseFloat(el.getAttribute('data-count') || '0');
        if (!isFinite(target)) target = 0;
        var prefix = el.getAttribute('data-count-prefix') || '';
        var suffix = el.getAttribute('data-count-suffix') || '';
        var duration = parseInt(el.getAttribute('data-count-duration') || '1400', 10);
        var locale = document.documentElement.lang || undefined;

        if (prefersReduced) {
            el.textContent = prefix + formatN(target, locale) + suffix;
            return;
        }

        var start = performance.now();
        var ease = function (t) { return 1 - Math.pow(1 - t, 3); }; /* cubic-out */
        var tick = function (now) {
            var p = Math.min(1, (now - start) / duration);
            var value = Math.round(ease(p) * target);
            el.textContent = prefix + formatN(value, locale) + suffix;
            if (p < 1) window.requestAnimationFrame(tick);
        };
        window.requestAnimationFrame(tick);
    };

    var counterIo = new IntersectionObserver(function (entries) {
        for (var i = 0; i < entries.length; i++) {
            if (entries[i].isIntersecting) {
                animateCount(entries[i].target);
                counterIo.unobserve(entries[i].target);
            }
        }
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.4 });

    for (var k = 0; k < counters.length; k++) counterIo.observe(counters[k]);
})();
