<?php
function clear_reviews_block_cache($post_id) {
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }
    delete_transient('reviews_block_' . $post_id);
}
add_action('save_post', 'clear_reviews_block_cache');


function clear_in_stock_cache($post_id = null) {

    if ($post_id && get_post_type($post_id) !== 'post') {
        return;
    }

    delete_transient('in_stock_posts');
}

add_action('save_post', 'clear_in_stock_cache');
add_action('set_object_terms', 'clear_in_stock_cache');
add_action('updated_post_meta', 'clear_in_stock_cache');