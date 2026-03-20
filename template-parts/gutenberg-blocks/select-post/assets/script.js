document.addEventListener('DOMContentLoaded', function () {
    new Swiper('.select-post__swiper', {
        speed: 600,
        // parallax: true,
        slidesPerView: 3,
        spaceBetween: 30,
        centeredSlides: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        loop: true,

        breakpoints: {
            0: {
                slidesPerView: 1
            },
            551: {
                slidesPerView: 2
            },
            767: {
                slidesPerView: 3
            },
        }
    });
});