document.addEventListener('DOMContentLoaded', () => {

    const thumbsEl = document.querySelector('.gallery-thumbs');
    let thumbs = null;

    if (thumbsEl) {
        thumbs = new Swiper(thumbsEl, {
            slidesPerView: 3,
            spaceBetween: 12,
            watchSlidesProgress: true,
            watchOverflow: true,
            pagination: {
                el: '.swiper-pagination',
                type: 'progressbar',
                clickable: true
            },
        });

        // Проверяем количество слайдов
        const totalSlides = thumbsEl.querySelectorAll('.swiper-slide').length;
        if (totalSlides < 4) {
            const paginationEl = thumbsEl.querySelector('.swiper-pagination');
            if (paginationEl) {
                paginationEl.remove(); // убираем сам элемент прогрессбара
            }
        }
    }

    new Swiper('.gallery-main', {
        effect: 'fade',
        fadeEffect: { crossFade: true },
        speed: 500,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev'
        },
        thumbs: thumbs ? { swiper: thumbs } : undefined,
        preloadImages: false,
        lazy: {
            loadPrevNext: true,
            loadOnTransitionStart: true
        }
    });

});