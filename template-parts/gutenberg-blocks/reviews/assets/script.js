document.addEventListener('DOMContentLoaded', () => {

    let swiperInstance = null
    const slider = document.querySelector('.reviews-slider')

    if (slider && typeof Swiper !== 'undefined') {
        swiperInstance = new Swiper(slider, {
            slidesPerView: 'auto',
            spaceBetween: 24,
            speed: 500,
            allowTouchMove: true,
            autoplay: {
                delay: 2000,
                disableOnInteraction: false
            }
        })
    }

    if (!swiperInstance) return

    const pause = () => swiperInstance.autoplay.stop()
    const play  = () => swiperInstance.autoplay.start()

    // MOUSE
    slider.addEventListener('mousedown', (e) => {
        if (e.button !== 0) return
        pause()
    })

    slider.addEventListener('mouseup', play)
    slider.addEventListener('mouseleave', play)

    // TOUCH
    // slider.addEventListener('touchstart', pause, { passive: true })
    slider.addEventListener('touchend', play)
    slider.addEventListener('touchcancel', play)

})