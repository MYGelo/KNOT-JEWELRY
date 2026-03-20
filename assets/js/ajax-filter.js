

document.addEventListener('DOMContentLoaded', function () {
    const body = document.querySelector('body');
    const searchInput = document.getElementById('ajax-search');
    const postsWrap = document.getElementById('posts-wrap');
    const loader = document.getElementById('ajax-loader');
    const loadTrigger = document.createElement('div'); // невидимый триггер для наблюдателя
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

    const getFilters = () => {
        const materials = [...document.querySelectorAll('.filter-material:checked')].map(el => el.value);
        const stones = [...document.querySelectorAll('.filter-stone:checked')].map(el => el.value);
        const product_type = [...document.querySelectorAll('.filter-product_type:checked')].map(el => el.value);

        return { materials, stones, product_type };
    };

    const loadPosts = (reset = false) => {
        if (loading || (!reset && noMorePosts)) return;


        loading = true;
        loader.classList.add('active');

        if (reset) {
            page = 1;
            noMorePosts = false;
        } else {
            page++;
        }

        const filters = getFilters();

        const formData = new FormData();
        formData.append('action', 'filter_posts');
        formData.append('search', searchInput.value);
        formData.append('materials', JSON.stringify(filters.materials));
        formData.append('stones', JSON.stringify(filters.stones));
        formData.append('product_type', JSON.stringify(filters.product_type));
        formData.append('page', page);

        fetch(ajax_object.ajax_url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                loader.classList.remove('active');
                loading = false;

                // ❗ Если постов нет
                if (!data.posts || !data.posts.length) {

                    if (reset) {
                        postsWrap.innerHTML = '<p>Нічого не знайдено</p>';
                    }

                    noMorePosts = true;
                    if (loadMoreBtn) loadMoreBtn.style.display = 'none';
                    return;
                }

                // ❗ Удаляем старые ТОЛЬКО когда новые уже получены
                if (reset) {
                    postsWrap.innerHTML = '';
                }

                postsWrap.insertAdjacentHTML('beforeend', data.posts.join(''));

                // проверка конца страниц
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

    // Live поиск с задержкой
    searchInput.addEventListener('input', () => {
        clearTimeout(searchInput.delay);
        searchInput.delay = setTimeout(() => loadPosts(true), 400); // reset = true
    });

    // Фильтры
    // document.addEventListener('change', e => {
    //     if (
    //         e.target.classList.contains('filter-material') ||
    //         e.target.classList.contains('filter-stone') ||
    //         e.target.classList.contains('filter-product_type')
    //     ) {
    //         loadPosts(true);
    //     }
    // });


    // Кнопка "Пошук"
    if (searchBtn) {
        searchBtn.addEventListener('click', () => {
            loadPosts(true);
            wrapper.classList.remove('filter-open');
            body.classList.remove('overflow');
        });
    }

    // Кнопка "Обнулити"
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            searchInput.value = '';
            document.querySelectorAll('.filter-material, .filter-stone, .filter-product_type').forEach(el => el.checked = false);
            loadPosts(true);
            toggleResetBtn();
        });
    }

    // Кнопка "Load More"
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            loadPosts(false); // следующая страница
        });
    }

    if (filterBtn) {
        filterBtn.addEventListener('click', () => {
            wrapper.classList.toggle('filter-open');
            body.classList.toggle('overflow');
        })
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

    // RESET BTN
    function toggleResetBtn() {
        const anyChecked = Array.from(document.querySelectorAll('.filter-material:checked, .filter-stone:checked, .filter-product_type:checked')).length > 0;
        resetBtn.style.opacity = anyChecked ? '1' : '0';
        resetBtn.style.pointerEvents = anyChecked ? 'auto' : 'none';
    }

    document.querySelectorAll('.filter-material, .filter-stone, .filter-product_type').forEach(cb => {
        cb.addEventListener('change', toggleResetBtn);
    });

// и сразу вызываем, чтобы корректно инициализировать кнопку
    toggleResetBtn();


    // IntersectionObserver для инфинити скролла
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !loading && !noMorePosts) {
                loadPosts(false);
            }
        });
    });

    observer.observe(loadTrigger);

    loadPosts(true);
});
