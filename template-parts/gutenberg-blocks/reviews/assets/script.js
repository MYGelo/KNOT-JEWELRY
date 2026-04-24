document.addEventListener('DOMContentLoaded', function () {
    const slider = document.querySelector('.reviews-slider');

    if (slider && typeof Swiper !== 'undefined') {

        const swiper = new Swiper(slider, {
            slidesPerView: 'auto',
            spaceBetween: 16,

            loop: true,
            centeredSlides: true,

            grabCursor: true,
            allowTouchMove: true,

            freeMode: {
                enabled: true,
                momentum: true,
                momentumRatio: 1.2,
                momentumVelocityRatio: 1.2,
            },

            autoplay: {
                delay: 0,
                disableOnInteraction: false,
            },

            speed: 4000,
        });

        /* ---------- торможение ---------- */

        slider.addEventListener('pointerdown', () => {
            swiper.autoplay.stop();
        });

        /* ---------- разгон ---------- */

        slider.addEventListener('pointerup', () => {

            swiper.params.speed = 900;     // мягкая остановка
            swiper.slideToClosest();

            setTimeout(() => {

                swiper.params.speed = 4000; // разгон
                swiper.autoplay.start();

            }, 300);

        });

    }
});