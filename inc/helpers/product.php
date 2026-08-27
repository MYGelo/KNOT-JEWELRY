<?php

/**
 * Alt text for a product image.
 *
 * A manually written alt in the media library always wins. When it is empty we
 * fall back to the product name plus a short qualifier, so the gallery doesn't
 * end up with a dozen identical alts (which search engines treat as noise).
 */
function knot_image_alt(int $attachment_id, string $fallback, string $qualifier = ''): string {
    $alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));

    if ($alt !== '') {
        return $alt;
    }

    $fallback = trim($fallback);
    if ($fallback === '') {
        return '';
    }

    return $qualifier === '' ? $fallback : $fallback . ' — ' . $qualifier;
}

function get_product_meta($post_id): array {
    return [
        'price'     => get_post_meta($post_id, 'price', true),
        'old_price' => get_post_meta($post_id, 'old_price', true),
//        'metal'     => get_post_meta($post_id, 'metal', true),
//        'test'      => get_post_meta($post_id, 'test', true),
//        'stone'     => get_post_meta($post_id, 'stone', true),
        'gallery'   => (array) get_post_meta($post_id, 'gallery', true),
        'thumb_id'  => get_post_thumbnail_id($post_id),

        'note_mode' => get_field('product_note_settings', $post_id),
        'product_note_custom' => get_field('product_note_custom', $post_id),
    ];
}