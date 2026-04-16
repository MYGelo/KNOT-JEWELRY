<?php
$main_title = get_field('select-post_main_title') ?? '';
$selected_posts = get_field('select_post');

$block_anchor  = $block['anchor'] ?? '';
$block_classes = 'select-post';
if (!empty($block['className'])) $block_classes .= ' ' . $block['className'];
?>

<section class="<?= esc_attr($block_classes) ?>" id="<?= esc_attr($block_anchor) ?>">
    <div class="container">
        <?php if ($main_title): ?>
            <h2 class="select-post__title"><?= wp_kses_post($main_title); ?></h2>
        <?php endif; ?>
    </div>

        <?php if ($selected_posts && is_array($selected_posts)): ?>
            <div class="select-post__wrap">
<!--                <div class="select_post__swiper-button-prev">-->
<!--                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="32" height="32"><path d="m12.59 27.41-10-10a2 2 0 0 1 0-2.82l10-10A2 2 0 1 1 15.4 7.4L8.83 14H28a2 2 0 1 1 0 4H8.83l6.58 6.59a2 2 0 0 1 0 2.82 2 2 0 0 1-2.82 0"/></svg>-->
<!--                </div>-->

                <div class="swiper select-post__swiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($selected_posts as $post): ?>
                            <?php
                            $post_id    = is_object($post) ? $post->ID : $post;
                            $post_title = get_the_title($post_id);
                            $post_link  = get_permalink($post_id);

                            $thumb_id = get_post_thumbnail_id($post_id);

                            // размеры
                            $img_large  = wp_get_attachment_image_src($thumb_id, 'large');
                            $img_medium = wp_get_attachment_image_src($thumb_id, 'medium_large');
                            $img_full   = wp_get_attachment_image_src($thumb_id, 'full');

                            $alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true) ?: $post_title;
                            ?>

                            <div class="swiper-slide select-post__item">
                                <?php if ($thumb_id): ?>
                                    <picture>
                                        <!-- Mobile --><source srcset="<?= esc_url($img_medium[0]); ?>" media="(max-width: 551px)">
                                        <!-- Desktop --><source srcset="<?= esc_url($img_large[0]); ?>" media="(min-width: 552px)">

                                        <img
                                                src="<?= esc_url($img_large[0]); ?>"
                                                alt="<?= esc_attr($alt); ?>"
                                                width="<?= esc_attr($img_large[1]); ?>"
                                                height="<?= esc_attr($img_large[2]); ?>"
                                                loading="lazy"
                                                decoding="async"
                                                class="select-post__image"
                                        >
                                    </picture>
                                <?php endif; ?>

                                <!-- Надпись -->
                                <div class="slide-content">
                                    <h4 class="select-post__name"><?= esc_html($post_title); ?></h4>
                                </div>
                                <a class="slide-content__link" href="<?= esc_url($post_link); ?>"></a>
                            </div>

                        <?php endforeach; ?>
                    </div>
                </div>

<!--                <div class="select_post__swiper-button-next">-->
<!--                    <svg style="rotate: 180deg" xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="32" height="32"><path d="m12.59 27.41-10-10a2 2 0 0 1 0-2.82l10-10A2 2 0 1 1 15.4 7.4L8.83 14H28a2 2 0 1 1 0 4H8.83l6.58 6.59a2 2 0 0 1 0 2.82 2 2 0 0 1-2.82 0"/></svg>-->
<!--                </div>-->

        <?php endif; ?>
    </div>
</section>