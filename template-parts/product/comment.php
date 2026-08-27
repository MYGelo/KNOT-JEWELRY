<?php
$title = get_field('comments_main_title','option') ?: 'Щоденник майстра';
$subtitle = get_field('comments_subtitle_title','option') ?: 'Думки, враження та маленькі історії про прикраси';
$form_title = get_field('comments_form_title','option') ?: 'Залишити запис у щоденнику';

$label_name = get_field('comments_label_name','option') ?: 'Ім’я';
$placeholder_name = get_field('comments_label_name_placeholder','option') ?: 'Анна Клевлина';

$label_text = get_field('comments_label_text','option') ?: 'Коментар';
$placeholder_text = get_field('comments_label_text_placeholder','option') ?: 'Ваші враження, думки або просто “вау” — мені справді важливо це знати';

$button_text = get_field('comments_button_text','option') ?: 'Надіслати коментар';

// Shown instead of an empty list — announcing "(0)" reads as "nobody bought
// this", which is the opposite of what a hand-made jewellery page needs.
$empty_text = get_field('comments_empty_text','option')
    ?: 'Тут ще немає відгуків. Буду щиро рада, якщо Ви залишите перший.';

$comments_count = (int) get_comments_number();
?>

<section class="comments">
    <div class="container">
    <div class="comments__title-wrapper">
        <h3 class="comments-title scroll-animate">
            <?= esc_html($title) ?><?php // counter only once there is something to count ?>
            <span class="comments-title__count" <?= $comments_count ? '' : 'hidden' ?>>(<span id="comments-count"><?= esc_html($comments_count) ?></span>)</span>
        </h3>
        <p class="diary-subtitle scroll-animate"><?=$subtitle?></p>
    </div>

    <?php if (!$comments_count): ?>
        <p class="comments-empty scroll-animate"><?= wp_kses_post($empty_text) ?></p>
    <?php endif; ?>

    <div id="comments-list">
        <?php
            $comments = get_comments([
                'post_id' => get_the_ID(),
                'status'  => 'approve'
            ]);

            foreach ($comments as $comment) {
                echo render_comment_html($comment);
            }
        ?>
    </div>

    <div class="comment-form scroll-animate">
        <h3><?=$form_title?></h3>

        <input type="text" id="comment-hp" name="website" style="display:none">
        <input type="hidden" id="comment-time" value="<?php echo time(); ?>">

        <div class="styled">
            <label class="styled-label"><?=$label_name?></label>

            <input
                class="styled__input scroll-animate"
                type="text"
                id="comment-name"
                placeholder="<?=$placeholder_name?>"
            >
        </div>

        <div class="styled">
            <label class="styled-label"><?=$label_text?></label>

            <textarea
                class="styled__input scroll-animate"
                id="comment-text"
                placeholder="<?=$placeholder_text?>"
                required
            ></textarea>
        </div>

        <div class="styled">
            <label class="styled-label">Фото</label>

            <input
                    class="styled__input scroll-animate"
                    type="file"
                    id="comment-photo"
                    accept="image/jpeg,image/png,image"
            >
        </div>

        <button class="btn-buy main-btn third scroll-animate" id="comment-submit"><?=$button_text?></button>
    </div>
    </div>
</section>