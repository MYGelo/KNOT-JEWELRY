document.addEventListener('DOMContentLoaded', function () {
    const slider = document.querySelector('.reviews-slider');

    if (slider && typeof Swiper !== 'undefined') {
        new Swiper(slider, {
            slidesPerView: 'auto',
            spaceBetween: 16,

            loop: true,
            centeredSlides: true,

            grabCursor: true,
            allowTouchMove: true,

            freeMode: {
                enabled: true,
                momentum: true,
                momentumRatio: 1,
                momentumVelocityRatio: 1,
            },

            autoplay: {
                delay: 0,
                disableOnInteraction: false,
            },

            speed: 3000,
        });
    }
});