<?php
/* =========================================================
| REVIEWS CACHE
========================================================= */

function clear_reviews_block_cache($post_id) {

    if (!$post_id) return;

    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    delete_transient('reviews_block_' . $post_id);
}

/* безопасный ACF хук */
add_action('acf/save_post', function($post_id) {

    if (!is_numeric($post_id)) return;

    clear_reviews_block_cache((int)$post_id);

}, 20);

add_action('save_post', 'clear_reviews_block_cache');


/* =========================================================
| STOCK CACHE
========================================================= */

function clear_in_stock_cache($post_id = null) {

    if ($post_id && get_post_type($post_id) !== 'post') {
        return;
    }

    delete_transient('in_stock_posts');
}

/* save post */
add_action('save_post', 'clear_in_stock_cache');

/* taxonomy changes (ограничено post type) */
add_action('set_object_terms', function($object_id, $terms, $tt_ids, $taxonomy) {

    if (get_post_type($object_id) !== 'post') return;

    clear_in_stock_cache($object_id);

}, 10, 4);

/* meta updates (ограничено post type) */
add_action('updated_post_meta', function($meta_id, $object_id, $meta_key) {

    if (get_post_type($object_id) !== 'post') return;

    clear_in_stock_cache($object_id);

}, 10, 3);


/* =========================================================
| FILTER CACHE VERSION
========================================================= */

function bump_filter_cache() {

    static $done = false;

    if ($done) return;
    $done = true;

    update_option('filter_cache_version', time());
}

add_action('save_post_post', 'bump_filter_cache');
add_action('created_term', 'bump_filter_cache');
add_action('edited_term', 'bump_filter_cache');
add_action('delete_term', 'bump_filter_cache');