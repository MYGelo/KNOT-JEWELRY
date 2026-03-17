<?php require_once get_template_directory() . '/inc/helpers/product.php';

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();

        $product = get_product_meta(get_the_ID());

        ?>

        <main class="product-page container">

            <section class="product scroll-animate">

                <?php get_template_part('template-parts/product/gallery', null, $product); ?>
                <?php get_template_part('template-parts/product/info', null, $product); ?>

            </section>

        </main>

    <?php
    endwhile;
endif;

get_footer(); ?>
