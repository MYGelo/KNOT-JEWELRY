<?php

acf_add_local_field_group(array(
    'key'      => 'group_add_comments',
    'title'    => 'Add Comments',
    'fields'   => array(

        array(
            'key'   => 'field_add_comments_main_title',
            'label' => 'Main Title',
            'name'  => 'add_comments_main_title',
            'type'  => 'text',
        ),

        array(
            'key'   => 'field_add_comments_description',
            'label' => 'Description',
            'name'  => 'add_comments_description',
            'type'  => 'textarea',
        ),

    ),

    'location' => array(
        array(
            array(
                'param'    => 'block',
                'operator' => '==',
                'value'    => 'acf/add-comments',
            ),
        ),
    ),
));