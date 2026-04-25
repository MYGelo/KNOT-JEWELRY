class GalleryPopup {

    constructor() {

        this.gallery = document.querySelector('.product-gallery')
        this.popup = document.querySelector('.gallery-popup')

        if(!this.gallery || !this.popup) return

        this.wrapper = this.popup.querySelector('.swiper-wrapper')
        this.images = this.gallery.querySelectorAll('.gallery-main img')

        this.hint = this.popup.querySelector('.zoom-hint')

        this.swiper = null
        this.hasZoomed = false

        this.bindEvents()
    }


    /* EVENTS */
    bindEvents() {

        this.gallery.addEventListener('click',(e)=>this.onOpen(e))
        this.popup.addEventListener('click',(e)=>this.onClose(e))
        document.addEventListener('keydown',(e)=>this.onKey(e))

        this.popup.addEventListener('wheel',(e)=>this.onZoomWheel(e), { passive:true })

        this.addDoubleTapZoom()
        // this.addSwipeClose()
    }


    /* OPEN */
    onOpen(e) {

        const zoom = e.target.closest('.gallery-zoom')
        if(!zoom) return

        const mainSwiper = this.gallery.querySelector('.gallery-main')?.swiper
        const index = mainSwiper?.clickedIndex ?? 0

        this.openPopup()
        this.initSwiper()

        this.swiper.slideToLoop(index,0)

        this.showHint()
    }


    openPopup() {
        this.popup.classList.add('open')
        document.body.style.overflow='hidden'
    }


    closePopup() {
        this.popup.classList.remove('open')
        document.body.style.overflow=''
    }


    onClose(e) {

        if(
            e.target.classList.contains('overlay') ||
            e.target.classList.contains('gallery-close')
        ){
            this.closePopup()
        }
    }


    onKey(e) {

        if(e.key === 'Escape'){
            this.closePopup()
        }
    }


    /* SWIPER */
    initSwiper() {

        if(this.swiper) return

        this.wrapper.innerHTML=''

        this.images.forEach((img,i)=>{

            const slide=document.createElement('div')
            slide.className='swiper-slide'

            const zoomWrap=document.createElement('div')
            zoomWrap.className='swiper-zoom-container'

            const image=document.createElement('img')

            image.src=img.dataset.src || img.src
            image.loading = i < 2 ? 'eager' : 'lazy'
            image.decoding='async'

            zoomWrap.appendChild(image)
            slide.appendChild(zoomWrap)

            this.wrapper.appendChild(slide)
        })


        this.swiper = new Swiper('.gallery-popup-slider',{

            slidesPerView:1,
            speed:400,
            loop:true,

            preloadImages:false,
            lazy:true,

            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },

            zoom:{
                maxRatio:5,
                minRatio:1,
                toggle:false,
            },

            navigation:{
                nextEl:'.gallery-popup .swiper-button-next',
                prevEl:'.gallery-popup .swiper-button-prev'
            },

            on:{
                slideChange: () => this.resetZoom()
            }

        })
    }


    /* WHEEL ZOOM */
    onZoomWheel(e) {

        const slide = this.popup.querySelector('.swiper-slide-active')
        if(!slide) return

        const container = slide.querySelector('.swiper-zoom-container')
        if(!container) return

        const rect = container.getBoundingClientRect()

        // позиция курсора внутри изображения (0–1)
        const x = (e.clientX - rect.left) / rect.width
        const y = (e.clientY - rect.top) / rect.height

        // фиксируем точку фокуса
        container.style.transformOrigin = `${x * 100}% ${y * 100}%`

        let scale = parseFloat(container.dataset.scale || 1)

        scale += e.deltaY < 0 ? 0.2 : -0.2
        scale = Math.min(Math.max(scale, 1), 5)

        container.dataset.scale = scale
        container.style.transform = `scale(${scale})`

        this.hideZoomHint()
    }

    /* DOUBLE TAP */
    addDoubleTapZoom() {

        let lastTap = 0

        this.popup.addEventListener('touchend',(e)=>{

            const now = Date.now()

            if(now - lastTap < 300){

                const slide = this.popup.querySelector('.swiper-slide-active')
                const zoom = slide?.querySelector('.swiper-zoom-container')

                if(!zoom) return

                let scale = parseFloat(zoom.dataset.scale || 1)

                scale = scale > 1 ? 1 : 2

                zoom.dataset.scale = scale
                zoom.style.transform = scale === 1 ? '' : `scale(${scale})`

                this.hideZoomHint()
            }

            lastTap = now
        })
    }


    /* SWIPE CLOSE */
    addSwipeClose() {

        let startY = 0
        let currentY = 0
        let dragging = false

        this.popup.addEventListener('touchstart',(e)=>{

            startY = e.touches[0].clientY
            dragging = true
        })

        this.popup.addEventListener('touchmove',(e)=>{

            if(!dragging) return

            currentY = e.touches[0].clientY
            const diff = currentY - startY

            if(diff > 0){
                this.popup.querySelector('.popup_container').style.transform =
                    `translateY(${diff}px)`
            }
        })

        this.popup.addEventListener('touchend',()=>{

            const diff = currentY - startY
            const container = this.popup.querySelector('.popup_container')

            if(diff > 120){
                this.closePopup()
            } else {
                container.style.transform = ''
            }

            dragging = false
        })
    }


    /* HINT */
    showHint() {

        if(!this.hint) return

        this.hint.classList.remove('hide')
        this.hasZoomed = false
    }


    hideZoomHint() {

        if(!this.hint) return

        const slide = this.popup.querySelector('.swiper-slide-active')
        if(!slide) return

        const zoom = slide.querySelector('.swiper-zoom-container')
        if(!zoom) return

        const scale = parseFloat(zoom.dataset.scale || 1)

        if(scale > 1){
            this.hint.classList.add('hide')
            this.hasZoomed = true
        } else {
            this.hint.classList.remove('hide')
            this.hasZoomed = false
        }
    }


    /* RESET */
    resetZoom() {

        const slide = this.popup.querySelector('.swiper-slide-active')
        if(!slide) return

        const zoom = slide.querySelector('.swiper-zoom-container')
        if(!zoom) return

        zoom.style.transform = ''
        zoom.dataset.scale = 1

        this.hasZoomed = false

        if(this.hint){
            this.hint.classList.remove('hide')
        }
    }

}



document.addEventListener('DOMContentLoaded', () => {

    new GalleryPopup()

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
