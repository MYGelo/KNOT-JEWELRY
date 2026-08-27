document.addEventListener('DOMContentLoaded', () => {

    const videos = document.querySelectorAll('[data-banner-video]');
    if (!videos.length) return;

    const conn = navigator.connection || {};
    const saveData = conn.saveData === true;
    const slowNetwork = /(^|-)(2g|slow-2g)$/.test(conn.effectiveType || '');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Data saver, a 2G connection or a motion-sensitive visitor: keep the
    // poster image and never fetch the file.
    if (saveData || slowNetwork || reduceMotion) return;

    function attach(video) {
        if (video.dataset.bannerLoaded) return;
        video.dataset.bannerLoaded = '1';

        const source = document.createElement('source');
        source.src = video.dataset.bannerVideo;
        source.type = 'video/mp4';
        video.appendChild(source);

        video.load();

        // Reveal only once there are actual frames — avoids a black flash
        // over the poster image.
        video.addEventListener('canplay', () => {
            video.classList.add('is-ready');
        }, { once: true });

        const play = video.play();
        if (play && typeof play.catch === 'function') {
            play.catch(() => { /* autoplay blocked — poster stays */ });
        }
    }

    // Don't compete with the hero image and the rest of the page: start the
    // download once loading has settled.
    function whenIdle(fn) {
        if (document.readyState === 'complete') {
            window.requestIdleCallback ? requestIdleCallback(fn, { timeout: 2000 }) : setTimeout(fn, 200);
            return;
        }
        window.addEventListener('load', () => whenIdle(fn), { once: true });
    }

    videos.forEach(video => {
        // Pause while off screen — a looping video off screen is wasted
        // battery and CPU, especially on phones.
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver(entries => {
                const visible = entries[0].isIntersecting;

                if (visible) {
                    whenIdle(() => attach(video));
                    if (video.dataset.bannerLoaded) video.play().catch(() => {});
                } else if (video.dataset.bannerLoaded) {
                    video.pause();
                }
            }, { threshold: 0.1 });

            io.observe(video);
        } else {
            whenIdle(() => attach(video));
        }
    });

});
