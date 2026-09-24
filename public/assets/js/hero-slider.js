(function () {
    var slides = document.querySelectorAll('.hero__slide');
    if (slides.length < 2) {
        return;
    }
    var hero = document.querySelector('.hero');
    var interval = hero ? parseInt(hero.dataset.interval, 10) : NaN;
    if (!interval || interval < 500) {
        interval = 6000;
    }
    var current = 0;
    setInterval(function () {
        if (document.body.classList.contains('is-editing')) {
            return;
        }
        slides[current].classList.remove('is-active');
        current = (current + 1) % slides.length;
        slides[current].classList.add('is-active');
    }, interval);
})();
