<?php
$block_anchor = $block['anchor'] ?? '';
$block_classes = 'in-stock';

if (!empty($block['className'])) $block_classes .= ' ' . $block['className'];

$title = get_field('in-stock_main_title');
$tap_text = get_field('in-stock_tap_text');

/* КЕШ ЗАПРОСА */
$posts = get_transient('in_stock_posts');

if ($posts === false) {

    $posts = get_posts([
        'post_type' => 'post',
        'category_name' => 'in-stock',
        'posts_per_page' => -1,
        'no_found_rows' => true,
        'update_post_meta_cache' => true,
        'update_post_term_cache' => false,
        'ignore_sticky_posts' => true,
        'suppress_filters' => true
    ]);

    set_transient('in_stock_posts', $posts, HOUR_IN_SECONDS);
}

if ($posts) : ?>

    <section class="<?= esc_attr($block_classes) ?>" id="<?= esc_attr($block_anchor) ?>">
        <div class="container">
            <div class="stock__wrapper">

                <?php if($title): ?>
                    <h2><?= esc_html($title); ?></h2>
                <?php endif; ?>

                <div class="swiper in-stock-slider">
                    <div class="swiper-wrapper">

                        <?php foreach ($posts as $post):
                            get_template_part('template-parts/components/stock-card', null, [
                                'post_id'  => $post->ID,
                                'tap_text' => $tap_text,
                            ]);
                        endforeach; ?>

                    </div>
                </div>
            </div>
        </div>
    </section>

<?php endif; ?>