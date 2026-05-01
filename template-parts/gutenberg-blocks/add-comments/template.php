<?php
$title = get_field('add_comments_main_title');
$desc  = get_field('add_comments_description');

$block_anchor = $block['anchor'] ?? '';
$block_classes = 'add-comments';

if (!empty($block['className'])) {
    $block_classes .= ' ' . $block['className'];
}

/*===== CACHE COMMENTS =====*/
$comments_data = get_transient('all_comments_block');

if ($comments_data === false) {

    $comments = get_comments([
        'status'  => 'approve',
        'number'  => 0,
        'orderby' => 'comment_date_gmt',
        'order'   => 'DESC',
        'no_found_rows' => true
    ]);

    $comments_data = [];

    if ($comments) {

        $comment_ids = [];
        $post_ids = [];

        foreach ($comments as $comment) {
            $comment_ids[] = $comment->comment_ID;
            $post_ids[]    = $comment->comment_post_ID;
        }

        /*===== PRELOAD COMMENT META =====*/
        update_meta_cache('comment', $comment_ids);

        /*===== PRELOAD POSTS =====*/
        $posts = get_posts([
            'post__in' => array_unique($post_ids),
            'numberposts' => -1
        ]);

        $thumbs = [];

        foreach ($posts as $post) {

            if (has_post_thumbnail($post->ID)) {
                $thumbs[$post->ID] = get_the_post_thumbnail_url($post->ID, 'medium');
            }

        }

        /*===== BUILD DATA ===== */
        foreach ($comments as $comment) {
            $photo = get_comment_meta($comment->comment_ID, 'comment_photo', true);
            $fallback = false;

            if (!$photo) {
                $post_id = $comment->comment_post_ID;

                if (!empty($thumbs[$post_id])) {
                    $photo = $thumbs[$post_id];
                    $fallback = true;
                }
            }

            $comments_data[] = [
                'comment'  => $comment,
                'photo'    => $photo,
                'fallback' => $fallback
            ];
        }
    }

    set_transient('all_comments_block', $comments_data, 10 * MINUTE_IN_SECONDS);
}

if ($comments_data) : ?>

    <section class="<?= esc_attr($block_classes); ?>" id="<?= esc_attr($block_anchor); ?>">

        <div class="container">

            <?php if(!empty($title)): ?>
                <h2><?= wp_kses_post($title); ?></h2>
            <?php endif; ?>

            <?php if(!empty($desc)): ?>
                <p class="add-comments__desc"><?= wp_kses_post($desc); ?></p>
            <?php endif; ?>

            <div class="comments-wrapper">

                <div class="swiper comments-slider">
                    <div class="swiper-wrapper">

                        <?php foreach ($comments_data as $item):

                            $comment  = $item['comment'];
                            $photo    = $item['photo'];
                            $fallback = $item['fallback'];
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
                                                    alt="<?= esc_attr($comment->comment_author ?: 'Аноним'); ?>"
                                            >
                                        <?php endif; ?>

                                        <div class="stock-card-back-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                                <path d="M17 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h6a1 1 0 0 0 0-2H5a3 3 0 0 0-3 3v11a3 3 0 0 0 3 3h11a3 3 0 0 0 3-3v-6a1 1 0 0 0-2 0m-6.3 1.7L20 5.42V9a1 1 0 0 0 2 0V3a1 1 0 0 0-.3-.7A1 1 0 0 0 21 2h-6a1 1 0 0 0 0 2h3.59l-9.3 9.3a1 1 0 1 0 1.42 1.4"></path>
                                            </svg>
                                        </div>

                                        <?php if ($fallback): ?>
                                            <p class="comment-photo-badge">
                                                Фото виробу
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="comment-text">
                                        <?= esc_html($comment->comment_content); ?>
                                    </div>

                                    <div class="comment-footer">
                                        <span class="comment-author">
                                            <?= esc_html($comment->comment_author ?: 'Аноним'); ?>
                                        </span>

                                        <span class="comment-date">
                                            <?= esc_html(get_comment_date('d.m.Y', $comment)); ?>
                                        </span>
                                    </div>

                                    <?php if ($post_link): ?>
                                        <a class="comment-link" href="<?= esc_url($post_link); ?>"></a>
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