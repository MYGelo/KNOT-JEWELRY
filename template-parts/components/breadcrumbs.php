<?php
$home_url = apply_filters('wpml_home_url', get_home_url());
$front_page_id = apply_filters('wpml_object_id', get_option('page_on_front'), 'page', true);
$front_page_title = get_the_title($front_page_id);

// Blog page (WP posts page)
$blog_page_id = apply_filters('wpml_object_id', get_option('page_for_posts'), 'page', true);
$blog_page_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/blog/');
$blog_page_title = $blog_page_id ? get_the_title($blog_page_id) : 'Blog';

// Authors page (custom fallback for CPT archive)
$authors_page = get_page_by_path('authors');
$authors_title = $authors_page ? get_the_title($authors_page) : 'Authors';
?>

<div class="breadcrumbs">
    <div class="container">
        <ul>
            <?php if ( !is_front_page()) : ?>
                <!-- HOME -->
                <li class="breadcrumbs-home">
                    <a href="<?= esc_url($home_url) ?>">
                        <?= esc_html($front_page_title) ?>
                    </a>
                </li>

                <?php if (is_404()) : ?>
                    <!-- 404 -->
                    <li>
                        <span>404</span>
                    </li>

                <?php elseif (is_post_type_archive('authors')) : ?>
                    <!-- AUTHORS CPT ARCHIVE -->
                    <li>
                        <span><?= esc_html($authors_title) ?></span>
                    </li>

                <?php elseif (is_home() && !is_front_page()) : ?>
                    <!-- BLOG CPT / POSTS PAGE -->
                    <li>
                        <span><?= esc_html($blog_page_title) ?></span>
                    </li>

                <?php elseif (is_post_type_archive('blog')) : ?>
                    <!-- BLOG ARCHIVE (if CPT blog exists) -->
                    <li>
                        <span><?= esc_html($blog_page_title) ?></span>
                    </li>

                <?php elseif (is_singular('blog')) : ?>
                    <!-- SINGLE POST -->
                    <li class="breadcrumbs-home">
                        <a href="<?= esc_url($blog_page_url) ?>">
                            <?= esc_html($blog_page_title) ?>
                        </a>
                    </li>

                    <li>
                        <span><?php the_title(); ?></span>
                    </li>


                <?php elseif (is_singular('authors')) : ?>
                    <!-- SINGLE AUTHORS -->
                    <li class="breadcrumbs-home">
                        <a href="<?= esc_url(get_post_type_archive_link('authors')) ?>">
                            <?= esc_html($authors_title) ?>
                        </a>
                    </li>

                    <li>
                        <span><?php the_title(); ?></span>
                    </li>


                <?php else : ?>
                    <!-- DEFAULT -->
                    <li>
                        <span><?php the_title(); ?></span>
                    </li>
                <?php endif; ?>
            <?php endif;?>
        </ul>
    </div>
</div>