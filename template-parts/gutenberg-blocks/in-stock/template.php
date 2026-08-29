<?php
$block_anchor = $block['anchor'] ?? '';
$block_classes = 'in-stock';

if (!empty($block['className'])) $block_classes .= ' ' . $block['className'];

$title = get_field('in-stock_main_title');
$tap_text = get_field('in-stock_tap_text');

/* КЕШ ЗАПРОСА — only IDs. Caching whole post objects would keep serving
   products that were meanwhile trashed or deleted (broken cards / images). */
$post_ids = get_transient('in_stock_posts');

if ($post_ids === false) {

    $post_ids = get_posts([
        'post_type' => 'post',
        'category_name' => 'in-stock',
        'posts_per_page' => -1,
        'fields' => 'ids',
        // Recently updated products first — editing a product (price, stock
        // note, photos) moves it back to the front of the slider.
        'orderby' => 'modified',
        'order' => 'DESC',
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'ignore_sticky_posts' => true,
        'suppress_filters' => true
    ]);

    set_transient('in_stock_posts', $post_ids, HOUR_IN_SECONDS);
}

// A cache written before this block switched to IDs holds WP_Post objects —
// normalise whatever shape came back so a stale entry can't break the cards.
if (is_array($post_ids)) {
    $post_ids = array_values(array_filter(array_map(
        static fn($item) => is_object($item) ? (int) $item->ID : (int) $item,
        $post_ids
    )));
} else {
    $post_ids = [];
}

if ($post_ids) {

    // One query for the posts + their meta; get_post() below is then free.
    // Terms too: each card now prints stone / material / type.
    _prime_post_caches($post_ids, true, true);

    // Drop anything deleted or unpublished since the cache was written.
    $post_ids = array_values(array_filter($post_ids, static function ($id) {
        $post = get_post($id);
        return $post && $post->post_status === 'publish';
    }));

    $thumb_ids = [];
    foreach ($post_ids as $id) {
        $tid = get_post_thumbnail_id($id);
        if ($tid) {
            $thumb_ids[] = $tid;
        }
    }
    if ($thumb_ids) {
        _prime_post_caches($thumb_ids, false, true);
    }
}

if ($post_ids) :
    ?>

    <?php // data-cards-section hands the slider + flip behaviour to viewed.js,
          // which is the single implementation for every strip of these cards. ?>
    <section class="<?= esc_attr($block_classes) ?>" data-cards-section<?= $block_anchor ? ' id="' . esc_attr($block_anchor) . '"' : '' ?>>
        <div class="container">
            <div class="stock__wrapper">

                <?php if($title): ?>
                    <h2><?= esc_html($title); ?></h2>
                <?php endif; ?>

                <div class="swiper in-stock-slider">
                    <div class="swiper-wrapper">

                        <?php foreach ($post_ids as $post_id):
                            get_template_part('template-parts/components/stock-card', null, [
                                'post_id'  => $post_id,
                                'tap_text' => $tap_text,
                            ]);
                        endforeach; ?>

                    </div>
                </div>
            </div>
        </div>
    </section>

<?php endif; ?>