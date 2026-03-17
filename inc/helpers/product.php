<?php

function get_product_meta($post_id): array {
    return [
        'price'     => get_post_meta($post_id, 'price', true),
        'old_price' => get_post_meta($post_id, 'old_price', true),
//        'metal'     => get_post_meta($post_id, 'metal', true),
//        'test'      => get_post_meta($post_id, 'test', true),
//        'stone'     => get_post_meta($post_id, 'stone', true),
        'gallery'   => (array) get_post_meta($post_id, 'gallery', true),
        'thumb_id'  => get_post_thumbnail_id($post_id),
    ];
}