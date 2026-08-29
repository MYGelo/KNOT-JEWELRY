<?php

/*
| CACHE VERSION
*/

function get_filter_cache_version() {
    return get_option('filter_cache_version', 1);
}

/*
| REST ROUTES
*/

add_action('rest_api_init', function () {

    register_rest_route('site/v1', '/filter-posts', [
        'methods'             => 'POST',
        'callback'            => 'site_filter_posts',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('site/v1', '/filter-available', [
        'methods'             => 'POST',
        'callback'            => 'site_filter_available',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('site/v1', '/search-suggest', [
        'methods'             => 'GET',
        'callback'            => 'site_search_suggest',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('site/v1', '/viewed', [
        'methods'             => 'GET',
        'callback'            => 'site_viewed_posts',
        'permission_callback' => '__return_true',
    ]);

});

/*
| RECENTLY VIEWED — render cards for the given post IDs (order preserved).
*/
function site_viewed_posts(WP_REST_Request $request) {
    $raw = (string) $request->get_param('ids');
    $tap = sanitize_text_field((string) $request->get_param('tap'));

    $ids = array_values(array_unique(array_filter(array_map('absint', explode(',', $raw)))));
    $ids = array_slice($ids, 0, 12);

    if (empty($ids)) {
        return rest_ensure_response(['html' => '']);
    }

    $posts = get_posts([
        'post_type'              => 'post',
        'post_status'            => 'publish',
        'post__in'               => $ids,
        'orderby'                => 'post__in',
        'posts_per_page'         => count($ids),
        'no_found_rows'          => true,
        // Terms are needed: each card prints stone / material / type.
        'update_post_term_cache' => true,
        'ignore_sticky_posts'    => true,
    ]);

    // Prime attachment caches in one pass so each card doesn't trigger its own
    // queries for the thumbnail post, its metadata and alt text (avoids N+1).
    $thumb_ids = [];
    foreach ($posts as $post) {
        $tid = get_post_thumbnail_id($post->ID);
        if ($tid) {
            $thumb_ids[] = $tid;
        }
    }
    if ($thumb_ids) {
        _prime_post_caches($thumb_ids, false, true);
    }

    $html = '';
    foreach ($posts as $post) {
        ob_start();
        get_template_part('template-parts/components/stock-card', null, [
            'post_id'  => $post->ID,
            'tap_text' => $tap,
        ]);
        $html .= ob_get_clean();
    }

    // Per-user, always-fresh data — do not let host/CDN caches store it.
    nocache_headers();

    return rest_ensure_response(['html' => $html]);
}


/*
| HELPERS
*/

function site_sanitize_filter_array($value): array {
    if (!is_array($value)) return [];
    return array_values(array_filter(array_map('strval', $value), fn($s) => $s !== ''));
}

function site_filter_cache_key(string $prefix, array $data): string {
    return $prefix . get_filter_cache_version() . '_' . md5(json_encode($data));
}

function site_build_stone_clause(array $stones): ?array {
    $no_stone    = in_array('no-stone', $stones, true);
    $real_stones = array_values(array_filter($stones, fn($s) => $s !== 'no-stone'));

    if ($no_stone && $real_stones) {
        return [
            'relation' => 'OR',
            ['taxonomy' => 'stone', 'operator' => 'NOT EXISTS'],
            ['taxonomy' => 'stone', 'field' => 'slug', 'terms' => $real_stones, 'operator' => 'IN'],
        ];
    }
    if ($no_stone) {
        return ['taxonomy' => 'stone', 'operator' => 'NOT EXISTS'];
    }
    if ($real_stones) {
        return ['taxonomy' => 'stone', 'field' => 'slug', 'terms' => $real_stones, 'operator' => 'IN'];
    }
    return null;
}

function site_build_tax_query(array $stones, array $materials, array $product_type): array {
    $tax_query = ['relation' => 'AND'];

    if ($materials) {
        $tax_query[] = ['taxonomy' => 'material', 'field' => 'slug', 'terms' => $materials, 'operator' => 'IN'];
    }

    $stone_clause = site_build_stone_clause($stones);
    if ($stone_clause) {
        $tax_query[] = $stone_clause;
    }

    if ($product_type) {
        $tax_query[] = ['taxonomy' => 'product_type', 'field' => 'slug', 'terms' => $product_type, 'operator' => 'IN'];
    }

    return $tax_query;
}

/**
 * Keep only slugs that are real terms of the taxonomy. Prevents arbitrary
 * values from reaching the query and from bloating the transient cache with
 * junk keys. Pass $allow_no_stone for the stone facet's "no-stone" sentinel.
 */
function site_whitelist_terms(string $taxonomy, array $slugs, bool $allow_no_stone = false): array {
    if (empty($slugs)) return [];

    $terms = get_cached_terms($taxonomy);
    if (!is_array($terms)) return [];

    $valid = wp_list_pluck($terms, 'slug');
    if ($allow_no_stone) {
        $valid[] = 'no-stone';
    }

    return array_values(array_intersect($slugs, $valid));
}

/**
 * Read the catalog state from the URL query string (?type=&material=&stone=&q=&paged=),
 * whitelisted against real terms. Multi-value facets are comma-separated.
 * Used by the block for server-side rendering of the requested state.
 */
/** Legacy slugs → current ones, so already-shared links keep working. */
function site_catalog_slug_aliases(): array {
    return [
        'silver'      => 'sriblo',
        'gold'        => 'pozolota',
        'rings'       => 'kabluchky',
        'earrings'    => 'serezhky',
        'garnet'      => 'granat',
        'labradorite' => 'labradoryt',
        'moonstone'   => 'misiachnyi-kamin',
        'chalcedony'  => 'khaltsedon',
    ];
}

function site_catalog_request_filters(): array {
    $aliases = site_catalog_slug_aliases();

    // Param names must avoid WordPress' reserved query vars (m, p, s, paged,
    // page, cat, name, …) — those hijack routing and send the request to a
    // search/post/date-archive instead of the page holding this block.
    $csv = static function (array $keys) use ($aliases) {
        foreach ($keys as $key) {
            $raw = isset($_GET[$key]) ? (string) wp_unslash($_GET[$key]) : '';
            if ($raw === '') continue;

            $parts = array_filter(array_map('trim', explode(',', $raw)));
            return array_map(static fn($s) => $aliases[$s] ?? $s, $parts);
        }
        return [];
    };

    // `pagenum`, not `paged` — `paged` is a WP query var and 404s a static page.
    $page = absint($_GET['pagenum'] ?? 0) ?: absint($_GET['pg'] ?? 0) ?: 1;

    return [
        'search'       => sanitize_text_field(wp_unslash($_GET['q'] ?? '')),
        'materials'    => site_whitelist_terms('material', site_sanitize_filter_array($csv(['material', 'mat']))),
        'stones'       => site_whitelist_terms('stone', site_sanitize_filter_array($csv(['stone', 'stn'])), true),
        'product_type' => site_whitelist_terms('product_type', site_sanitize_filter_array($csv(['type', 'typ']))),
        'page'         => max(1, $page),
    ];
}

/**
 * Base catalog URL (current page permalink) carrying the active filters/search
 * but NOT the page number — pagination appends `paged`. Empty state → clean URL.
 */
function site_catalog_base_url(array $filters, string $base = ''): string {
    // REST has no queried object, so the caller (AJAX) passes the catalog page
    // URL explicitly; it is validated as same-site before being trusted.
    if ($base !== '' && strpos($base, home_url('/')) !== 0) {
        $base = '';
    }

    if ($base === '') {
        $base = get_permalink() ?: home_url('/');
    }

    $args = [];
    if (!empty($filters['materials']))    $args['material'] = implode(',', $filters['materials']);
    if (!empty($filters['product_type'])) $args['type']     = implode(',', $filters['product_type']);
    if (!empty($filters['stones']))       $args['stone']    = implode(',', $filters['stones']);
    if (!empty($filters['search']))       $args['q']        = $filters['search'];

    // Keep the comma between multi-values literal (add_query_arg encodes it).
    return $args ? str_replace('%2C', ',', add_query_arg($args, $base)) : $base;
}

/**
 * Canonicalise catalog URLs with a 301: out-of-range page numbers, legacy param
 * names and legacy slugs all end up on the current, valid address.
 */
function site_catalog_canonical_redirect(): void {
    if (is_admin() || wp_doing_ajax() || !function_exists('knot_is_catalog_page') || !knot_is_catalog_page()) {
        return;
    }

    // Absurd page numbers (bots, stale links): the site can never have more
    // pages than "all published posts / per page", and wp_count_posts() is
    // cached — so this costs nothing and avoids rendering an empty grid.
    $requested_page = absint($_GET['pagenum'] ?? 0);
    if ($requested_page > 1) {
        $published = (int) (wp_count_posts('post')->publish ?? 0);
        $max_pages = max(1, (int) ceil($published / 24));

        if ($requested_page > $max_pages) {
            wp_safe_redirect(site_catalog_base_url(site_catalog_request_filters()), 301);
            exit;
        }
    }

    $has_legacy_param = false;
    foreach (['mat', 'stn', 'typ', 'pg', 'paged'] as $old) {
        if (isset($_GET[$old])) { $has_legacy_param = true; break; }
    }

    $aliases        = site_catalog_slug_aliases();
    $has_legacy_slug = false;
    foreach (['material', 'stone', 'type'] as $key) {
        $raw = isset($_GET[$key]) ? (string) wp_unslash($_GET[$key]) : '';
        if ($raw === '') continue;
        foreach (explode(',', $raw) as $slug) {
            if (isset($aliases[trim($slug)])) { $has_legacy_slug = true; break 2; }
        }
    }

    if (!$has_legacy_param && !$has_legacy_slug) {
        return;
    }

    $filters = site_catalog_request_filters();
    $target  = site_catalog_base_url($filters);
    if ($filters['page'] > 1) {
        $target = add_query_arg('pagenum', $filters['page'], $target);
    }

    wp_safe_redirect($target, 301);
    exit;
}

add_action('template_redirect', 'site_catalog_canonical_redirect');

/**
 * Single source of truth for the catalog query args, shared by the REST
 * endpoints (AJAX) and the server-side block render (SSR), so both filter
 * identically. A suggestion pick (exact IDs) overrides search + facets.
 */
function site_build_query_args(string $search, array $materials, array $stones, array $product_type, array $pick_ids = []): array {
    if (!empty($pick_ids)) {
        return [
            'post_type'   => 'post',
            'post_status' => 'publish',
            'post__in'    => $pick_ids,
            'orderby'     => 'post__in',
        ];
    }

    $tax_query = site_build_tax_query($stones, $materials, $product_type);

    $args = ['post_type' => 'post', 'post_status' => 'publish'];
    if ($search !== '') {
        $args['s'] = $search;
    }
    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }

    return $args;
}

/*
| CATALOG RESULTS (shared by SSR + AJAX, with a bounded transient cache)
*/

/**
 * Only plain facet combinations on the first pages are cached. Free-text search
 * and suggestion picks are excluded on purpose: they come from a public
 * endpoint and would let anyone create unlimited transient rows.
 */
function site_catalog_is_cacheable(string $search, array $pick_ids, int $page): bool {
    if (defined('DISABLE_FILTER_CACHE') && DISABLE_FILTER_CACHE) return false;

    return $search === '' && empty($pick_ids) && $page <= 3;
}

/**
 * Resolve a catalog state to ['post_ids', 'total_pages', 'available'].
 * Cached for the combinations above; always fresh otherwise.
 */
function site_catalog_get_results(string $search, array $materials, array $stones, array $product_type, array $pick_ids, int $page, int $per_page, bool $with_available = true): array {

    $ms = $materials;    sort($ms);
    $ss = $stones;       sort($ss);
    $ps = $product_type; sort($ps);

    $cacheable = site_catalog_is_cacheable($search, $pick_ids, $page);
    $cache_key = site_filter_cache_key('filter_posts_', [$ms, $ss, $ps, $page, $per_page]);

    if ($cacheable) {
        $cached = get_transient($cache_key);

        // One entry per combination. A cached entry may have been written by a
        // caller that didn't need the available terms — only reuse it if it
        // already carries what this caller asks for.
        if (is_array($cached)
            && isset($cached['post_ids'], $cached['total_pages'], $cached['total_posts'])
            && (!$with_available || !empty($cached['available']))
        ) {
            return $cached;
        }
    }

    $base_args = site_build_query_args($search, $materials, $stones, $product_type, $pick_ids);

    $query = new WP_Query(array_merge($base_args, [
        'posts_per_page'         => $per_page,
        'paged'                  => $page,
        'fields'                 => 'ids',
        'ignore_sticky_posts'    => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]));

    // All matching IDs — drives the "unavailable" greying of the checkboxes.
    // Skipped when the caller doesn't need it (unfiltered SSR), saving a query.
    $available = [];

    if ($with_available) {
        $all_ids_query = new WP_Query(array_merge($base_args, [
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]));

        $available = site_compute_available_terms($all_ids_query->posts);
    }

    $result = [
        'post_ids'    => $query->posts,
        'total_pages' => (int) $query->max_num_pages,
        'total_posts' => (int) $query->found_posts,
        'available'   => $available,
    ];

    if ($cacheable) {
        // Short TTL: the cache version is bumped on every product save, which
        // orphans old keys — keep them from lingering in wp_options for a day.
        set_transient($cache_key, $result, 3 * HOUR_IN_SECONDS);
    }

    return $result;
}

/**
 * Render product cards for the given IDs, priming post + attachment caches in
 * one pass so no card queries its own thumbnail (avoids N+1).
 */
function site_render_post_cards(array $post_ids): string {
    if (empty($post_ids)) {
        return '<p>Нічого не знайдено</p>';
    }

    _prime_post_caches($post_ids, true, true);

    $thumb_ids = [];
    foreach ($post_ids as $id) {
        $tid = get_post_thumbnail_id($id);
        if ($tid) $thumb_ids[] = $tid;
    }
    if ($thumb_ids) {
        _prime_post_caches($thumb_ids, false, true);
    }

    global $post;
    ob_start();
    foreach ($post_ids as $id) {
        $post = get_post($id);
        if (!$post) continue;
        setup_postdata($post);
        get_template_part('template-parts/components/post', 'card');
    }
    wp_reset_postdata();

    return ob_get_clean();
}

function site_compute_available_terms(array $post_ids): array {
    $available = ['materials' => [], 'stones' => [], 'product_type' => []];

    if (empty($post_ids)) return $available;

    update_object_term_cache($post_ids, 'post');

    foreach ($post_ids as $post_id) {
        $m = get_the_terms($post_id, 'material');
        $s = get_the_terms($post_id, 'stone');
        $t = get_the_terms($post_id, 'product_type');

        if ($m) foreach ($m as $term) $available['materials'][] = $term->slug;

        if ($s) {
            foreach ($s as $term) $available['stones'][] = $term->slug;
        } else {
            $available['stones'][] = 'no-stone';
        }

        if ($t) foreach ($t as $term) $available['product_type'][] = $term->slug;
    }

    foreach ($available as $k => $v) {
        $available[$k] = array_values(array_unique($v));
    }

    return $available;
}


/*
| TERMS CACHE
*/

function get_cached_terms($taxonomy) {
    $cache_key = 'terms_' . $taxonomy;
    $terms     = get_transient($cache_key);

    if ($terms !== false) return $terms;

    $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
    set_transient($cache_key, $terms, DAY_IN_SECONDS);

    return $terms;
}


/*
| FILTER POSTS
*/

function site_filter_posts($request) {

    $search       = sanitize_text_field($request['search'] ?? '');
    $materials    = site_whitelist_terms('material', site_sanitize_filter_array($request['materials'] ?? []));
    $stones       = site_whitelist_terms('stone', site_sanitize_filter_array($request['stones'] ?? []), true);
    $product_type = site_whitelist_terms('product_type', site_sanitize_filter_array($request['product_type'] ?? []));
    $page           = max(1, intval($request['page'] ?? 1));
    $posts_per_page = 24;

    // Exact picks from a search suggestion (one title → possibly several posts).
    $pick_ids = array_values(array_unique(array_filter(array_map('absint', (array) ($request['ids'] ?? [])))));

    $results = site_catalog_get_results(
        $search, $materials, $stones, $product_type, $pick_ids, $page, $posts_per_page
    );

    // Clamp an out-of-range page (bots, stale links) to the last real one.
    if ($page > 1 && $results['total_pages'] > 0 && $page > $results['total_pages']) {
        $page    = (int) $results['total_pages'];
        $results = site_catalog_get_results(
            $search, $materials, $stones, $product_type, $pick_ids, $page, $posts_per_page
        );
    }

    $posts_html = site_render_post_cards($results['post_ids']);

    /* PAGINATION — real hrefs need the catalog page URL, which REST doesn't
       know; the client sends it and it is validated as same-site. */

    ob_start();
    $total_pages   = $results['total_pages'];
    $total_posts   = $results['total_posts'];
    $shown_posts   = count($results['post_ids']);
    $paged         = $page;
    $page_base_url = site_catalog_base_url(
        [
            'search'       => $search,
            'materials'    => $materials,
            'stones'       => $stones,
            'product_type' => $product_type,
        ],
        esc_url_raw((string) ($request['base_url'] ?? ''))
    );
    include get_template_directory() . '/template-parts/components/pagination.php';
    $pagination_html = ob_get_clean();

    return [
        'posts'        => $posts_html,
        'pagination'   => $pagination_html,
        'total_pages'  => $total_pages,
        'current_page' => $paged,
        'available'    => $results['available'],
    ];
}


/*
| AVAILABLE TERMS
*/

function site_filter_available($request) {

    $search       = sanitize_text_field($request['search'] ?? '');
    $materials    = site_whitelist_terms('material', site_sanitize_filter_array($request['materials'] ?? []));
    $stones       = site_whitelist_terms('stone', site_sanitize_filter_array($request['stones'] ?? []), true);
    $product_type = site_whitelist_terms('product_type', site_sanitize_filter_array($request['product_type'] ?? []));

    $args = array_merge(
        site_build_query_args($search, $materials, $stones, $product_type),
        [
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'cache_results'  => true,
        ]
    );

    $query = new WP_Query($args);

    return site_compute_available_terms($query->posts);
}


/*
| SEARCH SUGGEST
*/

function site_search_suggest(WP_REST_Request $request) {

    $q = sanitize_text_field($request->get_param('q') ?? '');

    if (mb_strlen($q) < 2) {
        return rest_ensure_response([]);
    }

    $query = new WP_Query([
        'post_type'              => 'post',
        'post_status'            => 'publish',
        'posts_per_page'         => 12,
        's'                      => $q,
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    // Group by title — one title can belong to several products, so keep all IDs.
    $groups = [];

    foreach ($query->posts as $post) {
        $title = get_the_title($post);
        $key   = mb_strtolower(trim($title));

        if (!isset($groups[$key])) {
            if (count($groups) >= 8) continue;
            $groups[$key] = ['title' => $title, 'ids' => []];
        }

        $groups[$key]['ids'][] = $post->ID;
    }

    $suggestions = array_map(static function ($g) {
        return [
            'title' => $g['title'],
            'ids'   => array_values(array_unique(array_map('intval', $g['ids']))),
        ];
    }, array_values($groups));

    return rest_ensure_response($suggestions);
}

// config.php
// define('DISABLE_FILTER_CACHE', true);
