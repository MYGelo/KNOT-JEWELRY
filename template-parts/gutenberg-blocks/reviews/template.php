<?php
$block_anchor = $block['anchor'] ?? '';
$block_classes = 'reviews';

if (!empty($block['className'])) {
    $block_classes .= ' ' . $block['className'];
}

$title = get_field('reviews_main_title');
$tap_text = get_field('reviews_tap_text');

$images = get_field('reviews_gallery');

if ($images) : ?>

    <section class="<?= esc_attr($block_classes) ?>" id="<?= esc_attr($block_anchor) ?>">
        <div class="container">
            <div class="stock__wrapper">

                <?php if ($title): ?>
                    <h2><?= esc_html($title); ?></h2>
                <?php endif; ?>

                <div class="swiper reviews-slider">
                    <div class="swiper-wrapper">

                        <?php foreach ($images as $img):
                            $img_id = $img['ID'];

                            $large = wp_get_attachment_image_url($img_id, 'large');
                            $mobile = wp_get_attachment_image_url($img_id, 'medium_large');

                            $meta = wp_get_attachment_metadata($img_id);

                            $width = $meta['width'] ?? '';
                            $height = $meta['height'] ?? '';

                            $alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                            $title_img = get_the_title($img_id);
                            ?>

                            <div class="swiper-slide">
                                <div class="stock-card">

                                    <picture>
                                        <source srcset="<?= esc_url($mobile); ?>" media="(max-width:551px)">
                                        <source srcset="<?= esc_url($large); ?>" media="(min-width:552px)">

                                        <img
                                            src="<?= esc_url($large); ?>"
                                            alt="<?= esc_attr($alt ?: $title_img); ?>"
                                            width="<?= esc_attr($width); ?>"
                                            height="<?= esc_attr($height); ?>"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    </picture>

                                    <?php if ($tap_text): ?>
                                        <p class="stock-card-hint-text">
                                            <?= wp_kses_post($tap_text); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php endif; ?>