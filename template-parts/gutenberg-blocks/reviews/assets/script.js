document.addEventListener('DOMContentLoaded', () => {

    const slider = document.querySelector('.reviews-slider')
    if (!slider || typeof Swiper === 'undefined') return

    const swiper = new Swiper(slider, {
        slidesPerView: 'auto',
        spaceBetween: 24,
        speed: 500,
        allowTouchMove: true,
        loop: true,

        autoplay: {
            delay: 2000,
            disableOnInteraction: false
        },

        on: {
            touchStart(swiper) {
                swiper.autoplay.stop()
            },

            touchEnd(swiper) {
                swiper.slideNext()
                swiper.autoplay.start()
            }
        }
    })

    // мышка (desktop)
    slider.addEventListener('mousedown', (e) => {
        if (e.button !== 0) return
        swiper.autoplay.stop()
    })

    slider.addEventListener('mouseup', () => {
        swiper.slideNext()
        swiper.autoplay.start()
    })

    slider.addEventListener('mouseleave', () => {
        swiper.autoplay.start()
    })

})