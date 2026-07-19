document.addEventListener('DOMContentLoaded', function () {
    const slider = document.querySelector('.in-stock-slider')
    if (!slider) return

    // Scope to this slider so the shared .stock-card markup used elsewhere
    // (e.g. the "recently viewed" strip) never gets these handlers.
    const cards = slider.querySelectorAll('.stock-card')
    if (!cards.length) return

    let swiperInstance = null
    let inView = false

    if (typeof Swiper !== 'undefined') {
        swiperInstance = new Swiper(slider, {
            slidesPerView: 'auto',
            spaceBetween: 24,
            autoplay: {
                delay: 4000,
            },
            freeMode: false,
            speed: 500,
        })
        // Hold autoplay until the slider is actually on screen (observer below),
        // so it doesn't advance several slides before the user scrolls to it.
        swiperInstance.autoplay?.stop()
    }

    const syncAutoplay = () => {
        if (!swiperInstance?.autoplay) return

        const anyFlipped = slider.querySelector('.stock-card.flipped')

        if (inView && !anyFlipped) {
            swiperInstance.autoplay.start()
        } else {
            swiperInstance.autoplay.stop()
        }
    }

    if (swiperInstance && 'IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            inView = entries[0].isIntersecting
            syncAutoplay()
        }, { threshold: 0.25 })
        io.observe(slider)
    } else {
        inView = true // no observer support: fall back to always-on
        syncAutoplay()
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
                // const loader = document.getElementById('ajax-loader');
                // if (loader) {
                //     loader.classList.add('active');
                // }
                //
                window.location.href = link;
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