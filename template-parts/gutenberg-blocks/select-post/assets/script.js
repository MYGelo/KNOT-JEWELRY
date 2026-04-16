document.addEventListener('DOMContentLoaded', function () {
    const slider = document.querySelector('.select-post__swiper');
    const wrapper = slider?.querySelector('.swiper-wrapper');
    const slides = slider?.querySelectorAll('.swiper-slide') || [];


    if (slides.length <= 6) {
        slides.forEach(slide => {
            wrapper.appendChild(slide.cloneNode(true));
        });
    }

    new Swiper('.select-post__swiper', {
        loop: true,
        slidesPerView: 4,
        spaceBetween: 18,

        speed: 8000,
        autoplay: {
            delay: 0,
            disableOnInteraction: false,
        },

        allowTouchMove: true,
        disableOnInteraction: false,

        freeMode: false
    });
});