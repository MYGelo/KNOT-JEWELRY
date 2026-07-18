<?php require_once get_template_directory() . '/inc/helpers/product.php';

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();

        $product = get_product_meta(get_the_ID());

        ?>


        <main class="product-page">
            <?php get_template_part('template-parts/components/breadcrumbs'); ?>

            <section class="product-single">
                <div class="container">
                    <div class="product">
                        <?php get_template_part('template-parts/product/gallery', null, $product); ?>
                        <?php get_template_part('template-parts/product/info', null, $product); ?>
                    </div>
                </div>
            </section>

            <?php get_template_part('template-parts/components/viewed-section', null, [
                'title'   => get_field('viewed_section_title', 'option') ?: 'Ви переглядали',
                'tap'     => get_field('viewed_section_tap_text', 'option') ?: 'Більше про виріб',
                'exclude' => get_the_ID(),
            ]); ?>

            <?php get_template_part('template-parts/product/comment', null, $product); ?>

            <?php get_template_part('template-parts/product/product-popup', null, $product); ?>

        </main>

    <?php
    endwhile;
endif;

get_footer(); ?>

<?php get_template_part('template-parts/popups/example-popup'); ?>