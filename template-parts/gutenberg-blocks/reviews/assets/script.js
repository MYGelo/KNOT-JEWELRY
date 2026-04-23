document.addEventListener('DOMContentLoaded', function () {
    const slider = document.querySelector('.reviews-slider');

    if (slider && typeof Swiper !== 'undefined') {
        new Swiper(slider, {
            slidesPerView: 'auto',
            spaceBetween: 16,
            autoplay: {
                delay: 4000,
            },
            freeMode: false,
            speed: 500,
            loop: true,
        });
    }
});