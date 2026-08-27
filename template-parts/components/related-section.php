<?php
/**
 * "Схожі вироби" — server-rendered suggestions for a product page.
 *
 * Unlike "Ви переглядали" (which mirrors the visitor's own history and is empty
 * on a first visit) this is always populated: it is derived from the product
 * itself. Products of the same type come first, with a matching stone ranked
 * above the rest, so the page never dead-ends.
 *
 * @var array $args ['post_id', 'title', 'tap', 'limit']
 */

$post_id = (int) ($args['post_id'] ?? get_the_ID());
$title   = $args['title'] ?? 'Схожі вироби';
$tap     = $args['tap']   ?? 'Більше про виріб';
$limit   = (int) ($args['limit'] ?? 8);

if (!$post_id) {
    return;
}

$term_slugs = static function (string $taxonomy) use ($post_id): array {
    $terms = get_the_terms($post_id, $taxonomy);
    return ($terms && !is_wp_error($terms)) ? wp_list_pluck($terms, 'slug') : [];
};

$types  = $term_slugs('product_type');
$stones = $term_slugs('stone');

if (!$types && !$stones) {
    return;
}

$base_args = [
    'post_type'              => 'post',
    'post_status'            => 'publish',
    'post__not_in'           => [$post_id],
    'posts_per_page'         => $limit,
    'fields'                 => 'ids',
    'no_found_rows'          => true,
    'ignore_sticky_posts'    => true,
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false,
];

// Closest match first: same type AND same stone.
$ids = [];

if ($types && $stones) {
    $ids = get_posts(array_merge($base_args, [
        'tax_query' => [
            'relation' => 'AND',
            ['taxonomy' => 'product_type', 'field' => 'slug', 'terms' => $types],
            ['taxonomy' => 'stone', 'field' => 'slug', 'terms' => $stones],
        ],
    ]));
}

// Then top up with the same type (or, failing that, the same stone).
if (count($ids) < $limit) {
    $fallback_tax = $types
        ? ['taxonomy' => 'product_type', 'field' => 'slug', 'terms' => $types]
        : ['taxonomy' => 'stone', 'field' => 'slug', 'terms' => $stones];

    $more = get_posts(array_merge($base_args, [
        'posts_per_page' => $limit - count($ids),
        'post__not_in'   => array_merge([$post_id], $ids),
        'tax_query'      => [$fallback_tax],
    ]));

    $ids = array_merge($ids, $more);
}

if (!$ids) {
    return;
}

// One pass for posts + meta, one for their images — no per-card queries.
_prime_post_caches($ids, false, true);

$thumb_ids = [];
foreach ($ids as $id) {
    $tid = get_post_thumbnail_id($id);
    if ($tid) {
        $thumb_ids[] = $tid;
    }
}
if ($thumb_ids) {
    _prime_post_caches($thumb_ids, false, true);
}
?>

<?php // Same shell, classes and slider as the "Ви переглядали" section, so it
      // reuses that section's styles and its Swiper/flip init (viewed.js picks
      // up any [data-cards-section]). Only the source of the cards differs. ?>
<section class="in-stock viewed-posts related-posts" data-cards-section>
    <div class="container">
        <div class="stock__wrapper">

            <?php if ($title): ?>
                <h2><?= esc_html($title) ?></h2>
            <?php endif; ?>

            <div class="swiper viewed-slider">
                <div class="swiper-wrapper">
                    <?php foreach ($ids as $id): ?>
                        <?php get_template_part('template-parts/components/stock-card', null, [
                            'post_id'  => $id,
                            'tap_text' => $tap,
                        ]); ?>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</section>
