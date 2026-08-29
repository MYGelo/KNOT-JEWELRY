<?php
/**
 * Single stock-card slide (shared by the in-stock block and the viewed-posts
 * REST endpoint so the markup stays identical).
 *
 * @var array $args ['post_id' => int, 'tap_text' => string]
 */
$post_id  = (int) ($args['post_id'] ?? 0);
$tap_text = $args['tap_text'] ?? '';

if (!$post_id) {
    return;
}

$meta     = get_post_meta($post_id);
$price    = $meta['price'][0] ?? '';
$in_stock = $meta['in-stock'][0] ?? '';

$desc = get_the_excerpt($post_id);
$link = get_permalink($post_id);

// Served from the term cache primed by whoever renders the strip — see the
// _prime_post_caches() calls with the term flag on.
$specs = [];
foreach (['stone' => 'Камінь', 'material' => 'Матеріал', 'product_type' => 'Тип'] as $taxonomy => $label) {
    $terms = get_the_terms($post_id, $taxonomy);

    if ($terms && !is_wp_error($terms)) {
        $specs[$label] = implode(', ', wp_list_pluck($terms, 'name'));
    }
}

$thumb_id   = get_post_thumbnail_id($post_id);
$img_large  = '';
$img_mobile = '';
$width      = '';
$height     = '';
$alt        = '';
$title_img  = '';

if ($thumb_id) {
    $img_large  = wp_get_attachment_image_url($thumb_id, 'large');
    $img_mobile = wp_get_attachment_image_url($thumb_id, 'medium_large');

    $meta_img = wp_get_attachment_metadata($thumb_id);
    $width    = $meta_img['width'] ?? '';
    $height   = $meta_img['height'] ?? '';

    // Product name beats the attachment's own title (often "IMG_1234").
    $alt = function_exists('knot_image_alt')
        ? knot_image_alt($thumb_id, get_the_title($post_id))
        : get_post_meta($thumb_id, '_wp_attachment_image_alt', true);

    $title_img = get_the_title($thumb_id);
}
?>

<div class="swiper-slide">
    <div class="stock-card" data-link="<?= esc_url($link) ?>">
        <div class="stock-card-inner">

            <div class="stock-card-front image-wrapper">

                <?php if (!empty($img_large)): ?>
                    <picture>
                        <source srcset="<?= esc_url($img_mobile); ?>" media="(max-width:551px)">
                        <source srcset="<?= esc_url($img_large); ?>" media="(min-width:552px)">
                        <img
                                src="<?= esc_url($img_large); ?>"
                                alt="<?= esc_attr($alt ?: $title_img); ?>"
                                width="<?= esc_attr($width); ?>"
                                height="<?= esc_attr($height); ?>"
                                loading="lazy"
                                decoding="async"
                                fetchpriority="low"
                        >
                    </picture>
                <?php endif; ?>

                <?php if ($tap_text): ?>
                    <p class="stock-card-hint-text"><?= esc_html($tap_text); ?></p>
                <?php endif; ?>

                <div class="stock-card-hint">
                    <svg width="22" height="22" viewBox="0 0 24 24">
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2"
                              d="M4 4v6h6m10 10v-6h-6m6-8A8 8 0 0 0 6 8M4 18a8 8 0 0 0 14-2"/>
                    </svg>
                </div>

            </div>

            <div class="stock-card-back">

                <?php // No handler of its own any more — a click anywhere on this
                      // face closes the card, ✕ included. ?>
                <div class="stock-close" aria-hidden="true">
                    <span></span>
                    <span></span>
                </div>

                <?php // Everything above the footer scrolls together, so a long
                      // name or an extra stone never pushes the price out of
                      // sight. The footer below stays put. ?>
                <div class="stock-card-scroll">
                    <h3><?= esc_html(get_the_title($post_id)) ?></h3>

                    <?php if ($in_stock): ?>
                        <p class="product-stock"><?= esc_html($in_stock) ?></p>
                    <?php endif; ?>

                    <?php if ($specs): ?>
                        <dl class="stock-specs">
                            <?php foreach ($specs as $label => $value): ?>
                                <div class="stock-specs__row">
                                    <dt><?= esc_html($label) ?></dt>
                                    <dd><?= esc_html($value) ?></dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>
                    <?php endif; ?>

                    <p class="stock-text"><?= esc_html($desc) ?></p>
                </div>

                <?php // The one element that navigates. Everything else on this
                      // face flips the card back, so a second tap closes it
                      // instead of leaving the page unexpectedly. Being a real
                      // <a> also gives keyboard access, "open in new tab" and a
                      // crawlable internal link. ?>
                <div class="stock-card-foot">
                    <?php if ($price): ?>
                        <div class="stock-price"><?= esc_html($price) ?> грн</div>
                    <?php endif; ?>

                    <a class="stock-card-cta" href="<?= esc_url($link) ?>">
                        Дивитись
                        <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/>
                        </svg>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
