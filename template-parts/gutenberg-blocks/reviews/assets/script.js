document.addEventListener('DOMContentLoaded', () => {

    const slider = document.querySelector('.reviews-slider')
    if (!slider || typeof Swiper === 'undefined') return

    let inView = false

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
            touchStart(s) {
                s.autoplay.stop()
            },

            touchEnd(s) {
                if (inView) s.autoplay.start()
            }
        }
    })

    // Hold autoplay until the slider is actually on screen (observer below),
    // so it doesn't run in the background before the user scrolls to it.
    swiper.autoplay?.stop()

    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            inView = entries[0].isIntersecting
            if (inView) {
                swiper.autoplay?.start()
            } else {
                swiper.autoplay?.stop()
            }
        }, { threshold: 0.25 })
        io.observe(slider)
    } else {
        inView = true // no observer support: fall back to always-on
        swiper.autoplay?.start()
    }

    // мышка (desktop) — resume only while the slider is in view
    slider.addEventListener('mousedown', (e) => {
        if (e.button !== 0) return
        swiper.autoplay.stop()
    })

    slider.addEventListener('mouseup', () => {
        if (inView) swiper.autoplay.start()
    })

    slider.addEventListener('mouseleave', () => {
        if (inView) swiper.autoplay.start()
    })

})