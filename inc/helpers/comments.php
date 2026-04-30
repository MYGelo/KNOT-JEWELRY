<?php

add_action('wp_ajax_nopriv_add_comment', 'add_comment_ajax');
add_action('wp_ajax_add_comment', 'add_comment_ajax');

function add_comment_ajax() {

    if(empty($_POST['post_id']) || empty($_POST['comment'])){
        wp_die();
    }

    if(!empty($_POST['hp'])){
        wp_die();
    }

    if(isset($_POST['time'])){
        $time = intval($_POST['time']);

        if(time() - $time < 3){
            wp_die();
        }
    }

    $ip = $_SERVER['REMOTE_ADDR'];

    if(get_transient('comment_'.$ip)){
        wp_die();
    }

    set_transient('comment_'.$ip,1,5);

    $post_id = intval($_POST['post_id']);
    $author  = sanitize_text_field($_POST['author']);
    $text    = sanitize_textarea_field($_POST['comment']);

    if(!$author){
        $author = 'Анонім';
    }

    $photo_url = '';

    /*
    =========================
    IMAGE UPLOAD + OPTIMIZE
    =========================
    */

    if(!empty($_FILES['photo']['name'])){

        $file = $_FILES['photo'];

        if($file['size'] > 3 * 1024 * 1024){
            wp_die();
        }

        $allowed = ['image/jpeg','image/png','image/webp'];

        if(!in_array($file['type'],$allowed)){
            wp_die();
        }

        require_once(ABSPATH.'wp-admin/includes/file.php');
        require_once(ABSPATH.'wp-admin/includes/image.php');

        add_filter('upload_dir','comment_upload_dir');

        $upload = wp_handle_upload($file,['test_form'=>false]);

        remove_filter('upload_dir','comment_upload_dir');

        if(isset($upload['file'])){

            $image_path = $upload['file'];

            $editor = wp_get_image_editor($image_path);

            if(!is_wp_error($editor)){

                $size = $editor->get_size();

                if($size['width'] > 1200){
                    $editor->resize(1200,null);
                }

                $webp_path = preg_replace('/\.(jpg|jpeg|png)$/i','.webp',$image_path);

                $editor->set_quality(75);

                $saved = $editor->save($webp_path,'image/webp');

                if(!is_wp_error($saved)){

                    unlink($image_path);

                    $upload_dir = wp_upload_dir();

                    $photo_url = str_replace(
                        $upload_dir['basedir'],
                        $upload_dir['baseurl'],
                        $webp_path
                    );

                }
            }
        }
    }

    $comment_id = wp_insert_comment([
        'comment_post_ID' => $post_id,
        'comment_author'  => $author,
        'comment_content' => $text,
        'comment_approved'=> 1
    ]);

    if($photo_url){
        add_comment_meta($comment_id,'comment_photo',$photo_url);
    }

    $comment = get_comment($comment_id);

    echo render_comment_html($comment);

    wp_die();
}

function comment_upload_dir($dirs){

    $dirs['subdir'] = '/comments';
    $dirs['path'] = $dirs['basedir'].'/comments';
    $dirs['url'] = $dirs['baseurl'].'/comments';

    return $dirs;
}

/*
====================
DELETE PHOTO
====================
*/

add_action('delete_comment','delete_comment_photo');

function delete_comment_photo($comment_id){

    $photo = get_comment_meta($comment_id,'comment_photo',true);

    if($photo){

        $upload_dir = wp_upload_dir();

        $file = str_replace($upload_dir['baseurl'],$upload_dir['basedir'],$photo);

        if(file_exists($file)){
            unlink($file);
        }

    }

}

/*
====================
COMMENT HTML
====================
*/

function render_comment_html($comment){

    $photo = get_comment_meta($comment->comment_ID,'comment_photo',true);

    ob_start(); ?>

    <div class="comment scroll-animate">

        <div class="comment-body">
            <?php echo esc_html($comment->comment_content); ?>
        </div>

        <?php if($photo): ?>
            <div class="comment-photo image-wrapper ">
                <div class="gallery-zoom">
                    <svg width="20" height="20" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M10 18a8 8 0 1 1 5.293-14.293A8 8 0 0 1 10 18m0-14a6 6 0 1 0 4.472 10.028l4.75 4.75l1.414-1.414l-4.75-4.75A6 6 0 0 0 10 4"></path>
                    </svg>
                </div>

                <img  class="comment-photo-img" src="<?php echo esc_url($photo); ?>" loading="lazy">
            </div>
        <?php endif; ?>

        <div class="comment-footer">
            <span class="comment-author">
                <?php echo esc_html($comment->comment_author ?: 'Аноним'); ?>
            </span>

            <span class="comment-date">
                <?php echo get_comment_date('d.m.Y',$comment); ?>
            </span>
        </div>

    </div>

    <?php

    return ob_get_clean();

}