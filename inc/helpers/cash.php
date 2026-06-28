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


/* =========================================================
| PRELOAD BLOCK CACHE — інвалідація при оновленні посту
========================================================= */

add_action('save_post', function($post_id) {
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;
    delete_transient('knot_first_block_name_' . $post_id);
    delete_transient('knot_first_block_imgs_' . $post_id);
});


/* =========================================================
| CSS FILES LIST CACHE — інвалідація при зміні теми
========================================================= */

add_action('upgrader_process_complete', function() { delete_transient('knot_css_files_list'); });
add_action('switch_theme', function() { delete_transient('knot_css_files_list'); });


/* =========================================================
| TAXONOMY TERMS CACHE
========================================================= */

function knot_get_cached_terms(string $taxonomy): array {
    $key    = 'knot_tax_terms_' . $taxonomy;
    $cached = get_transient($key);
    if ($cached !== false) return is_array($cached) ? $cached : [];

    $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
    $terms = is_wp_error($terms) ? [] : $terms;
    set_transient($key, $terms, DAY_IN_SECONDS);
    return $terms;
}

add_action('created_term', function($id, $tt, $taxonomy) { delete_transient('knot_tax_terms_' . $taxonomy); }, 10, 3);
add_action('edited_term',  function($id, $tt, $taxonomy) { delete_transient('knot_tax_terms_' . $taxonomy); }, 10, 3);
add_action('delete_term',  function($id, $tt, $taxonomy) { delete_transient('knot_tax_terms_' . $taxonomy); }, 10, 3);


/* =========================================================
| ADMIN: КНОПКА "ОЧИСТИТИ ВСІ КЕШІ" (Global Settings)
========================================================= */

add_action('admin_notices', function() {
    if (!is_admin() || !current_user_can('manage_options')) return;
    if (empty($_GET['page']) || $_GET['page'] !== 'global_settings') return;

    $cleared = isset($_GET['knot_cache_cleared']);
    ?>
    <div class="notice notice-info" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;padding:10px 12px;">
        <strong>🗄 Кеш сайту:</strong>
        <?php if ($cleared): ?>
            <span style="color:#46b450;font-weight:600;">✓ Всі кеші очищено!</span>
        <?php endif; ?>
        <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>">
            <?php wp_nonce_field('knot_clear_all_caches', 'knot_cache_nonce'); ?>
            <input type="hidden" name="action" value="knot_clear_all_caches">
            <button type="submit" class="button button-primary">Очистити всі кеші</button>
        </form>
    </div>
    <?php
});

add_action('admin_post_knot_clear_all_caches', function() {
    if (!current_user_can('manage_options')) wp_die('Недостатньо прав.');
    if (!isset($_POST['knot_cache_nonce']) || !wp_verify_nonce($_POST['knot_cache_nonce'], 'knot_clear_all_caches')) wp_die('Невірний nonce.');

    global $wpdb;

    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_knot\_%' OR option_name LIKE '_transient_timeout_knot\_%'");
    delete_transient('in_stock_posts');
    delete_transient('all_comments_block');
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_reviews\_block\_%' OR option_name LIKE '_transient_timeout_reviews\_block\_%'");
    update_option('filter_cache_version', time());

    wp_redirect(add_query_arg('knot_cache_cleared', '1', admin_url('admin.php?page=global_settings')));
    exit;
});