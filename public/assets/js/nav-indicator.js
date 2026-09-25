(function () {
    var list = document.querySelector('.site-nav__links');
    var indicator = document.querySelector('.site-nav__indicator');
    if (!list || !indicator) {
        return;
    }

    // Browsers that support CSS Anchor Positioning size/place the indicator
    // declaratively (see .site-nav__indicator + anchor-name in style.css);
    // this script then only supplies the slide-in animation on page load
    // and lets go of its inline overrides so the anchor-computed position
    // takes over. Unsupported browsers keep the old JS-measured fallback.
    // Mirrors the CSS @supports condition exactly (anchor-name property AND
    // the anchor()/anchor-size() functions), so the two never disagree.
    var anchorSupported = !!(
        window.CSS &&
        CSS.supports &&
        CSS.supports('position-anchor', '--nav-active') &&
        CSS.supports('left', 'anchor(--nav-active left)') &&
        CSS.supports('width', 'anchor-size(--nav-active width)')
    );

    function positionOf(link) {
        return { left: link.offsetLeft, width: link.offsetWidth };
    }

    function place(pos, animate) {
        indicator.style.transition = animate ? '' : 'none';
        indicator.style.width = pos.width + 'px';
        indicator.style.left = pos.left + 'px';
    }

    function releaseToAnchor(animate) {
        indicator.style.transition = animate ? '' : 'none';
        indicator.style.width = '';
        indicator.style.left = '';
    }

    var active = list.querySelector('a.active');
    if (!active) {
        return;
    }

    var storageKey = 'siteNavIndicatorPos';
    var current = positionOf(active);
    var stored = null;
    try {
        stored = JSON.parse(sessionStorage.getItem(storageKey) || 'null');
    } catch (e) {
        stored = null;
    }

    if (stored) {
        place(stored, false);
        indicator.classList.add('is-ready');
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                // Always drive the slide-in with the JS-measured left/width
                // transition instead of releasing straight to anchor()
                // values here: some browsers (e.g. current Firefox) support
                // anchor-name/anchor() but don't animate a transition whose
                // end value comes from anchor(), so the indicator jumps
                // instantly instead of sliding. Anchor positioning is still
                // handed control right after, purely for later-resize
                // correctness, once the visible slide has finished.
                place(current, true);
                if (anchorSupported) {
                    indicator.addEventListener('transitionend', function handler(e) {
                        if (e.target !== indicator) {
                            return;
                        }
                        indicator.removeEventListener('transitionend', handler);
                        releaseToAnchor(false);
                    });
                }
            });
        });
    } else if (anchorSupported) {
        releaseToAnchor(false);
        indicator.classList.add('is-ready');
    } else {
        place(current, false);
        indicator.classList.add('is-ready');
    }

    try {
        sessionStorage.setItem(storageKey, JSON.stringify(current));
    } catch (e) {
        // sessionStorage unavailable (private mode) - indicator still renders, just without slide-in
    }

    list.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            try {
                sessionStorage.setItem(storageKey, JSON.stringify(positionOf(active)));
            } catch (e) {
                // ignore
            }
        });
    });

    if (!anchorSupported) {
        var resizeTimer = null;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                place(positionOf(active), false);
            }, 100);
        });
    }
})();
