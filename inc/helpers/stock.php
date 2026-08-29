<?php
/**
 * Availability: whether a product is in stock and, for rings, which sizes are.
 *
 * The sizes used to live inside the `in-stock` meta as free text ("В наявності
 * – 15.5 розмір"), which meant they could not be listed separately or checked
 * against anything. They are now their own meta — an array of values from
 * knot_get_ring_sizes() — while the old string is still read as a fallback so
 * nothing disappears from the site before every product has been filled in.
 */

const KNOT_STOCK_CATEGORY = 'in-stock';
const KNOT_STOCK_SIZES_META = 'stock_sizes';
const KNOT_STOCK_HAS_SIZES_META = 'has_sizes';
const KNOT_STOCK_LEGACY_META = 'in-stock';

function knot_stock_label(): string {
    return (string) apply_filters('knot_stock_label', 'В наявності');
}

function knot_product_in_stock(int $post_id): bool {
    return has_term(KNOT_STOCK_CATEGORY, 'category', $post_id);
}

/**
 * Whether this product is sold in sizes at all.
 *
 * Rings and sets are, earrings and pendants are not — but that is a guess from
 * the type, and guesses have exceptions. The switch in the editor overrides it;
 * until someone sets it, the guess stands.
 */
function knot_product_has_sizes(int $post_id): bool {
    $stored = get_post_meta($post_id, KNOT_STOCK_HAS_SIZES_META, true);

    if ($stored !== '') {
        return (bool) $stored;
    }

    return knot_product_needs_ring_size($post_id);
}

/**
 * Sizes currently in stock, newest storage first, legacy string second.
 *
 * @return string[] Values as stored, e.g. ['15.0', '16.5'].
 */
function knot_get_stock_sizes(int $post_id): array {
    $stored = get_post_meta($post_id, KNOT_STOCK_SIZES_META, true);

    if (is_array($stored) && $stored) {
        return array_values(array_filter(array_map('strval', $stored)));
    }

    return knot_parse_legacy_stock_sizes((string) get_post_meta($post_id, KNOT_STOCK_LEGACY_META, true));
}

/**
 * Pull sizes out of the old free-text value: "В наявності — 13.0, 14.5 розміри".
 * Read-only — nothing is written back, so the original text stays untouched
 * until someone saves the product with the new selector.
 */
function knot_parse_legacy_stock_sizes(string $stored): array {
    if ($stored === '') {
        return [];
    }

    if (!preg_match_all('/\d+(?:[.,]\d)?/u', $stored, $matches)) {
        return [];
    }

    $allowed = knot_get_ring_sizes();
    $sizes   = [];

    foreach ($matches[0] as $match) {
        $size = number_format((float) str_replace(',', '.', $match), 1, '.', '');

        if (in_array($size, $allowed, true) && !in_array($size, $sizes, true)) {
            $sizes[] = $size;
        }
    }

    return $sizes;
}

/** Keep only values that are real sizes — used before anything is stored. */
function knot_sanitize_stock_sizes(array $sizes): array {
    $allowed = knot_get_ring_sizes();

    $clean = array_values(array_unique(array_filter(
        array_map('strval', $sizes),
        static fn(string $size): bool => in_array($size, $allowed, true)
    )));

    usort($clean, static fn(string $a, string $b): int => (float) $a <=> (float) $b);

    return $clean;
}
