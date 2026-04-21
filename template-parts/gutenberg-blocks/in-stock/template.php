<?php
$title = get_field('in-stock_main_title');
$tap_text = get_field('in-stock_tap_text');

$posts = get_posts([
    'post_type' => 'post',
    'category_name' => 'in-stock',
    'posts_per_page' => -1,
    'no_found_rows' => true,
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false,
    'suppress_filters' => true
]);

if ($posts) : ?>

    <section class="in-stock">
        <div class="container">
            <div class="stock__wrapper">
                <?php if($title): ?>
                    <h2><?= wp_kses_post($title); ?></h2>
                <?php endif; ?>

                <div class="swiper in-stock-slider">
                    <div class="swiper-wrapper">
                        <?php foreach ($posts as $post):
                            setup_postdata($post);
                            $post_id = $post->ID;
                            $price = get_post_meta($post_id,'price',true);
                            $in_stock = get_post_meta($post_id,'in-stock',true);
                            $desc = get_the_excerpt($post_id);
                            $link = get_permalink($post_id);
                            /* image */
                            $thumb_id = get_post_thumbnail_id($post_id);

                            if($thumb_id){

                                $img_large = wp_get_attachment_image_url($thumb_id,'large');
                                $img_mobile = wp_get_attachment_image_url($thumb_id,'medium_large');

                                $meta = wp_get_attachment_metadata($thumb_id);

                                $width = $meta['width'] ?? '';
                                $height = $meta['height'] ?? '';

                                $alt = get_post_meta($thumb_id,'_wp_attachment_image_alt',true);
                                $title_img = get_the_title($thumb_id);

                            }
                            ?>

                            <div class="swiper-slide">
                                <div class="stock-card" data-link="<?= esc_url($link) ?>">
                                    <div class="stock-card-inner">
                                        <div class="stock-card-front">
                                            <?php if(!empty($img_large)): ?>
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
                                                    >
                                                </picture>
                                            <?php endif; ?>

                                            <?php if($tap_text): ?>
                                                <p class="stock-card-hint-text"><?= wp_kses_post($tap_text); ?></p>
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
                                            <div class="stock-close stock-close--js">
                                                <span></span>
                                                <span></span>
                                            </div>

                                            <div class="stock-card-back-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"><path d="M17 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h6a1 1 0 0 0 0-2H5a3 3 0 0 0-3 3v11a3 3 0 0 0 3 3h11a3 3 0 0 0 3-3v-6a1 1 0 0 0-2 0m-6.3 1.7L20 5.42V9a1 1 0 0 0 2 0V3a1 1 0 0 0-.3-.7A1 1 0 0 0 21 2h-6a1 1 0 0 0 0 2h3.59l-9.3 9.3a1 1 0 1 0 1.42 1.4"/></svg>
                                            </div>

                                            <h3><?= esc_html(get_the_title($post_id)) ?></h3>

                                            <?php if($in_stock): ?>
                                                <p class="product-stock"><?= wp_kses_post($in_stock) ?></p>
                                            <?php endif; ?>

                                            <p class="stock-text"><?= esc_html($desc) ?></p>

                                            <?php if($price): ?>
                                                <div class="stock-price"><?= esc_html($price) ?> грн</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php wp_reset_postdata(); ?>

<?php endif; ?>