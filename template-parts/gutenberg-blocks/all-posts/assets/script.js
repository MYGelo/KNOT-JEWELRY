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
    const searchIconBtn = document.getElementById('ajax-search-icon-btn');
    const resetBtn = document.getElementById('ajax-reset-btn');
    const suggestionsEl = document.getElementById('search-suggestions');

    let page = (() => {
        const pg = parseInt(new URLSearchParams(location.search).get('pagenum') || '1', 10);
        return pg > 0 ? pg : 1;
    })();
    let loading = false;
    let searchIds = null; // exact IDs picked from a suggestion (one title → many posts)

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
    /* URL STATE (shareable / SEO)      */
    /* -------------------------------- */

    // Build the shareable URL for the current filters + page, mirroring the
    // param names the server reads (?type=&material=&stone=&q=&paged=).
    function buildUrl(targetPage) {
        const base = (section && section.dataset.catalogUrl) || location.pathname;
        const f = getFilters();
        const q = (searchInput?.value || '').trim();

        // Build manually so the comma separating multi-values stays literal
        // (URLSearchParams would encode it to %2C). Slugs are latin.
        const enc = list => list.map(encodeURIComponent).join(',');
        const parts = [];

        // Names must avoid WP's reserved query vars (m, p, s, paged, page, cat…),
        // which would route the request away from this page.
        if (f.materials.length) parts.push('material=' + enc(f.materials));
        if (f.product_type.length) parts.push('type=' + enc(f.product_type));
        if (f.stones.length) parts.push('stone=' + enc(f.stones));
        if (q) parts.push('q=' + encodeURIComponent(q));
        if (targetPage > 1) parts.push('pagenum=' + targetPage);

        return parts.length ? base + '?' + parts.join('&') : base;
    }

    function pushUrl(targetPage) {
        try {
            history.pushState(null, '', buildUrl(targetPage));
        } catch (e) { /* ignore */ }
    }

    // Reflect the current URL back into the controls (used on Back/Forward).
    function syncUIFromUrl() {
        const p = new URLSearchParams(location.search);
        const setChecks = (els, csv) => {
            const wanted = new Set((csv || '').split(',').filter(Boolean));
            els.forEach(el => { el.checked = wanted.has(el.value); });
        };

        if (searchInput) searchInput.value = p.get('q') || '';
        setChecks(materialEls, p.get('material'));
        setChecks(stoneEls, p.get('stone'));
        setChecks(typeEls, p.get('type'));
        searchIds = null;

        const pg = parseInt(p.get('pagenum') || '1', 10);
        return pg > 0 ? pg : 1;
    }

    /* -------------------------------- */
    /* UTILS                            */
    /* -------------------------------- */

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

    // Keep the "24 з 121" counter honest: it always reflects what is actually
    // in the grid right now (appended pages included).
    function refreshCount() {
        const el = paginationWrap?.querySelector('[data-count-current]');
        if (!el) return;
        el.textContent = String(postsWrap.querySelectorAll('.all-posts__post-item').length);
    }

    async function loadPosts(targetPage = 1, { scroll = false, push = true, append = false } = {}) {

        if (loading) return;

        loading = true;

        let loaderVisible = false;
        const loaderTimer = setTimeout(() => {
            loader?.classList.add('active');
            allPostWrap?.classList.add('is-loading');
            loaderVisible = true;
        }, 200);

        const filters = getFilters();

        try {

            const data = await fetch('/wp-json/site/v1/filter-posts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    search: searchInput?.value || '',
                    materials: filters.materials,
                    stones: filters.stones,
                    product_type: filters.product_type,
                    ids: searchIds || [],
                    page: targetPage,
                    // Lets the server build real pagination hrefs that keep the
                    // active filters (REST has no page context of its own).
                    base_url: (section && section.dataset.catalogUrl) || ''
                })
            }).then(res => res.json());

            if (append) {
                postsWrap.insertAdjacentHTML('beforeend', data.posts);
            } else {
                postsWrap.innerHTML = data.posts;
            }
            paginationWrap.innerHTML = data.pagination;
            refreshCount();

            page = targetPage;

            // Reflect the new state in the URL so it's shareable / bookmarkable
            // and Back/Forward works. Skipped when the load itself came from a
            // popstate (Back/Forward), to avoid pushing a duplicate entry.
            if (push) pushUrl(targetPage);

            closeFilter();

            if (data.available) {
                updateFilters(data.available);
            }

            // Scroll after the DOM is updated and layout settled (search also
            // blurs the input first, closing the mobile keyboard) — one frame later.
            if (scroll) requestAnimationFrame(scrollToSection);

        } catch (err) {
            console.error(err);
        }

        clearTimeout(loaderTimer);
        loader?.classList.remove('active');
        allPostWrap?.classList.remove('is-loading');
        loading = false;

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

        const active = getFilters();

        const activeCount =
            (active.stones.length > 0 ? 1 : 0) +
            (active.materials.length > 0 ? 1 : 0) +
            (active.product_type.length > 0 ? 1 : 0);

        const onlyStones = active.stones.length > 0 && activeCount === 1;
        const onlyMaterials = active.materials.length > 0 && activeCount === 1;
        const onlyTypes = active.product_type.length > 0 && activeCount === 1;

        stoneEls.forEach(el => {

            const label = el.closest('label');

            if (el.checked) {
                label.classList.remove('unavailable');
                return;
            }

            if (onlyStones) {
                label.classList.remove('unavailable');
                return;
            }

            label.classList.toggle('unavailable', !stones.has(el.value));

        });

        materialEls.forEach(el => {

            const label = el.closest('label');

            if (el.checked) {
                label.classList.remove('unavailable');
                return;
            }

            if (onlyMaterials) {
                label.classList.remove('unavailable');
                return;
            }

            label.classList.toggle('unavailable', !materials.has(el.value));

        });

        typeEls.forEach(el => {

            const label = el.closest('label');

            if (el.checked) {
                label.classList.remove('unavailable');
                return;
            }

            if (onlyTypes) {
                label.classList.remove('unavailable');
                return;
            }

            label.classList.toggle('unavailable', !types.has(el.value));

        });
    }

    function setCheckboxLoading(active) {

        const all = [...materialEls, ...stoneEls, ...typeEls];

        all.forEach(el => {
            const label = el.closest('label');
            if (!label) return;

            if (active) {
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

        // "Завантажити ще" — appends the next page instead of replacing.
        const more = e.target.closest('[data-load-more]');
        if (more) {
            const next = parseInt(more.dataset.nextPage, 10);
            if (next) {
                more.disabled = true;
                loadPosts(next, { append: true, scroll: false });
            }
            return;
        }

        const btn = e.target.closest('.page-num');

        if (!btn || btn.classList.contains('dots')) return;

        e.preventDefault(); // links carry a real href for SEO / no-JS; AJAX here

        const target = parseInt(btn.dataset.page);

        if (!target || target === page) return;

        loadPosts(target, { scroll: true });

    });

    /* -------------------------------- */
    /* SUGGESTIONS                      */
    /* -------------------------------- */

    let suggestAbort = null;
    let closeTimer   = null;
    let blockNextClick = false;

    document.addEventListener('click', (e) => {
        if (blockNextClick) {
            blockNextClick = false;
            e.preventDefault();
            e.stopPropagation();
        }
    }, { capture: true });

    function closeSuggestions() {
        if (!suggestionsEl) return;
        suggestionsEl.classList.remove('active');
        clearTimeout(closeTimer);
        closeTimer = setTimeout(() => { suggestionsEl.innerHTML = ''; }, 250);
    }

    function runSearch() {
        closeSuggestions();
        searchInput?.blur();
        loadPosts(1, { scroll: true });
    }

    async function fetchSuggestions(q) {

        if (suggestAbort) suggestAbort.abort();
        suggestAbort = new AbortController();

        searchIconBtn?.classList.add('is-searching');

        try {
            const res = await fetch(
                `/wp-json/site/v1/search-suggest?q=${encodeURIComponent(q)}`,
                { signal: suggestAbort.signal }
            );
            const data = await res.json();

            if (!data.length) {
                closeSuggestions();
                return;
            }

            clearTimeout(closeTimer);
            suggestionsEl.classList.remove('active');
            suggestionsEl.innerHTML = '';

            data.forEach(item => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'all-posts__suggestion-item';
                btn.textContent = item.title;

                let pointerStartY = 0;

                btn.addEventListener('pointerdown', (e) => {
                    pointerStartY = e.clientY;
                });

                btn.addEventListener('pointerup', (e) => {
                    if (Math.abs(e.clientY - pointerStartY) > 10) return;
                    e.preventDefault();
                    blockNextClick = true;

                    // Filter the grid by the exact posts behind this title
                    // (one title can be several products) — reliable, no fuzzy search.
                    searchIds = Array.isArray(item.ids) ? item.ids : [];
                    searchInput.value = item.title;
                    runSearch();
                });

                suggestionsEl.appendChild(btn);
            });

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    suggestionsEl.classList.add('active');
                });
            });

        } catch (err) {
            if (err.name !== 'AbortError') console.error(err);
        } finally {
            searchIconBtn?.classList.remove('is-searching');
        }
    }

    /* -------------------------------- */
    /* SEARCH                           */
    /* -------------------------------- */

    // Typing means a new keyword search — drop any picked suggestion IDs.
    searchInput?.addEventListener('input', () => { searchIds = null; });

    searchInput?.addEventListener('input', debounce(() => {
        const q = searchInput.value.trim();
        if (q.length >= 2) {
            fetchSuggestions(q);
        } else {
            closeSuggestions();
        }
    }, 300));

    searchInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { searchIds = null; runSearch(); }
        if (e.key === 'Escape') closeSuggestions();
    });

    searchInput?.addEventListener('blur', () => {
        setTimeout(closeSuggestions, 150);
    });

    searchIconBtn?.addEventListener('click', () => { searchIds = null; runSearch(); });
    searchBtn?.addEventListener('click', () => { searchIds = null; loadPosts(1, { scroll: true }); });

    /* -------------------------------- */
    /* FILTER CHANGE                    */
    /* -------------------------------- */

    [...materialEls, ...stoneEls, ...typeEls].forEach(el => {

        el.addEventListener('change', () => {

            searchIds = null; // changing filters cancels a suggestion pick

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

        searchIds = null;
        searchInput.value = '';
        closeSuggestions();

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
    /* BACK / FORWARD                   */
    /* -------------------------------- */

    // The server renders whatever the URL asks for, so on Back/Forward we just
    // sync the controls to the new URL and re-fetch that state (without pushing
    // a new history entry). No pageshow refetch is needed anymore.
    window.addEventListener('popstate', () => {
        const pg = syncUIFromUrl();
        updateAvailableFilters();
        loadPosts(pg, { push: false, scroll: false });
    });

    /* -------------------------------- */
    /* INIT                             */
    /* -------------------------------- */

    // SSR already rendered the current URL state and, when filters are active,
    // ships the available terms in a data attribute — no extra request needed.
    (() => {
        const raw = section && section.dataset.available;
        if (!raw) return;

        try {
            updateFilters(JSON.parse(raw));
        } catch (e) {
            updateAvailableFilters();
        }
    })();

    // Landing on a URL that already carries catalog state (search, filters or a
    // page number): bring the results into view instead of leaving the visitor
    // at the top of the page.
    (() => {
        if (!section) return;

        const p = new URLSearchParams(location.search);
        const hasState = ['q', 'material', 'type', 'stone', 'pagenum']
            .some(key => (p.get(key) || '').trim() !== '');

        if (!hasState) return;

        // Wait for layout to settle (the browser may restore its own scroll first).
        requestAnimationFrame(() => {
            setTimeout(scrollToSection, 100);
        });
    })();

    loader?.classList.remove('active');

});