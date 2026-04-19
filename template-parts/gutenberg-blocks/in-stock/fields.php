<?php
acf_add_local_field_group(array(
    'key'      => 'group_in-stock',
    'title'    => 'All Posts',
    'fields'   => array(
        // MAIN TITLE
        array(
            'key'   => 'field_in-stock_main_title',
            'label' => 'In Stock',
            'name'  => 'in-stock_main_title',
            'type'  => 'text',
            'wrapper' => array(
                'width' => '100',
            ),
        ),

        // DESCRIPTION
        array(
            'key'   => 'field_in-stock_tap_text',
            'label' => 'Tap Text',
            'name'  => 'in-stock_tap_text',
            'type'  => 'text',
            'default'=> '123',
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
                'value'    => 'acf/in-stock',
            ),
        ),
    ),
));
