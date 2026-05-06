<?php

$cache_key = 'reviews_block_' . get_the_ID();
$cached = get_transient($cache_key);

if ($cached) {
    echo $cached;
    return;
}

ob_start();

$fields = get_fields();

$title    = $fields['reviews_main_title'] ?? '';
$tap_text = $fields['reviews_tap_text'] ?? '';
$items    = $fields['reviews_items'] ?? [];

if (empty($items)) {
    ob_end_clean(); // FIX: закрываем буфер
    return;
}

$block_anchor  = $block['anchor'] ?? '';
$block_classes = 'reviews' . (!empty($block['className']) ? ' ' . $block['className'] : '');

?>

    <section class="<?= esc_attr($block_classes); ?>" <?= $block_anchor ? 'id="'.esc_attr($block_anchor).'"' : ''; ?>>
        <div class="container">
            <div class="stock__wrapper">

                <?php if ($title): ?>
                    <h2><?= esc_html($title); ?></h2>
                <?php endif; ?>

                <div class="swiper reviews-slider">
                    <div class="swiper-wrapper">

                        <?php foreach ($items as $item):

                            $image = $item['image'];
                            $link  = $item['link'];

                            if (empty($image)) continue;

                            $image_id = $image['ID'] ?? 0;
                            if (!$image_id) continue;

                            // FIX: прогрев attachment cache (без изменения логики)
                            wp_get_attachment_image_src($image_id, 'large');

                            ?>

                            <div class="swiper-slide">
                                <div class="reviews-card">

                                    <?php if ($link): ?>
                                        <a href="<?= esc_url($link); ?>" class="reviews-card-link"></a>
                                    <?php endif; ?>

                                    <div class="reviews__image-wrapper">

                                        <?php
                                        // FIX: защита от битых картинок
                                        $img = wp_get_attachment_image(
                                            $image_id,
                                            'large',
                                            false,
                                            [
                                                'loading'  => 'lazy',
                                                'decoding' => 'async',
                                                'class'    => 'reviews-img'
                                            ]
                                        );

                                        if ($img) {
                                            echo $img;
                                        } else {
                                            // fallback чтобы не ломать верстку
                                            echo '<div class="reviews-img-placeholder"></div>';
                                        }
                                        ?>

                                        <div class="svg-wrapper">

                                            <?php if ($link): ?>
                                                <a href="<?= esc_url($link); ?>" class="stock-svg-link"></a>
                                            <?php endif; ?>

                                            <svg xmlns="http://www.w3.org/2000/svg" width="800" height="800" viewBox="0 0 2500 2500">
                                                <defs>
                                                    <radialGradient id="a" cx="332.14" cy="2511.81" r="3263.54">
                                                        <stop offset=".09" stop-color="#fa8f21"/>
                                                        <stop offset=".78" stop-color="#d82d7e"/>
                                                    </radialGradient>
                                                    <radialGradient id="b" cx="1516.14" cy="2623.81" r="2572.12">
                                                        <stop offset=".64" stop-color="#8c3aaa" stop-opacity="0"/>
                                                        <stop offset="1" stop-color="#8c3aaa"/>
                                                    </radialGradient>
                                                </defs>
                                                <path fill="url(#a)" d="M833.4 1250c0-230.11 186.49-416.7 416.6-416.7s416.7 186.59 416.7 416.7-186.59 416.7-416.7 416.7-416.6-186.59-416.6-416.7"/>
                                                <path fill="url(#b)" d="M833.4 1250c0-230.11 186.49-416.7 416.6-416.7s416.7 186.59 416.7 416.7-186.59 416.7-416.7 416.7-416.6-186.59-416.6-416.7"/>
                                            </svg>

                                        </div>

                                    </div>

                                    <?php if ($tap_text): ?>
                                        <a href="<?= esc_url($link); ?>" class="reviews-card-link">
                                            <p class="reviews-card-hint-text">
                                                <?= esc_html($tap_text); ?>
                                            </p>
                                        </a>
                                    <?php endif; ?>

                                </div>
                            </div>

                        <?php endforeach; ?>

                    </div>
                </div>

            </div>
        </div>
    </section>

<?php

$html = ob_get_clean();

// FIX: сохраняем только если реально есть контент
if (!empty($html)) {
    set_transient($cache_key, $html, 12 * HOUR_IN_SECONDS);
}

echo $html;