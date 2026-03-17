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
    $page      = intval($_POST['page'] ?? 1);

    $tax_query = ['relation' => 'AND'];
    if (!empty($materials)) $tax_query[] = ['taxonomy'=>'material','field'=>'slug','terms'=>$materials];
    if (!empty($stones)) $tax_query[] = ['taxonomy'=>'stone','field'=>'slug','terms'=>$stones];
    if (!empty($product_type)) $tax_query[] = ['taxonomy'=>'product_type','field'=>'slug','terms'=>$product_type];

    $posts_per_page = 24;

    $args = [
        'post_type'      => 'post',
        'posts_per_page' => $posts_per_page,
        'paged'          => $page,
        'post_status'    => 'publish',
    ];

    if (count($tax_query) > 1) $args['tax_query'] = $tax_query;
    if ($search) $args['s'] = $search;

    $query = new WP_Query($args);
    $posts_html = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            ob_start();
//            get_template_part('template-parts/content', 'ajax-post');

            $price_meta = get_post_meta(get_the_ID(), 'price', true);
            ?>

            <div class="all-posts__post-item">
                <?php if (has_post_thumbnail()): ?>
                    <a href="<?= esc_url(get_permalink()); ?>" class="all-posts__post-thumb scroll-animate--off">
                        <?php the_post_thumbnail('medium_large'); ?>
                    </a>
                <?php endif; ?>

                <div class="all-post__text-content">
                    <h2 class="all-posts__item-title scroll-animate--off"><?php the_title(); ?></h2>

                    <?php
                    $taxonomies = [
                        'material'     => 'Матеріал',
                        'stone'        => 'Камінь',
                        // 'product_type' => 'Тип виробу',
                    ];

                    echo '<div class="all-posts__categories scroll-animate--off">';

                    foreach ($taxonomies as $taxonomy => $label) {

                        $terms = get_the_terms(get_the_ID(), $taxonomy);

                        if ($terms && !is_wp_error($terms)) {
                            $links = [];

                            foreach ($terms as $term) {
                                $links[] = '<a class="all-posts__category scroll-animate--off" href="' . esc_url(get_term_link($term)) . '">'
                                    . esc_html($term->name)
                                    . '</a>';
                            }

                            echo implode(' ', $links);
                        }
                    }

                    echo '</div>';
                    ?>
                </div>

                <?php if ($price_meta): ?>
                    <p class="all-posts__price scroll-animate--off"><?= esc_html($price_meta); ?> <span>грн</span></p>
                <?php endif; ?>
            </div>

            <?php
            $posts_html[] = ob_get_clean();
        }
    }

    wp_reset_postdata();

    wp_send_json([
        'posts' => $posts_html,
        'posts_per_page' => $posts_per_page
    ]);
}