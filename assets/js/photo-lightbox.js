/**
 * Photo lightbox — dependency-free popup viewer for photo galleries.
 *
 * Any <a href="full.jpg" data-lightbox="group"> opens in a full-screen popup with
 * a close (X) button and previous / next arrows. Anchors sharing the same
 * data-lightbox value form one gallery, in document order.
 *
 * Optional attributes on the anchor:
 *   data-title    first caption line (bold)
 *   data-caption  second caption line
 *
 * Controls: X button, Esc, click on the dark backdrop, ← / → keys,
 * on-screen arrows, swipe left/right on touch screens.
 */
(function () {
    'use strict';

    var CSS = [
        '.pl-overlay{position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,.92);display:none;align-items:center;justify-content:center;-webkit-user-select:none;user-select:none}',
        '.pl-overlay.pl-open{display:flex}',
        '.pl-stage{display:flex;flex-direction:column;align-items:center;max-width:100vw;max-height:100vh;padding:56px 72px 16px;box-sizing:border-box}',
        '.pl-img{display:block;max-width:100%;max-height:calc(100vh - 150px);object-fit:contain;border-radius:4px;box-shadow:0 8px 40px rgba(0,0,0,.6)}',
        '.pl-info{color:#fff;text-align:center;margin-top:12px;max-width:90vw;font-family:inherit}',
        '.pl-title{font-weight:600;font-size:1rem;line-height:1.3}',
        '.pl-caption{font-size:.875rem;opacity:.8;margin-top:2px}',
        '.pl-counter{font-size:.8rem;opacity:.7;margin-top:6px}',
        '.pl-btn{position:absolute;display:flex;align-items:center;justify-content:center;width:48px;height:48px;border:0;border-radius:50%;background:rgba(255,255,255,.16);color:#fff;font-size:26px;line-height:1;cursor:pointer;padding:0;transition:background .15s}',
        '.pl-btn:hover,.pl-btn:focus-visible{background:rgba(255,255,255,.32);outline:none}',
        '.pl-close{top:12px;right:12px;font-size:30px}',
        '.pl-prev,.pl-next{top:50%;transform:translateY(-50%)}',
        '.pl-prev{left:12px}',
        '.pl-next{right:12px}',
        '.pl-single .pl-prev,.pl-single .pl-next,.pl-single .pl-counter{display:none}',
        '@media (max-width:576px){.pl-stage{padding:56px 8px 12px}.pl-btn{width:42px;height:42px}.pl-prev{left:6px}.pl-next{right:6px}}',
        'body.pl-noscroll{overflow:hidden}'
    ].join('');

    var overlay, imgEl, titleEl, captionEl, counterEl, closeBtn, prevBtn, nextBtn;
    var items = [];
    var index = 0;
    var lastFocus = null;

    function build() {
        if (overlay) return;

        var style = document.createElement('style');
        style.textContent = CSS;
        document.head.appendChild(style);

        overlay = document.createElement('div');
        overlay.className = 'pl-overlay';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-label', 'Φωτογραφίες');
        overlay.innerHTML =
            '<button type="button" class="pl-btn pl-close" aria-label="Κλείσιμο" title="Κλείσιμο (Esc)">&times;</button>' +
            '<button type="button" class="pl-btn pl-prev" aria-label="Προηγούμενη" title="Προηγούμενη (←)">&#10094;</button>' +
            '<button type="button" class="pl-btn pl-next" aria-label="Επόμενη" title="Επόμενη (→)">&#10095;</button>' +
            '<div class="pl-stage">' +
                '<img class="pl-img" alt="">' +
                '<div class="pl-info">' +
                    '<div class="pl-title"></div>' +
                    '<div class="pl-caption"></div>' +
                    '<div class="pl-counter"></div>' +
                '</div>' +
            '</div>';
        document.body.appendChild(overlay);

        imgEl = overlay.querySelector('.pl-img');
        titleEl = overlay.querySelector('.pl-title');
        captionEl = overlay.querySelector('.pl-caption');
        counterEl = overlay.querySelector('.pl-counter');
        closeBtn = overlay.querySelector('.pl-close');
        prevBtn = overlay.querySelector('.pl-prev');
        nextBtn = overlay.querySelector('.pl-next');

        closeBtn.addEventListener('click', close);
        prevBtn.addEventListener('click', function (e) { e.stopPropagation(); step(-1); });
        nextBtn.addEventListener('click', function (e) { e.stopPropagation(); step(1); });

        // Click anywhere on the dark area (not on the image or a button) closes
        overlay.addEventListener('click', function (e) {
            if (e.target === imgEl || e.target.closest('.pl-btn')) return;
            close();
        });

        // Swipe left/right
        var touchX = null;
        overlay.addEventListener('touchstart', function (e) {
            touchX = e.touches.length === 1 ? e.touches[0].clientX : null;
        }, { passive: true });
        overlay.addEventListener('touchend', function (e) {
            if (touchX === null || !e.changedTouches.length) return;
            var dx = e.changedTouches[0].clientX - touchX;
            touchX = null;
            if (Math.abs(dx) > 50) step(dx < 0 ? 1 : -1);
        }, { passive: true });
    }

    function isOpen() {
        return overlay && overlay.classList.contains('pl-open');
    }

    function show(i) {
        index = (i + items.length) % items.length;
        var it = items[index];

        imgEl.src = it.href;
        imgEl.alt = it.title || '';
        titleEl.textContent = it.title;
        titleEl.style.display = it.title ? '' : 'none';
        captionEl.textContent = it.caption;
        captionEl.style.display = it.caption ? '' : 'none';
        counterEl.textContent = (index + 1) + ' / ' + items.length;

        // Preload neighbours so next/prev feel instant
        if (items.length > 1) {
            new Image().src = items[(index + 1) % items.length].href;
            new Image().src = items[(index - 1 + items.length) % items.length].href;
        }
    }

    function step(dir) {
        if (items.length > 1) show(index + dir);
    }

    function open(anchor) {
        var group = anchor.getAttribute('data-lightbox');
        var anchors = Array.prototype.filter.call(
            document.querySelectorAll('a[data-lightbox]'),
            function (a) { return a.getAttribute('data-lightbox') === group; }
        );
        items = anchors.map(function (a) {
            return {
                href: a.href,
                title: a.getAttribute('data-title') || '',
                caption: a.getAttribute('data-caption') || ''
            };
        });

        build();
        lastFocus = document.activeElement;
        overlay.classList.toggle('pl-single', items.length < 2);
        show(anchors.indexOf(anchor));
        overlay.classList.add('pl-open');
        document.body.classList.add('pl-noscroll');
        closeBtn.focus();
    }

    function close() {
        if (!isOpen()) return;
        overlay.classList.remove('pl-open');
        document.body.classList.remove('pl-noscroll');
        imgEl.removeAttribute('src');
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    document.addEventListener('click', function (e) {
        var a = e.target.closest ? e.target.closest('a[data-lightbox]') : null;
        if (!a) return;
        // Let ctrl/cmd/shift/middle-click open the image in a new tab as usual
        if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
        e.preventDefault();
        open(a);
    });

    document.addEventListener('keydown', function (e) {
        if (!isOpen()) return;
        if (e.key === 'Escape') { e.preventDefault(); close(); }
        else if (e.key === 'ArrowLeft') { e.preventDefault(); step(-1); }
        else if (e.key === 'ArrowRight') { e.preventDefault(); step(1); }
        else if (e.key === 'Tab') {
            // Keep focus inside the popup
            var focusable = [closeBtn, prevBtn, nextBtn].filter(function (b) { return b.offsetParent !== null; });
            var pos = focusable.indexOf(document.activeElement);
            e.preventDefault();
            focusable[(pos + (e.shiftKey ? -1 : 1) + focusable.length) % focusable.length].focus();
        }
    });
})();
