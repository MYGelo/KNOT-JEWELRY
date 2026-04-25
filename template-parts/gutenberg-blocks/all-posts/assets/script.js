document.addEventListener('DOMContentLoaded', () => {

    const body = document.body;

    const section = document.querySelector('.all-posts');
    const postsWrap = document.getElementById('posts-wrap');
    const paginationWrap = document.getElementById('ajax-pagination');
    const loader = document.getElementById('ajax-loader');
    const searchInput = document.getElementById('ajax-search');

    const filterBtn = document.querySelector('.all-posts__filter');
    const wrapper = document.querySelector('.all-posts__posts-wrapper');
    const allPostWrap = document.querySelector('.all-posts__posts-wrap');
    const closeBtn = document.querySelector('.filter-dropdown__close');
    const bg = document.querySelector('.filter-dropdown__bg');

    const searchBtn = document.getElementById('ajax-search-btn');
    const resetBtn = document.getElementById('ajax-reset-btn');

    let page = 1;
    let loading = false;
    let isInitialLoad = true;

    const materialEls = document.querySelectorAll('.filter-material');
    const stoneEls = document.querySelectorAll('.filter-stone');
    const typeEls = document.querySelectorAll('.filter-product_type');

    /* -------------------------------- */
    /* FILTER STATE                     */
    /* -------------------------------- */

    function getFilters() {
        return {
            materials: [...materialEls].filter(i => i.checked).map(i => i.value),
            stones: [...stoneEls].filter(i => i.checked).map(i => i.value),
            product_type: [...typeEls].filter(i => i.checked).map(i => i.value),
        };
    }

    /* -------------------------------- */
    /* UTILS                            */
    /* -------------------------------- */

    function wait(ms) {
        return new Promise(res => setTimeout(res, ms));
    }

    function debounce(fn, delay) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), delay);
        };
    }

    /* -------------------------------- */
    /* FILTER UI                        */
    /* -------------------------------- */

    function openFilter() {
        wrapper.classList.add('filter-open');
        body.classList.add('overflow');
    }

    function closeFilter() {
        wrapper.classList.remove('filter-open');
        body.classList.remove('overflow');
    }

    function scrollToSection() {

        const rect = section.getBoundingClientRect();

        window.scrollTo({
            top: window.scrollY + rect.top - 80,
            behavior: 'smooth'
        });

    }

    /* -------------------------------- */
    /* LOAD POSTS                       */
    /* -------------------------------- */

    async function loadPosts(targetPage = 1, { scroll = false } = {}) {

        if (loading) return;

        loading = true;

        loader?.classList.add('active');
        allPostWrap.classList.add('is-loading');

        const filters = getFilters();

        try {

            const fetchPromise = fetch('/wp-json/site/v1/filter-posts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    search: searchInput?.value || '',
                    materials: filters.materials,
                    stones: filters.stones,
                    product_type: filters.product_type,
                    page: targetPage
                })
            }).then(res => res.json());

            if (scroll) scrollToSection();

            const [data] = await Promise.all([
                fetchPromise,
                wait(1000)
            ]);

            postsWrap.innerHTML = data.posts;
            paginationWrap.innerHTML = data.pagination;

            page = targetPage;

            closeFilter();

            isInitialLoad = false;

            await updateAvailableFilters();

        } catch (err) {
            console.error(err);
        }

        loading = false;

        loader?.classList.remove('active');
        allPostWrap.classList.remove('is-loading');
    }

    /* -------------------------------- */
    /* UPDATE AVAILABLE FILTERS         */
    /* -------------------------------- */

    async function updateAvailableFilters() {

        const filters = getFilters();

        try {

            const res = await fetch('/wp-json/site/v1/filter-available', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    search: searchInput?.value || '',
                    materials: filters.materials,
                    stones: filters.stones,
                    product_type: filters.product_type
                })
            });

            const data = await res.json();

            updateFilters(data);

        } catch (err) {
            console.error(err);
        }

    }

    /* -------------------------------- */
    /* APPLY FILTER STATE               */
    /* -------------------------------- */

    function updateFilters(data) {

        const materials = new Set(data.materials);
        const stones = new Set(data.stones);
        const types = new Set(data.product_type);

        materialEls.forEach(el => {

            const label = el.closest('label');

            if (el.checked) {
                label.classList.remove('unavailable');
                return;
            }

            if (!materials.has(el.value)) {
                label.classList.add('unavailable');
            } else {
                label.classList.remove('unavailable');
            }

        });

        stoneEls.forEach(el => {

            const label = el.closest('label');

            if (el.checked) {
                label.classList.remove('unavailable');
                return;
            }

            if (!stones.has(el.value)) {
                label.classList.add('unavailable');
            } else {
                label.classList.remove('unavailable');
            }

        });

        typeEls.forEach(el => {

            const label = el.closest('label');

            if (el.checked) {
                label.classList.remove('unavailable');
                return;
            }

            if (!types.has(el.value)) {
                label.classList.add('unavailable');
            } else {
                label.classList.remove('unavailable');
            }

        });

    }

    function setCheckboxLoading(active) {

        const all = [...materialEls, ...stoneEls, ...typeEls];

        all.forEach(el => {
            const label = el.closest('label');
            if (!label) return;

            if (active) {
                // только НЕ выбранные
                if (!el.checked) {
                    label.classList.add('checkbox-loading');
                } else {
                    label.classList.remove('checkbox-loading');
                }
            } else {
                label.classList.remove('checkbox-loading');
            }
        });

    }

    /* -------------------------------- */
    /* PAGINATION                       */
    /* -------------------------------- */

    paginationWrap?.addEventListener('click', (e) => {

        const btn = e.target.closest('.page-num');

        if (!btn || btn.classList.contains('dots')) return;

        const target = parseInt(btn.dataset.page);

        if (!target || target === page) return;

        loadPosts(target, { scroll: true });

    });

    /* -------------------------------- */
    /* SEARCH                           */
    /* -------------------------------- */

    searchInput?.addEventListener(
        'input',
        debounce(() => loadPosts(1, { scroll: false }), 400)
    );

    searchBtn?.addEventListener('click', () => loadPosts(1));

    /* -------------------------------- */
    /* FILTER CHANGE                    */
    /* -------------------------------- */

    [...materialEls, ...stoneEls, ...typeEls].forEach(el => {

        el.addEventListener('change', () => {

            setCheckboxLoading(true);

            Promise.all([
                updateAvailableFilters(),
            ]).finally(() => {
                setCheckboxLoading(false);
            });

        });

    });

    /* -------------------------------- */
    /* RESET                            */
    /* -------------------------------- */

    resetBtn?.addEventListener('click', () => {

        searchInput.value = '';

        document.querySelectorAll(
            '.filter-material, .filter-stone, .filter-product_type'
        ).forEach(i => i.checked = false);

        loadPosts(1);

    });

    /* -------------------------------- */
    /* FILTER UI                        */
    /* -------------------------------- */

    filterBtn?.addEventListener('click', openFilter);
    closeBtn?.addEventListener('click', closeFilter);
    bg?.addEventListener('click', closeFilter);

    /* -------------------------------- */
    /* INIT                             */
    /* -------------------------------- */

    loadPosts(1);

});