document.addEventListener('DOMContentLoaded', function () {
    new Swiper('.select-post__swiper', {
        speed: 600,
        parallax: true,
        slidesPerView: 3,
        spaceBetween: 24,
        // pagination: {
        //     el: '.select_post .swiper-pagination',
        //     clickable: true,
        // },
        navigation: {
            nextEl: '.select_post__swiper-button-next',
            prevEl: '.select_post__swiper-button-prev',
        },
        // loop: true,
        // centeredSlides: true,

        breakpoints: {
            0: {
                slidesPerView: 1,
                // spaceBetween: 50,
            },
            551: {
                slidesPerView: 2,
                spaceBetween: 12,
            },
            767: {
                slidesPerView: 3,
                spaceBetween: 18,
            },
        }
    });
});