(function () {
    var list = document.querySelector('.site-nav__links');
    var indicator = document.querySelector('.site-nav__indicator');
    if (!list || !indicator) {
        return;
    }

    function positionOf(link) {
        return { left: link.offsetLeft, width: link.offsetWidth };
    }

    function place(pos, animate) {
        indicator.style.transition = animate ? '' : 'none';
        indicator.style.width = pos.width + 'px';
        indicator.style.transform = 'translateX(' + pos.left + 'px)';
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
                place(current, true);
            });
        });
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
                sessionStorage.setItem(storageKey, JSON.stringify(positionOf(link)));
            } catch (e) {
                // ignore
            }
        });
    });

    var resizeTimer = null;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            place(positionOf(active), false);
        }, 100);
    });
})();
