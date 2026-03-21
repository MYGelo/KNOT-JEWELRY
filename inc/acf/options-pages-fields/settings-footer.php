<?php

acf_add_local_field_group(array(
    'key'      => 'group_footer',
    'title'    => 'Footer',
    'fields'   => array(
        array(
            'key'           => 'footer_logo',
            'label'         => 'Footer Logo',
            'name'          => 'footer_logo',
            'type'          => 'image',
            'return_format' => 'array',
            'wrapper'       => array('width' => '50%'),
        ),

        array(
            'key'   => 'footer_company_info_group',
            'label' => 'Company Info',
            'name'  => 'footer_company_info',
            'type'  => 'group',
            'instructions' => 'Company contact information',
            'layout' => 'block',
            'sub_fields' => array(
                array(
                    'key'           => 'footer_company_name',
                    'label'         => 'Company Name',
                    'name'          => 'company_name',
                    'type'          => 'text',
                    'default_value' => 'Company Name',
                    'wrapper'       => array('width' => '100%'),
                ),
                array(
                    'key'           => 'footer_company_email',
                    'label'         => 'Email',
                    'name'          => 'email',
                    'type'          => 'email',
                    'default_value' => 'info@gmail.com',
                    'wrapper'       => array('width' => '33%'),
                ),
                array(
                    'key'           => 'footer_company_address',
                    'label'         => 'Address',
                    'name'          => 'address',
                    'type'          => 'text',
                    'default_value' => 'Address',
                    'wrapper'       => array('width' => '33%'),
                ),
                array(
                    'key'           => 'footer_company_city_zip',
                    'label'         => 'City & ZIP',
                    'name'          => 'city_zip',
                    'type'          => 'text',
                    'default_value' => 'City & ZIP',
                    'wrapper'       => array('width' => '33%'),
                ),
            ),
        ),

        array(
            'key'   => 'footer_bottom_group',
            'label' => 'Under Footer Group',
            'type'  => 'group',
            'instructions' => 'Settings Under Footer Group',
            'layout' => 'block',
            'sub_fields' => array(

                array(
                    'key'     => 'footer_link_1',
                    'label'   => 'Footer Link 1',
                    'name'    => 'footer_link_1',
                    'type'    => 'link',
                    'post_type' => array('page'),
                    'allow_null' => 1,
                    'wrapper' => array('width' => '33%'),
                ),

                array(
                    'key'     => 'footer_link_2',
                    'label'   => 'Footer Link 2',
                    'name'    => 'footer_link_2',
                    'type'    => 'link',
                    'post_type' => array('page'),
                    'allow_null' => 1,
                    'wrapper' => array('width' => '33%'),
                ),

                array(
                    'key'     => 'footer_copyright',
                    'label'   => 'Footer Copyright (Link)',
                    'name'    => 'footer_copyright',
                    'type'    => 'link',
                    'post_type' => array('page'),
                    'allow_null' => 1,
                    'wrapper' => array('width' => '33%'),
                ),
                array(
                    'key'           => 'bg_footer_bottom_group',
                    'label'         => 'Background Image',
                    'name'          => 'bg_footer_bottom_group',
                    'type'          => 'image',
                    'return_format' => 'array', // можно поставить 'array'
                    'wrapper'       => array('width' => '100%'),
                ),
            ),
        ),

    ),
    'location' => array(
        array(
            array(
                'param'    => 'options_page',
                'operator' => '==',
                'value'    => 'settings_footer',
            ),
        ),
    ),
));