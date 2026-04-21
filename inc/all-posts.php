<?php
    add_action('wp_ajax_load_more_posts', 'load_more_posts');
    add_action('wp_ajax_nopriv_load_more_posts', 'load_more_posts');

    function load_more_posts() {
        $paged = $_POST['page'];

        $query = new WP_Query([
            'post_type'      => 'post',
            'posts_per_page' => 9,
            'paged'          => $paged,
        ]);

        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post(); ?>
                <article class="post-item ">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="post-thumb">
                            <?php the_post_thumbnail('medium_large'); ?>
                        </div>
                    <?php endif; ?>

                    <h2><?php the_title(); ?></h2>
                    <?php the_excerpt(); ?>
                </article>
            <?php endwhile;
        endif;

        wp_reset_postdata();
        wp_die();
    }
?>