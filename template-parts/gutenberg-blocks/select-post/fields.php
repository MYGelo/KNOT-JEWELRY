<?php
acf_add_local_field_group(array(
    'key'      => 'group_select-post',
    'title'    => 'Select Post',
    'fields'   => array(
        // MAIN TITLE
        array(
            'key'   => 'field_select-post_main_title',
            'label' => 'Main Title',
            'name'  => 'select-post_main_title',
            'type'  => 'text',
            'wrapper' => array(
                'width' => '100',
            ),
        ),
        // POST PICKER
        array(
            'key'   => 'field_select-post_picker',
            'label' => 'Select Post',
            'name'  => 'select_post',
            'type'  => 'relationship', // или 'relationship', если нужно выбрать несколько
            'post_type' => array('post'), // можно указать несколько типов: 'post', 'page', 'custom_post'
            'return_format' => 'object', // или 'id'
            'ui' => 1, // показывает красивый селектор
        ),
    ),

    // BIND TO BLOCK
    'location' => array(
        array(
            array(
                'param'    => 'block',
                'operator' => '==',
                'value'    => 'acf/select-post',
            ),
        ),
    ),
));
