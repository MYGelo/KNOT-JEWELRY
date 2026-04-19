<?php

$title = get_field('in-stock_main_title');
$tap_text = get_field('in-stock_tap_text');


$query = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => -1,
    'category_name' => 'in-stock'
]);

?>

<section class="in-stock">
    <div class="container">
        <div class="stock__wrapper">
            <?php if(!empty($title)): ?>
                <h2><?=wp_kses_post($title);?></h2>
            <?php endif; ?>

            <div class="swiper in-stock-slider">
                <div class="swiper-wrapper">
                    <?php while($query->have_posts()): $query->the_post(); ?>
                        <?php
                            $price = get_post_meta(get_the_ID(), 'price', true);
                            $desc = get_the_excerpt();
                            $link = get_permalink();
                        ?>

                        <div class="swiper-slide">
                            <div class="stock-card" data-link="<?= esc_url($link) ?>">
                                <div class="stock-card-inner">
                                    <div class="stock-card-front">
                                        <?php the_post_thumbnail('medium'); ?>

                                        <?php if(!empty($tap_text)): ?>
                                            <p class="stock-card-hint-text"><?=wp_kses_post($tap_text);?></p>
                                        <?php endif; ?>

                                        <div class="stock-card-hint">
                                            <svg width="22" height="22" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6m10 10v-6h-6m6-8A8 8 0 0 0 6 8M4 18a8 8 0 0 0 14-2"/></svg>
                                        </div>
                                    </div>

                                    <div class="stock-card-back">
                                        <div class="stock-card-back-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"><path d="M17 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h6a1 1 0 0 0 0-2H5a3 3 0 0 0-3 3v11a3 3 0 0 0 3 3h11a3 3 0 0 0 3-3v-6a1 1 0 0 0-2 0m-6.3 1.7L20 5.42V9a1 1 0 0 0 2 0V3a1 1 0 0 0-.3-.7A1 1 0 0 0 21 2h-6a1 1 0 0 0 0 2h3.59l-9.3 9.3a1 1 0 1 0 1.42 1.4"/></svg>
                                        </div>

                                        <div class="stock-close stock-close--js">
                                            <span></span>
                                            <span></span>
                                        </div>

                                        <h3><?php the_title(); ?></h3>

                                        <p class="stock-text"><?= esc_html($desc) ?></p>

                                        <?php if($price): ?>
                                            <div class="stock-price">
                                                <?= esc_html($price) ?> грн
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        </div>
    </div>
</section>