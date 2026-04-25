<?php

/*
|--------------------------------------------------------------------------
| CACHE VERSION
|--------------------------------------------------------------------------
*/

function get_filter_cache_version() {
    return get_option('filter_cache_version', 1);
}

function bump_filter_cache() {
    update_option('filter_cache_version', time());
}


/*
|--------------------------------------------------------------------------
| REST ROUTE
|--------------------------------------------------------------------------
*/

add_action('rest_api_init', function () {

    register_rest_route('site/v1', '/filter-posts', [
        'methods'  => 'POST',
        'callback' => 'site_filter_posts',
        'permission_callback' => '__return_true'
    ]);

    register_rest_route('site/v1', '/filter-available', [
        'methods'  => 'POST',
        'callback' => 'site_filter_available',
        'permission_callback' => '__return_true'
    ]);

});



/*
|--------------------------------------------------------------------------
| TERMS CACHE
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

    $cache_key = 'filter_posts_' . get_filter_cache_version() . '_' . md5(json_encode([
            $search,
            $materials,
            $stones,
            $product_type,
            $page
        ]));


    /*
    |--------------------------------------------------------------------------
    | CACHE READ
    |--------------------------------------------------------------------------
    */

    if (!defined('DISABLE_FILTER_CACHE') || DISABLE_FILTER_CACHE === false) {

        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }
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
        'current_page' => $paged,
    ];


    /*
    |--------------------------------------------------------------------------
| CACHE WRITE
    |--------------------------------------------------------------------------
    */

    if (!defined('DISABLE_FILTER_CACHE') || DISABLE_FILTER_CACHE === false) {
        set_transient($cache_key, $response, HOUR_IN_SECONDS);
    }

    return $response;
}

function site_filter_available($request) {

    $search = sanitize_text_field($request['search'] ?? '');

    $materials = $request['materials'] ?? [];
    $stones = $request['stones'] ?? [];
    $product_type = $request['product_type'] ?? [];

    $tax_query = ['relation' => 'AND'];

    if ($materials) {
        $tax_query[] = [
            'taxonomy'=>'material',
            'field'=>'slug',
            'terms'=>$materials
        ];
    }

    if ($stones) {
        $tax_query[] = [
            'taxonomy'=>'stone',
            'field'=>'slug',
            'terms'=>$stones
        ];
    }

    if ($product_type) {
        $tax_query[] = [
            'taxonomy'=>'product_type',
            'field'=>'slug',
            'terms'=>$product_type
        ];
    }

    $args = [
        'post_type'=>'post',
        'posts_per_page'=>-1,
        'post_status'=>'publish',
        'fields'=>'ids'
    ];

    if ($search) {
        $args['s'] = $search;
    }

    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }

    $query = new WP_Query($args);

    $available = [
        'materials'=>[],
        'stones'=>[],
        'product_type'=>[]
    ];

    foreach ($query->posts as $post_id) {

        $available['materials'] = array_merge(
            $available['materials'],
            wp_get_post_terms($post_id,'material',['fields'=>'slugs'])
        );

        $available['stones'] = array_merge(
            $available['stones'],
            wp_get_post_terms($post_id,'stone',['fields'=>'slugs'])
        );

        $available['product_type'] = array_merge(
            $available['product_type'],
            wp_get_post_terms($post_id,'product_type',['fields'=>'slugs'])
        );

    }

    foreach ($available as $k=>$v) {
        $available[$k] = array_values(array_unique($v));
    }

    return $available;
}
/*
|--------------------------------------------------------------------------
| CACHE INVALIDATION
|--------------------------------------------------------------------------
*/

add_action('save_post_post', 'bump_filter_cache');
add_action('created_term', 'bump_filter_cache');
add_action('edited_term', 'bump_filter_cache');
add_action('delete_term', 'bump_filter_cache');


// add in config.php ! define('DISABLE_FILTER_CACHE', true);