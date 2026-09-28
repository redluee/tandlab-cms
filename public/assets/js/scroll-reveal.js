(function () {
    if (document.body.classList.contains('is-editing')) {
        return;
    }
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var bars = document.querySelectorAll('.tand-bar');
    if (!bars.length || reduceMotion || !('IntersectionObserver' in window)) {
        return;
    }
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });
    bars.forEach(function (bar) {
        bar.classList.add('js-reveal');
        observer.observe(bar);
    });
})();
