<?php function theme_ajax_scripts() {

    wp_enqueue_script(
        'ajax-filter',
        get_template_directory_uri() . '/assets/js/ajax-filter.js',
        [],
        null,
        true
    );

    wp_localize_script('ajax-filter', 'ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'theme_ajax_scripts');


add_action('wp_ajax_filter_posts', 'filter_posts');
add_action('wp_ajax_nopriv_filter_posts', 'filter_posts');

function filter_posts() {

    $search = sanitize_text_field($_POST['search'] ?? '');

    $materials = json_decode(stripslashes($_POST['materials'] ?? '[]'), true);
    $stones    = json_decode(stripslashes($_POST['stones'] ?? '[]'), true);
    $product_type = json_decode(stripslashes($_POST['product_type'] ?? '[]'), true);

    $page = intval($_POST['page'] ?? 1);

    $cache_key = 'filter_' . md5(json_encode([
            $search,
            $materials,
            $stones,
            $product_type,
            $page
        ]));

    $cached = get_transient($cache_key);
    if ($cached) {
        wp_send_json($cached);
    }

    $tax_query = ['relation' => 'AND'];

    if (!empty($materials)) {
        $tax_query[] = ['taxonomy'=>'material','field'=>'slug','terms'=>$materials];
    }

    if (!empty($stones)) {
        $tax_query[] = ['taxonomy'=>'stone','field'=>'slug','terms'=>$stones];
    }

    if (!empty($product_type)) {
        $tax_query[] = ['taxonomy'=>'product_type','field'=>'slug','terms'=>$product_type];
    }

    $posts_per_page = 24;

    $args = [
        'post_type'      => 'post',
        'posts_per_page' => $posts_per_page,
        'paged'          => $page,
        'post_status'    => 'publish',

        // ⚡ СУПЕР УСКОРЕНИЕ
        'no_found_rows'  => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ];

    if (!empty($materials) || !empty($stones) || !empty($product_type)) {
        $args['tax_query'] = $tax_query;
    }

    if ($search) {
        $args['s'] = $search;
    }

    $query = new WP_Query($args);

    $posts_html = [];

    while ($query->have_posts()) {
        $query->the_post();

        ob_start();

        $price_meta = get_post_meta(get_the_ID(), 'price', true);
        $in_stock = in_array('in-stock', wp_get_post_categories(get_the_ID(), ['fields' => 'slugs']));
        ?>

        <div class="all-posts__post-item">

            <?php if (has_post_thumbnail()): ?>
                <a href="<?= esc_url(get_permalink()); ?>" class="all-posts__post-thumb scroll-animate--off">
                    <?php the_post_thumbnail('medium_large'); ?>
                </a>
            <?php endif; ?>

            <div class="all-post__text-content">
                <h2 class="all-posts__item-title scroll-animate--off"><?php the_title(); ?></h2>

                <div class="all-posts__categories scroll-animate--off">
                    <?php
                    foreach (['material' => 'Матеріал', 'stone' => 'Камінь'] as $taxonomy => $label) {
                        $terms = get_the_terms(get_the_ID(), $taxonomy);

                        if ($terms && !is_wp_error($terms)) {
                            foreach ($terms as $term) {
                                echo '<span class="all-posts__category">'
                                    . esc_html($term->name) .
                                    '</span>';
                            }
                        }
                    }

                    if ($in_stock): ?>
                        <p class="product-stock all-posts__category">В наявності</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($price_meta): ?>
                <p class="all-posts__price scroll-animate--off">
                    <?= esc_html($price_meta); ?> <span>грн</span>
                </p>
            <?php endif; ?>

        </div>

        <?php
        $posts_html[] = ob_get_clean();
    }

    wp_reset_postdata();

    $response = [
        'posts' => $posts_html,
        'posts_per_page' => $posts_per_page
    ];

    // ⚡ CACHE 5 минут
    set_transient($cache_key, $response, 15 * MINUTE_IN_SECONDS);

    wp_send_json($response);
}