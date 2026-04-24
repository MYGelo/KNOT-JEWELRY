<?php

add_action('rest_api_init', function () {

    register_rest_route('site/v1', '/filter-posts', [
        'methods'  => 'POST',
        'callback' => 'site_filter_posts',
        'permission_callback' => '__return_true'
    ]);

});


/*
|--------------------------------------------------------------------------
| CACHE TERMS
|--------------------------------------------------------------------------
*/

function get_cached_terms($taxonomy) {

    $cache_key = 'terms_' . $taxonomy;

    $terms = get_transient($cache_key);

    if ($terms !== false) {
        return $terms;
    }

    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => false
    ]);

    set_transient($cache_key, $terms, DAY_IN_SECONDS);

    return $terms;
}


/*
|--------------------------------------------------------------------------
| FILTER POSTS
|--------------------------------------------------------------------------
*/

function site_filter_posts($request) {

    $search = sanitize_text_field($request['search'] ?? '');

    $materials = $request['materials'] ?? [];
    $stones = $request['stones'] ?? [];
    $product_type = $request['product_type'] ?? [];

    $page = intval($request['page'] ?? 1);
    $posts_per_page = 24;


    /*
    |--------------------------------------------------------------------------
    | CACHE KEY
    |--------------------------------------------------------------------------
    */

    $cache_key = 'filter_posts_' . md5(json_encode([
            $search,
            $materials,
            $stones,
            $product_type,
            $page
        ]));


    $cached = get_transient($cache_key);

    if ($cached !== false) {
        return $cached;
    }


    /*
    |--------------------------------------------------------------------------
    | TAX QUERY
    |--------------------------------------------------------------------------
    */

    $tax_query = ['relation' => 'AND'];

    if ($materials) {
        $tax_query[] = [
            'taxonomy' => 'material',
            'field' => 'slug',
            'terms' => $materials
        ];
    }

    if ($stones) {
        $tax_query[] = [
            'taxonomy' => 'stone',
            'field' => 'slug',
            'terms' => $stones
        ];
    }

    if ($product_type) {
        $tax_query[] = [
            'taxonomy' => 'product_type',
            'field' => 'slug',
            'terms' => $product_type
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | QUERY
    |--------------------------------------------------------------------------
    */

    $args = [
        'post_type' => 'post',
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'post_status' => 'publish',

        'ignore_sticky_posts' => true,

        'update_post_meta_cache' => true,
        'update_post_term_cache' => true
    ];

    if ($search) {
        $args['s'] = $search;
    }

    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }


    $query = new WP_Query($args);


    /*
    |--------------------------------------------------------------------------
    | POSTS HTML
    |--------------------------------------------------------------------------
    */

    ob_start();

    if ($query->have_posts()) {

        while ($query->have_posts()) {
            $query->the_post();

            get_template_part(
                'template-parts/components/post',
                'card'
            );
        }

    } else {

        echo '<p>Нічого не знайдено</p>';

    }

    $posts_html = ob_get_clean();


    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */

    ob_start();

    $total_pages = $query->max_num_pages;
    $paged = $page;

    include get_template_directory() . '/template-parts/components/pagination.php';

    $pagination_html = ob_get_clean();


    wp_reset_postdata();


    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    $response = [
        'posts' => $posts_html,
        'pagination' => $pagination_html,
        'total_pages' => $total_pages,
        'current_page' => $paged
    ];


    set_transient($cache_key, $response, HOUR_IN_SECONDS);

    return $response;
}


/*
|--------------------------------------------------------------------------
| CLEAR FILTER CACHE
|--------------------------------------------------------------------------
*/

add_action('save_post', function() {

    global $wpdb;

    $wpdb->query(
        "DELETE FROM $wpdb->options
         WHERE option_name LIKE '_transient_filter_posts_%'
         OR option_name LIKE '_transient_timeout_filter_posts_%'"
    );

});


/*
|--------------------------------------------------------------------------
| CLEAR TERMS CACHE
|--------------------------------------------------------------------------
*/

function clear_terms_cache() {

    delete_transient('terms_material');
    delete_transient('terms_stone');
    delete_transient('terms_product_type');

}

add_action('created_term', 'clear_terms_cache');
add_action('edited_term', 'clear_terms_cache');
add_action('delete_term', 'clear_terms_cache');