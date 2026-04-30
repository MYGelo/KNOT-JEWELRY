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

        this.lastTap = 0
        this.mode = null // gallery | comment

        this.bindEvents()
    }


    bindEvents() {

        this.gallery.addEventListener('click',(e)=>this.onOpen(e))
        this.popup.addEventListener('click',(e)=>this.onClose(e))
        document.addEventListener('keydown',(e)=>this.onKey(e))

        this.popup.addEventListener('wheel',(e)=>this.onZoomWheel(e), { passive:true })

        this.addDoubleTapZoom()
    }


    onOpen(e) {

        if(this.mode === 'comment') return

        const zoom = e.target.closest('.gallery-zoom')
        if(!zoom) return

        this.mode = 'gallery'

        const mainSwiper = this.gallery.querySelector('.gallery-main')?.swiper
        const index = mainSwiper?.clickedIndex ?? 0

        this.openPopup()
        this.initSwiper()

        this.swiper?.slideToLoop(index,0)

        this.showHint()
    }


    openPopup() {
        this.popup.classList.add('open')
        document.body.style.overflow='hidden'
    }


    closePopup() {
        this.popup.classList.remove('open')
        document.body.style.overflow=''

        this.mode = null
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


    initSwiper() {

        // ❗ КЛЮЧЕВОЙ FIX: всегда пересоздаём
        if(this.swiper){
            this.swiper.destroy(true,true)
            this.swiper = null
        }

        this.wrapper.innerHTML=''

        this.images.forEach((img,i)=>{

            const slide=document.createElement('div')
            slide.className='swiper-slide'

            const zoomWrap=document.createElement('div')
            zoomWrap.className='swiper-zoom-container'
            zoomWrap.dataset.scale = 1

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
            spaceBetween: 50,

            preloadImages:false,
            lazy:true,

            zoom:{
                maxRatio:5,
                minRatio:1,
                toggle:false
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


    onZoomWheel(e) {

        const slide = this.popup.querySelector('.swiper-slide-active')
        if(!slide) return

        const container = slide.querySelector('.swiper-zoom-container')
        if(!container) return

        let scale = parseFloat(container.dataset.scale || 1)

        scale += e.deltaY < 0 ? 0.2 : -0.2
        scale = Math.min(Math.max(scale,1),5)

        container.dataset.scale = scale

        container.style.transform = `translate3d(0,0,0) scale(${scale})`
        container.style.transformOrigin = 'center center'

        this.hideZoomHint()
    }


    addDoubleTapZoom() {

        this.popup.addEventListener('touchend',(e)=>{

            const now = Date.now()

            if(now - this.lastTap < 300){

                const slide = this.popup.querySelector('.swiper-slide-active')
                const zoom = slide?.querySelector('.swiper-zoom-container')

                if(!zoom) return

                let scale = parseFloat(zoom.dataset.scale || 1)

                scale = scale > 1 ? 1 : 2

                zoom.dataset.scale = scale

                zoom.style.transform =
                    scale === 1
                        ? 'translate3d(0,0,0) scale(1)'
                        : `translate3d(0,0,0) scale(${scale})`

                this.hideZoomHint()
            }

            this.lastTap = now
        }, { passive:true })
    }


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


    resetZoom() {

        const slide = this.popup.querySelector('.swiper-slide-active')
        if(!slide) return

        const zoom = slide.querySelector('.swiper-zoom-container')
        if(!zoom) return

        zoom.dataset.scale = 1
        zoom.style.transform = 'translate3d(0,0,0) scale(1)'

        this.hasZoomed = false

        if(this.hint){
            this.hint.classList.remove('hide')
        }
    }


    openCommentImage(src){

        this.mode = 'comment'

        // ❗ важно: полностью убиваем старый swiper
        if(this.swiper){
            this.swiper.destroy(true,true)
            this.swiper = null
        }

        this.wrapper.innerHTML = ''

        const slide = document.createElement('div')
        slide.className = 'swiper-slide'

        const zoomWrap = document.createElement('div')
        zoomWrap.className='swiper-zoom-container'
        zoomWrap.dataset.scale = 1

        const img = document.createElement('img')

        img.src = src
        img.loading = 'eager'

        zoomWrap.appendChild(img)
        slide.appendChild(zoomWrap)

        this.wrapper.appendChild(slide)

        this.openPopup()

        this.swiper = new Swiper('.gallery-popup-slider',{

            slidesPerView:1,
            speed:400,
            spaceBetween:50,

            zoom:{
                maxRatio:5,
                minRatio:1,
                toggle:false
            },

            navigation:{
                nextEl:'.gallery-popup .swiper-button-next',
                prevEl:'.gallery-popup .swiper-button-prev'
            }

        })
    }
}


/* =========================
   INIT
========================= */

document.addEventListener('DOMContentLoaded', () => {

    const popup = new GalleryPopup()

    document.addEventListener("click",(e)=>{

        const img = e.target.closest(".comment-photo img")
        if(!img) return

        popup.openCommentImage(img.src)
    })


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

        const totalSlides = thumbsEl.querySelectorAll('.swiper-slide').length;
        if (totalSlides < 4) {
            const paginationEl = thumbsEl.querySelector('.swiper-pagination');
            if (paginationEl) paginationEl.remove();
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