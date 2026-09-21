(function () {
    var slides = document.querySelectorAll('.hero__slide');
    if (slides.length < 2) {
        return;
    }
    var current = 0;
    setInterval(function () {
        slides[current].classList.remove('is-active');
        current = (current + 1) % slides.length;
        slides[current].classList.add('is-active');
    }, 6000);
})();
