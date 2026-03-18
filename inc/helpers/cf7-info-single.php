<?php add_filter('wpcf7_form_hidden_fields', function($hidden_fields) {

    if (is_singular()) {
        $hidden_fields['product-title'] = get_the_title();

        $material = get_the_terms(get_the_ID(), 'material');
        $stone    = get_the_terms(get_the_ID(), 'stone');
        $type     = get_the_terms(get_the_ID(), 'product_type');

        $hidden_fields['product-material'] = $material && !is_wp_error($material) ? $material[0]->name : '';
        $hidden_fields['product-stone']    = $stone && !is_wp_error($stone) ? $stone[0]->name : '';
        $hidden_fields['product-type']     = $type && !is_wp_error($type) ? $type[0]->name : '';

        $hidden_fields['product-price'] = get_field('price') ?: '';
    }

    return $hidden_fields;
});

add_filter('wpcf7_autop_or_not', '__return_false');