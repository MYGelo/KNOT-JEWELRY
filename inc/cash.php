<?php add_action('save_post', function() {
    delete_transient('in_stock_posts');
});