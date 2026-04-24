<?php
acf_add_local_field_group(array(
    'key'   => 'group_reviews',
    'title' => 'All Posts',
    'fields' => array(

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

        // TAP TEXT
        array(
            'key'   => 'field_reviews_tap_text',
            'label' => 'Tap Text',
            'name'  => 'reviews_tap_text',
            'type'  => 'text',
            'default_value' => '123',
            'wrapper' => array(
                'width' => '100',
            ),
        ),

        // REPEATER
        array(
            'key' => 'field_reviews_items',
            'label' => 'Items',
            'name' => 'reviews_items',
            'type' => 'repeater',
            'layout' => 'row',
            'button_label' => 'Add Item',
            'min' => 1,

            'sub_fields' => array(

                // IMAGE
                array(
                    'key' => 'field_reviews_item_image',
                    'label' => 'Image',
                    'name' => 'image',
                    'type' => 'image',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                    'wrapper' => array(
                        'width' => '70',
                    ),
                ),

                // LINK
                array(
                    'key' => 'field_reviews_item_link',
                    'label' => 'Item Link',
                    'name' => 'link',
                    'type' => 'url',
                    'wrapper' => array(
                        'width' => '30',
                    ),
                ),

            ),
        ),

    ),

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