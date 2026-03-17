<?php

acf_add_local_field_group(array(
    'key'      => 'group_header',
    'title'    => 'Header',
    'fields'   => array(
        array(
            'key'           => 'header_logo',
            'label'         => 'Header Logo',
            'name'          => 'header_logo',
            'type'          => 'image',
            'return_format' => 'array',
            'wrapper'       => array('width' => '50%'),
        ),
        array(
            'key'     => 'header_button_text',
            'label'   => 'Button Text',
            'name'    => 'button_text',
            'type'    => 'text',
            'wrapper' => array('width' => '100%'),
        ),
        array(
            'key'     => 'header_button_link',
            'label'   => 'Button Link',
            'name'    => 'button_link',
            'type'    => 'url',
            'wrapper' => array('width' => '100%'),
        ),
    ),
    'location' => array(
        array(
            array(
                'param'    => 'options_page',
                'operator' => '==',
                'value'    => 'settings_header',
            ),
        ),
    ),
));