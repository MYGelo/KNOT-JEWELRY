<?php
$title = get_field('add_comments_main_title');
$desc  = get_field('add_comments_description');

$block_anchor = $block['anchor'] ?? '';
$block_classes = 'add-comments';

if (!empty($block['className'])) {
    $block_classes .= ' ' . $block['className'];
}

/*
====================
CACHE COMMENTS
====================
*/
$comments = get_transient('all_comments_block');

if ($comments === false) {

    $comments = get_comments([
        'status'  => 'approve',
        'number'  => 0, // все комментарии
        'orderby' => 'comment_date_gmt',
        'order'   => 'DESC'
    ]);

    set_transient('all_comments_block', $comments, 10 * MINUTE_IN_SECONDS);
}

if ($comments) : ?>

    <section class="<?= esc_attr($block_classes); ?>" id="<?= esc_attr($block_anchor); ?>">

        <div class="container">

            <?php if(!empty($title)): ?>
                <h2><?=wp_kses_post($title);?></h2>
            <?php endif; ?>

            <?php if(!empty($desc)): ?>
                <p class="add-comments__desc"><?=wp_kses_post($desc);?></p>
            <?php endif; ?>

            <div class="comments-wrapper">

                <div class="swiper comments-slider">
                    <div class="swiper-wrapper">

                        <?php foreach ($comments as $comment):

                            $photo = get_comment_meta($comment->comment_ID, 'comment_photo', true);

                            $post_link = get_permalink($comment->comment_post_ID);

                            ?>

                            <div class="swiper-slide">

                                <div class="comment-card">
                                    <!-- PHOTO -->

                                    <div class="comment-photo image-wrapper">
                                        <?php if ($photo): ?>
                                        <img
                                                src="<?= esc_url($photo); ?>"
                                                loading="lazy"
                                                decoding="async"
                                                alt="comment photo"
                                        >
                                        <?php endif; ?>

                                        <div class="stock-card-back-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"><path d="M17 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h6a1 1 0 0 0 0-2H5a3 3 0 0 0-3 3v11a3 3 0 0 0 3 3h11a3 3 0 0 0 3-3v-6a1 1 0 0 0-2 0m-6.3 1.7L20 5.42V9a1 1 0 0 0 2 0V3a1 1 0 0 0-.3-.7A1 1 0 0 0 21 2h-6a1 1 0 0 0 0 2h3.59l-9.3 9.3a1 1 0 1 0 1.42 1.4"></path></svg>
                                        </div>
                                    </div>

                                    <!-- TEXT -->
                                    <div class="comment-text">
                                        <?= esc_html($comment->comment_content); ?>
                                    </div>


                                    <!-- FOOTER -->
                                    <div class="comment-footer">

                                    <span class="comment-author">
                                        <?= esc_html($comment->comment_author ?: 'Аноним'); ?>
                                    </span>

                                        <span class="comment-date">
                                        <?= esc_html(get_comment_date('d.m.Y', $comment)); ?>
                                    </span>

                                    </div>

                                    <!-- LINK TO POST -->
                                    <?php if ($post_link): ?>
                                        <a class="comment-link" href="<?= esc_url($post_link); ?>">

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

<?php endif; ?>