<?php
add_action('wp_ajax_filter_posts', 'filter_posts');
add_action('wp_ajax_nopriv_filter_posts', 'filter_posts');

function filter_posts() {

    $search = sanitize_text_field($_POST['search'] ?? '');

    $materials = json_decode(stripslashes($_POST['materials'] ?? '[]'), true);
    $stones    = json_decode(stripslashes($_POST['stones'] ?? '[]'), true);
    $product_type = json_decode(stripslashes($_POST['product_type'] ?? '[]'), true);

    $page = intval($_POST['page'] ?? 1);
    $posts_per_page = 12;

    $tax_query = ['relation' => 'AND'];

    if (!empty($materials)) {
        $tax_query[] = [
            'taxonomy' => 'material',
            'field' => 'slug',
            'terms' => $materials
        ];
    }

    if (!empty($stones)) {
        $tax_query[] = [
            'taxonomy' => 'stone',
            'field' => 'slug',
            'terms' => $stones
        ];
    }

    if (!empty($product_type)) {
        $tax_query[] = [
            'taxonomy' => 'product_type',
            'field' => 'slug',
            'terms' => $product_type
        ];
    }

    $args = [
        'post_type'      => 'post',
        'posts_per_page' => $posts_per_page,
        'paged'          => $page,
        'post_status'    => 'publish'
    ];

    if (!empty($search)) {
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

            $price_meta = get_post_meta(get_the_ID(), 'price', true);
            ?>

            <div class="all-posts__post-item">

                <?php if (has_post_thumbnail()): ?>
                    <a href="<?= esc_url(get_permalink()); ?>" class="all-posts__post-thumb">
                        <?php the_post_thumbnail('medium_large'); ?>
                    </a>
                <?php endif; ?>

                <div class="all-post__text-content">
                    <h2 class="all-posts__item-title"><?php the_title(); ?></h2>

                    <div class="all-posts__categories">

                        <?php
                        foreach (['material', 'stone'] as $tax) {
                            $terms = get_the_terms(get_the_ID(), $tax);

                            if ($terms && !is_wp_error($terms)) {
                                foreach ($terms as $term) {
                                    echo '<span class="all-posts__category">'
                                        . esc_html($term->name) .
                                        '</span>';
                                }
                            }
                        }
                        ?>

                    </div>
                </div>

                <?php if ($price_meta): ?>
                    <p class="all-posts__price">
                        <?= esc_html($price_meta); ?> <span>грн</span>
                    </p>
                <?php endif; ?>

            </div>

            <?php
        }
    } else {
        echo '<p>Нічого не знайдено</p>';
    }

    $posts_html = ob_get_clean();

    // ===== PAGINATION =====
    ob_start();

    $total_pages = $query->max_num_pages;
    $paged = $page;

    include get_template_directory() . '/template-parts/components/pagination.php';

    $pagination_html = ob_get_clean();

    wp_reset_postdata();

    wp_send_json([
        'posts' => $posts_html,
        'pagination' => $pagination_html,
        'total_pages' => $total_pages,
        'current_page' => $paged
    ]);
}