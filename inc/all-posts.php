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

});


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
    $materials    = site_sanitize_filter_array($request['materials']    ?? []);
    $stones       = site_sanitize_filter_array($request['stones']       ?? []);
    $product_type = site_sanitize_filter_array($request['product_type'] ?? []);
    $page         = max(1, intval($request['page'] ?? 1));
    $posts_per_page = 24;

    $ms = $materials;    sort($ms);
    $ss = $stones;       sort($ss);
    $ps = $product_type; sort($ps);

    $cache_key = site_filter_cache_key('filter_posts_', [$search, $ms, $ss, $ps, $page]);
    $use_cache = !defined('DISABLE_FILTER_CACHE') || DISABLE_FILTER_CACHE === false;


    /* CACHE READ */

    if ($use_cache) {
        $cached = get_transient($cache_key);

        if ($cached !== false && isset($cached['available'])) {
            $post_ids    = $cached['post_ids'];
            $total_pages = $cached['total_pages'];
            $available   = $cached['available'];

            $posts = get_posts([
                'post_type'              => 'post',
                'post__in'               => $post_ids,
                'orderby'                => 'post__in',
                'posts_per_page'         => -1,
                'update_post_meta_cache' => true,
                'update_post_term_cache' => true,
            ]);

            global $post;
            ob_start();
            foreach ($posts as $post) {
                setup_postdata($post);
                get_template_part('template-parts/components/post', 'card');
            }
            wp_reset_postdata();
            $posts_html = ob_get_clean();

            ob_start();
            $paged = $page;
            include get_template_directory() . '/template-parts/components/pagination.php';
            $pagination_html = ob_get_clean();

            return [
                'posts'        => $posts_html,
                'pagination'   => $pagination_html,
                'total_pages'  => $total_pages,
                'current_page' => $paged,
                'available'    => $available,
            ];
        }
    }


    /* TAX QUERY */

    $tax_query = site_build_tax_query($stones, $materials, $product_type);

    $base_args = ['post_type' => 'post', 'post_status' => 'publish'];
    if ($search) $base_args['s'] = $search;
    if (count($tax_query) > 1) $base_args['tax_query'] = $tax_query;


    /* PAGED QUERY */

    $query = new WP_Query(array_merge($base_args, [
        'posts_per_page'         => $posts_per_page,
        'paged'                  => $page,
        'ignore_sticky_posts'    => true,
        'update_post_meta_cache' => true,
        'update_post_term_cache' => true,
        'cache_results'          => true,
    ]));


    /* IMAGE CACHE WARMUP */

    if ($query->posts) {
        $post_ids = wp_list_pluck($query->posts, 'ID');
        update_meta_cache('post', $post_ids);
        update_post_thumbnail_cache($query);

        foreach ($query->posts as $p) {
            $thumb_id = get_post_thumbnail_id($p->ID);
            if ($thumb_id) {
                get_post($thumb_id);
                wp_get_attachment_image_src($thumb_id, 'medium');
            }
        }
    }


    /* POSTS HTML */

    ob_start();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/components/post', 'card');
        }
    } else {
        echo '<p>Нічого не знайдено</p>';
    }
    $posts_html = ob_get_clean();


    /* PAGINATION */

    ob_start();
    $total_pages = $query->max_num_pages;
    $paged       = $page;
    include get_template_directory() . '/template-parts/components/pagination.php';
    $pagination_html = ob_get_clean();

    wp_reset_postdata();


    /* ALL IDs — for available terms (after paged to avoid term cache interference) */

    $all_ids_query = new WP_Query(array_merge($base_args, [
        'posts_per_page'         => -1,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'ignore_sticky_posts'    => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]));

    $available = site_compute_available_terms($all_ids_query->posts);


    /* RESPONSE */

    $response = [
        'posts'        => $posts_html,
        'pagination'   => $pagination_html,
        'total_pages'  => $total_pages,
        'current_page' => $paged,
        'available'    => $available,
    ];


    /* CACHE WRITE */

    if ($use_cache) {
        set_transient($cache_key, [
            'post_ids'    => wp_list_pluck($query->posts, 'ID'),
            'total_pages' => $total_pages,
            'available'   => $available,
        ], DAY_IN_SECONDS);
    }

    return $response;
}


/*
| AVAILABLE TERMS
*/

function site_filter_available($request) {

    $search       = sanitize_text_field($request['search'] ?? '');
    $materials    = site_sanitize_filter_array($request['materials']    ?? []);
    $stones       = site_sanitize_filter_array($request['stones']       ?? []);
    $product_type = site_sanitize_filter_array($request['product_type'] ?? []);

    $tax_query = site_build_tax_query($stones, $materials, $product_type);

    $args = [
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'cache_results'  => true,
    ];

    if ($search) $args['s'] = $search;
    if (count($tax_query) > 1) $args['tax_query'] = $tax_query;

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

    $suggestions = [];
    $seen_titles = [];

    foreach ($query->posts as $post) {
        $title = get_the_title($post);
        $key   = mb_strtolower(trim($title));

        if (isset($seen_titles[$key])) continue;
        if (count($suggestions) >= 8) break;

        $seen_titles[$key] = true;
        $suggestions[]     = ['id' => $post->ID, 'title' => $title];
    }

    return rest_ensure_response($suggestions);
}

// config.php
// define('DISABLE_FILTER_CACHE', true);
