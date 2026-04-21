document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const searchInput = document.getElementById('ajax-search');
    const postsWrap = document.getElementById('posts-wrap');
    const loader = document.getElementById('ajax-loader');
    const loadTrigger = document.createElement('div');

    postsWrap.after(loadTrigger);

    const searchBtn = document.getElementById('ajax-search-btn');
    const resetBtn = document.getElementById('ajax-reset-btn');
    const loadMoreBtn = document.getElementById('ajax-load-more-btn');
    const closeBtn = document.querySelector('.filter-dropdown__close');
    const filterDropdownBg = document.querySelector('.filter-dropdown__bg');
    const filterBtn = document.querySelector('.all-posts__filter');
    const wrapper = document.querySelector('.all-posts__posts-wrapper');

    let page = 1;
    let loading = false;
    let noMorePosts = false;
    let controller = null;

    // ⚡ CACHE FILTERS (ускорение DOM)
    const materialEls = document.querySelectorAll('.filter-material');
    const stoneEls = document.querySelectorAll('.filter-stone');
    const typeEls = document.querySelectorAll('.filter-product_type');

    const getFilters = () => {
        const materials = [];
        const stones = [];
        const product_type = [];

        materialEls.forEach(el => el.checked && materials.push(el.value));
        stoneEls.forEach(el => el.checked && stones.push(el.value));
        typeEls.forEach(el => el.checked && product_type.push(el.value));

        return { materials, stones, product_type };
    };

    function debounce(fn, delay) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), delay);
        };
    }

    const loadPosts = (reset = false) => {
        if (loading || (!reset && noMorePosts)) return;

        if (controller) controller.abort();
        controller = new AbortController();

        loading = true;
        loader.classList.add('active');

        const nextPage = reset ? 1 : page + 1;

        if (reset) {
            page = 1;
            noMorePosts = false;
        } else {
            page = nextPage;
        }

        const filters = getFilters();

        const formData = new FormData();
        formData.append('action', 'filter_posts');
        formData.append('search', searchInput.value);
        formData.append('materials', JSON.stringify(filters.materials));
        formData.append('stones', JSON.stringify(filters.stones));
        formData.append('product_type', JSON.stringify(filters.product_type));
        formData.append('page', page);

        fetch(ajax_object.ajax_url, {
            method: 'POST',
            body: formData,
            signal: controller.signal
        })
            .then(res => res.json())
            .then(data => {
                loader.classList.remove('active');
                loading = false;

                if (!data.posts || !data.posts.length) {
                    if (reset) postsWrap.innerHTML = '<p>Нічого не знайдено</p>';
                    noMorePosts = true;
                    if (loadMoreBtn) loadMoreBtn.style.display = 'none';
                    return;
                }

                if (reset) postsWrap.innerHTML = '';

                postsWrap.insertAdjacentHTML('beforeend', data.posts.join(''));

                if (data.posts.length < data.posts_per_page) {
                    noMorePosts = true;
                    if (loadMoreBtn) loadMoreBtn.style.display = 'none';
                } else {
                    if (loadMoreBtn) loadMoreBtn.style.display = 'block';
                }
            })
            .catch(() => {
                loader.classList.remove('active');
                loading = false;
            });
    };

    // ⚡ LIVE SEARCH (быстрее + debounce)
    searchInput.addEventListener('input',
        debounce(() => loadPosts(true), 400)
    );

    if (searchBtn) {
        searchBtn.addEventListener('click', () => {
            loadPosts(true);
            wrapper.classList.remove('filter-open');
            body.classList.remove('overflow');
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            searchInput.value = '';
            document.querySelectorAll('.filter-material, .filter-stone, .filter-product_type')
                .forEach(el => el.checked = false);

            loadPosts(true);
            toggleResetBtn();
        });
    }

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => loadPosts(false));
    }

    if (filterBtn) {
        filterBtn.addEventListener('click', () => {
            wrapper.classList.toggle('filter-open');
            body.classList.toggle('overflow');
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            wrapper.classList.remove('filter-open');
            body.classList.remove('overflow');
        });
    }

    if (filterDropdownBg) {
        filterDropdownBg.addEventListener('click', () => {
            wrapper.classList.remove('filter-open');
            body.classList.remove('overflow');
        });
    }

    function toggleResetBtn() {
        const anyChecked =
            [...document.querySelectorAll('.filter-material:checked, .filter-stone:checked, .filter-product_type:checked')].length > 0;

        resetBtn.style.opacity = anyChecked ? '1' : '0';
        resetBtn.style.pointerEvents = anyChecked ? 'auto' : 'none';
    }

    document.querySelectorAll('.filter-material, .filter-stone, .filter-product_type')
        .forEach(cb => cb.addEventListener('change', toggleResetBtn));

    toggleResetBtn();

    // ⚡ IntersectionObserver (без спама)
    const observer = new IntersectionObserver(entries => {
        if (!entries[0].isIntersecting) return;
        if (!loading && !noMorePosts) loadPosts(false);
    }, { threshold: 0.5 });

    observer.observe(loadTrigger);

    loadPosts(true);
});