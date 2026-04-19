document.addEventListener('DOMContentLoaded', function () {
    const cards = document.querySelectorAll('.stock-card')
    if (!cards.length) return

    let swiperInstance = null

    const slider = document.querySelector('.in-stock-slider')

    if (slider && typeof Swiper !== 'undefined') {
        swiperInstance = new Swiper(slider, {
            slidesPerView: 'auto',
            spaceBetween: 24,
            autoplay: {
                delay: 2000,
            },
            freeMode: false,
            speed: 3000,
        })
    }

    const syncAutoplay = () => {
        if (!swiperInstance?.autoplay) return

        const anyFlipped = document.querySelector('.stock-card.flipped')

        if (anyFlipped) {
            swiperInstance.autoplay.stop()
        } else {
            swiperInstance.autoplay.start()
        }
    }

    cards.forEach(card => {
        let flipped = false

        const closeBtn = card.querySelector('.stock-close--js')
        const link = card.dataset.link

        const hasLink = typeof link === 'string' && link.length > 0

        card.addEventListener('click', (e) => {
            if (e.target.closest('.stock-close--js')) return

            if (!flipped) {
                card.classList.add('flipped')
                flipped = true
                syncAutoplay()
                return
            }

            if (hasLink) {
                window.location.href = link
            }
        })

        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault()
                e.stopPropagation()

                if (card.classList.contains('flipped')) {
                    card.classList.remove('flipped')
                    flipped = false
                    syncAutoplay()
                }
            })
        }
    })
})