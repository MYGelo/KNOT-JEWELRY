document.addEventListener('DOMContentLoaded', () => {

    if (document.querySelector('.comments-slider')) {

        window.commentsSwiper = new Swiper('.comments-slider', {
            slidesPerView: 'auto',
            spaceBetween: 24,
            autoplay: {
                delay: 4000,
            },
            freeMode: false,
            speed: 500,
        });

    }

});