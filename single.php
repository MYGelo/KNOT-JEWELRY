<?php require_once get_template_directory() . '/inc/helpers/product.php';

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();

        $product = get_product_meta(get_the_ID());

        ?>


        <main class="product-page">
            <?php get_template_part('template-parts/components/breadcrumbs'); ?>

            <div class="container">

                <section class="product scroll-animate">

                    <?php get_template_part('template-parts/product/gallery', null, $product); ?>
                    <?php get_template_part('template-parts/product/info', null, $product); ?>

                </section>

                <?php get_template_part('template-parts/product/comment', null, $product); ?>

                <?php get_template_part('template-parts/product/product-popup', null, $product); ?>

            </div>
        </main>

    <?php
    endwhile;
endif;

get_footer(); ?>

<?php get_template_part('template-parts/popups/example-popup'); ?>