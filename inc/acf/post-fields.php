<?php
/**
 * Sizes on the post editor.
 *
 * Three steps, each revealing the next:
 *   1. the product is put in the «В наявності» category → this box appears;
 *   2. the switch says the piece is made in sizes → the size list appears;
 *   3. the sizes in stock are ticked.
 *
 * Availability itself is not repeated here — it is the category, and that stays
 * the single source of truth.
 */

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $sizes = [];
    foreach (knot_get_ring_sizes() as $size) {
        $sizes[$size] = $size;
    }

    // The whole box only exists for products in the «В наявності» category —
    // sizes of something that is not in stock are nobody's business. Ticking or
    // unticking the category in the editor shows and hides it.
    // The rule takes taxonomy:slug directly. Looking the term up here would be
    // unreliable: acf/init can fire before WordPress has registered its default
    // taxonomies, and get_term_by() would quietly return false.
    acf_add_local_field_group([
        'key'      => 'group_knot_stock',
        'title'    => 'Розміри',
        'location' => [[
            ['param' => 'post_type', 'operator' => '==', 'value' => 'post'],
            ['param' => 'post_category', 'operator' => '==', 'value' => 'category:' . KNOT_STOCK_CATEGORY],
        ]],
        // Sits with the other meta boxes, above the old groups, rather than in
        // the sidebar where it is easy to miss.
        'position'   => 'normal',
        'menu_order' => 0,
        'fields'   => [
            [
                // Availability itself is the `in-stock` category — no need to
                // mirror it here. What the editor cannot know is whether this
                // particular piece is made in sizes.
                'key'     => 'field_knot_has_sizes',
                'label'   => 'Виріб має розміри',
                'name'    => KNOT_STOCK_HAS_SIZES_META,
                'type'    => 'true_false',
                'ui'      => 1,
                'ui_on_text'  => 'Так',
                'ui_off_text' => 'Ні',
                'instructions' => 'За замовчуванням увімкнено для каблучок і сетів.',
            ],
            [
                'key'   => 'field_knot_stock_sizes',
                'label' => 'Розміри в наявності',
                'name'  => KNOT_STOCK_SIZES_META,
                // Checkboxes rather than a multi-select: picking several values
                // in a <select multiple> needs Ctrl/Cmd-click, which people
                // reasonably do not guess.
                'type'    => 'checkbox',
                'choices' => $sizes,
                'layout'  => 'horizontal',
                'instructions' => 'Показуються на картці окремим рядком у характеристиках.',
                'return_format' => 'value',
                'conditional_logic' => [[['field' => 'field_knot_has_sizes', 'operator' => '==', 'value' => '1']]],
            ],
        ],
    ]);
});

add_action('admin_enqueue_scripts', static function ($hook) {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }

    // Products only — pages and other types have no size pills to style.
    $screen = get_current_screen();

    if (!$screen || $screen->post_type !== 'post') {
        return;
    }

    $css = get_template_directory() . '/assets/admin/post-fields.css';

    if (file_exists($css)) {
        wp_enqueue_style(
            'knot-post-fields',
            get_template_directory_uri() . '/assets/admin/post-fields.css',
            [],
            filemtime($css)
        );
    }
});

/**
 * The old "Single Post In-Stock" group (created in the ACF UI) edits the same
 * availability as a free-text field, so leaving it on screen means two controls
 * disagreeing about one thing. Hidden here; the group itself can be deleted in
 * Custom Fields → Field Groups, along with the leftover `in-stock` meta.
 */
add_filter('acf/prepare_field/name=in-stock', '__return_false');

/** Unset switch: fall back to the guess made from the product type. */
add_filter('acf/load_value/key=field_knot_has_sizes', static function ($value, $post_id) {
    $id = (int) $post_id;

    if ($value !== null && $value !== '') {
        return $value;
    }

    return $id ? knot_product_needs_ring_size($id) : $value;
}, 10, 2);

/** Switched off: the sizes stored earlier would otherwise keep showing. */
add_filter('acf/update_value/key=field_knot_has_sizes', static function ($value, $post_id) {
    $id = (int) $post_id;

    if ($id && !$value) {
        delete_post_meta($id, KNOT_STOCK_SIZES_META);
    }

    return $value;
}, 10, 2);

/** Keep only real sizes, sorted — same rule the table applies. */
add_filter('acf/update_value/key=field_knot_stock_sizes', static function ($value) {
    return knot_sanitize_stock_sizes((array) $value) ?: null;
});
