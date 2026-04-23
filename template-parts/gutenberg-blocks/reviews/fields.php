<?php
acf_add_local_field_group(array(
    'key'      => 'group_reviews',
    'title'    => 'All Posts',
    'fields'   => array(
        // MAIN TITLE
        array(
            'key'   => 'field_reviews_main_title',
            'label' => 'In Stock',
            'name'  => 'reviews_main_title',
            'type'  => 'text',
            'wrapper' => array(
                'width' => '100',
            ),
        ),

        // DESCRIPTION
        array(
            'key'   => 'field_reviews_tap_text',
            'label' => 'Tap Text',
            'name'  => 'reviews_tap_text',
            'type'  => 'text',
            'default'=> '123',
            'wrapper' => array(
                'width' => '100',
            ),
        ),

        array(
            'key' => 'field_reviews_gallery',
            'label' => 'Gallery',
            'name' => 'reviews_gallery',
            'type' => 'gallery',
            'return_format' => 'array',
            'preview_size' => 'medium',
            'insert' => 'append',
            'library' => 'all',
        ),
    ),

    // BIND TO BLOCK
    'location' => array(
        array(
            array(
                'param'    => 'block',
                'operator' => '==',
                'value'    => 'acf/reviews',
            ),
        ),
    ),
));
