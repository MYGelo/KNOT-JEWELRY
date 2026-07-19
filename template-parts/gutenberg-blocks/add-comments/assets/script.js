document.addEventListener('DOMContentLoaded', () => {

    const slider = document.querySelector('.comments-slider')
    if (!slider || typeof Swiper === 'undefined') return

    let inView = false

    const swiper = new Swiper(slider, {
        slidesPerView: 'auto',
        spaceBetween: 24,
        autoplay: {
            delay: 4000,
        },
        freeMode: false,
        speed: 500,
    })
    window.commentsSwiper = swiper

    // Hold autoplay until the slider is actually on screen (observer below),
    // so it doesn't advance several slides before the user scrolls to it.
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

});