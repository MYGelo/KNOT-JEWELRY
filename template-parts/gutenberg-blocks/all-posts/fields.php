<?php
acf_add_local_field_group(array(
    'key'      => 'group_all-posts',
    'title'    => 'All Posts',
    'fields'   => array(
        array(
            'key' => 'field_all_posts_sticky',
            'label' => 'Use Sticky Filter & Search',
            'name' => 'all-posts_use_sticky',
            'type' => 'true_false',
            'ui' => 1,
            'ui_on_text' => 'On',
            'ui_off_text' => 'Off',
            'default_value' => 0,
            'instructions' => 'Enable to show sticky posts first or filter only sticky posts.',
        ),

        // MAIN TITLE
        array(
            'key'   => 'field_all-posts_main_title',
            'label' => 'Main Title',
            'name'  => 'all-posts_main_title',
            'type'  => 'text',
            'wrapper' => array(
                'width' => '100',
            ),
        ),

        // DESCRIPTION
        array(
            'key'   => 'field_all-posts_description',
            'label' => 'Description',
            'name'  => 'all-posts_description',
            'type'  => 'textarea',
            'wrapper' => array(
                'width' => '100',
            ),
        ),
    ),

    // BIND TO BLOCK
    'location' => array(
        array(
            array(
                'param'    => 'block',
                'operator' => '==',
                'value'    => 'acf/all-posts',
            ),
        ),
    ),
));
