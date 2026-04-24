<?php
$price_meta = get_post_meta(get_the_ID(), 'price', true);
?>

<div class="all-posts__post-item">

    <a href="<?= esc_url(get_permalink()) ?>"
       class="all-posts__post-thumb image-wrapper">

        <?= get_the_post_thumbnail(
            get_the_ID(),
            'medium_large',
            ['loading' => 'lazy']
        ); ?>

    </a>

    <div class="all-post__text-content">

        <h2 class="all-posts__item-title">
            <?php the_title(); ?>
        </h2>

        <div class="all-posts__categories">

            <?php
            foreach (['material','stone'] as $tax) {

                $terms = get_the_terms(get_the_ID(), $tax);

                if ($terms && !is_wp_error($terms)) {

                    foreach ($terms as $term) {

                        echo '<span class="all-posts__category">'
                            . esc_html($term->name)
                            . '</span>';

                    }
                }
            }
            ?>

        </div>

    </div>

    <?php if ($price_meta): ?>

        <p class="all-posts__price">
            <?= esc_html($price_meta); ?>
            <span>грн</span>
        </p>

    <?php endif; ?>

</div>