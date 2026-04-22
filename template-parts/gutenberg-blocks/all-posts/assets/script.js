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

    function getFilters() {
        return {
            materials: [...materialEls].filter(i => i.checked).map(i => i.value),
            stones: [...stoneEls].filter(i => i.checked).map(i => i.value),
            product_type: [...typeEls].filter(i => i.checked).map(i => i.value),
        };
    }
    function wait(ms) {
        return new Promise(res => setTimeout(res, ms));
    }

    function openFilter() {
        wrapper.classList.add('filter-open');
        body.classList.add('overflow');
    }

    function closeFilter() {
        wrapper.classList.remove('filter-open');
        body.classList.remove('overflow');
    }

    async function loadPosts(targetPage = 1) {

        if (loading) return;
        loading = true;
        loader?.classList.add('active');
        allPostWrap.classList.add('is-loading');

        if (!isInitialLoad) {
            scrollToSection();
        }

        const filters = getFilters();

        const formData = new FormData();
        formData.append('action', 'filter_posts');
        formData.append('search', searchInput?.value || '');
        formData.append('materials', JSON.stringify(filters.materials));
        formData.append('stones', JSON.stringify(filters.stones));
        formData.append('product_type', JSON.stringify(filters.product_type));
        formData.append('page', targetPage);

        try {

            const fetchPromise = fetch(ajax_object.ajax_url, {
                method: 'POST',
                body: formData
            }).then(res => res.json());

            // ⏱ минимум 1000ms лоадер
            const [data] = await Promise.all([
                fetchPromise,
                wait(1000)
            ]);

            postsWrap.innerHTML = data.posts;
            paginationWrap.innerHTML = data.pagination;

            page = targetPage;

            attachPagination();
            closeFilter();

            isInitialLoad = false;

        } catch (err) {
            console.error(err);
        }

        loading = false;
        loader?.classList.remove('active');
        allPostWrap.classList.remove('is-loading');
    }

    function attachPagination() {

        document.querySelectorAll('.page-num').forEach(btn => {

            btn.addEventListener('click', () => {

                if (btn.classList.contains('dots')) return;

                const target = parseInt(btn.dataset.page);
                if (!target || target === page) return;

                loadPosts(target);

            });

        });

    }

    function scrollToSection() {
        const rect = section.getBoundingClientRect();

        window.scrollTo({
            top: window.scrollY + rect.top - 80,
            behavior: 'smooth'
        });
    }

    // 🔎 SEARCH
    function debounce(fn, delay) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), delay);
        };
    }

    searchInput?.addEventListener('input',
        debounce(() => loadPosts(1), 400)
    );

    searchBtn?.addEventListener('click', () => loadPosts(1));

    // 🔘 FILTERS
    // [...materialEls, ...stoneEls, ...typeEls]
    //     .forEach(el => el.addEventListener('change', () => loadPosts(1)));

    // RESET
    resetBtn?.addEventListener('click', () => {

        searchInput.value = '';

        document.querySelectorAll(
            '.filter-material, .filter-stone, .filter-product_type'
        ).forEach(i => i.checked = false);

        loadPosts(1);
    });

    // FILTER UI
    filterBtn?.addEventListener('click', openFilter);
    closeBtn?.addEventListener('click', closeFilter);
    bg?.addEventListener('click', closeFilter);

    // INIT
    loadPosts(1);

});