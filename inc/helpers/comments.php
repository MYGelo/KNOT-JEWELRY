<?php
add_action('wp_ajax_nopriv_add_comment', 'add_comment_ajax');
add_action('wp_ajax_add_comment', 'add_comment_ajax');

function add_comment_ajax() {

    if(empty($_POST['post_id']) || empty($_POST['comment'])){
        wp_die();
    }

    // honeypot
    if(!empty($_POST['hp'])){
        wp_die();
    }

    // проверка времени (минимум 3 секунды)
    if(isset($_POST['time'])){
        $time = intval($_POST['time']);

        if(time() - $time < 3){
            wp_die();
        }
    }

    // лимит частоты комментариев
    $ip = $_SERVER['REMOTE_ADDR'];

    $last = get_transient('comment_'.$ip);

    if($last){
        wp_die();
    }

    set_transient('comment_'.$ip,1,5); // 10 секунд

    $post_id = intval($_POST['post_id']);
    $author  = sanitize_text_field($_POST['author']);
    $text    = sanitize_textarea_field($_POST['comment']);

    if(!$author){
        $author = 'Анонім';
    }

    $comment_id = wp_insert_comment([
        'comment_post_ID' => $post_id,
        'comment_author'  => $author,
        'comment_content' => $text,
        'comment_approved'=> 1
    ]);

    $comment = get_comment($comment_id);

    ob_start();

    echo render_comment_html($comment);

    echo ob_get_clean();

    wp_die();
}

function render_comment_html($comment){
    ob_start(); ?>

    <div class="comment scroll-animate">
        <div class="comment-body">
            <?php echo esc_html($comment->comment_content); ?>
        </div>

        <div class="comment-footer">
            <span class="comment-author"><?php echo esc_html($comment->comment_author ?: 'Аноним'); ?></span>
            <span class="comment-date">  <?php echo get_comment_date('d.m.Y', $comment); ?></span>
        </div>

    </div>

    <?php
    return ob_get_clean();
}