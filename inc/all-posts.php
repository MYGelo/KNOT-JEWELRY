<?php
add_action('rest_api_init', function () {

    register_rest_route('site/v1', '/filter-posts', [
        'methods'  => 'POST',
        'callback' => 'site_filter_posts',
        'permission_callback' => '__return_true'
    ]);

});

function site_filter_posts($request) {

    $search = sanitize_text_field($request['search'] ?? '');

    $materials = $request['materials'] ?? [];
    $stones = $request['stones'] ?? [];
    $product_type = $request['product_type'] ?? [];

    $page = intval($request['page'] ?? 1);
    $posts_per_page = 24;

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

    $args = [
        'post_type' => 'post',
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'post_status' => 'publish',

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

    ob_start();

    $total_pages = $query->max_num_pages;
    $paged = $page;

    include get_template_directory()
        . '/template-parts/components/pagination.php';

    $pagination_html = ob_get_clean();

    wp_reset_postdata();

    return [
        'posts' => $posts_html,
        'pagination' => $pagination_html,
        'total_pages' => $total_pages,
        'current_page' => $paged
    ];
}