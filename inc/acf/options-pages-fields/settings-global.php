<?php

acf_add_local_field_group(array(
    'key'      => 'group_site_settings',
    'title'    => 'Site Settings',
    'fields'   => array(
        array(
            'key'   => 'tab_general_settings',
            'label' => 'General Settings',
            'name'  => '',
            'type'  => 'tab',
            'placement' => 'top',
        ),
        array(
            'key'           => 'field_maintenance_mode',
            'label'         => 'Enable Maintenance Mode',
            'name'          => 'maintenance_mode',
            'type'          => 'true_false',
            'default_value' => 0,
            'ui'            => 1,
            'wrapper'       => array(
                'width' => '50%',
            ),
        ),
        array(
            'key'           => 'field_disable_payments',
            'label'         => 'Disable Payments',
            'name'          => 'disable_payments',
            'type'          => 'true_false',
            'default_value' => 0,
            'ui'            => 1,
            'wrapper'       => array(
                'width' => '50%',
            ),
        ),
        array(
            'key'     => 'field_post_form_title',
            'label'   => 'Contact Form 7',
            'name'    => 'post_form_shortcode',
            'type'          => 'post_object',
            'post_type'     => array('wpcf7_contact_form'),
            'return_format' => 'id',
            'ui'            => 1,
        ),

//        404
        array(
            'key'   => 'tab_404_page',
            'label' => '404 Page Settings',
            'name'  => '',
            'type'  => 'tab',
            'placement' => 'top',
        ),
        array(
            'key'    => 'field_404_wrapper',
            'label'  => '404 Page Settings',
            'name'   => 'page_404',
            'type'   => 'group',
            'layout' => 'block',
            'sub_fields' => array(

                array(
                    'key'     => 'field_404_main_title',
                    'label'   => 'Main Title',
                    'name'    => 'main_title',
                    'type'    => 'text',
                    'wrapper' => array('width' => '100%'),
                ),

                array(
                    'key'     => 'field_404_title',
                    'label'   => 'Title',
                    'name'    => 'title',
                    'type'    => 'text',
                    'wrapper' => array('width' => '100%'),
                ),

                array(
                    'key'     => 'field_404_description',
                    'label'   => 'Description',
                    'name'    => 'description',
                    'type'    => 'textarea',
                    'wrapper' => array('width' => '100%'),
                ),

                array(
                    'key'     => 'field_404_link_text',
                    'label'   => 'Link Text',
                    'name'    => 'link_text',
                    'type'    => 'text',
                    'wrapper' => array('width' => '100%'),
                ),

                array(
                    'key'           => 'field_404_bg_image',
                    'label'         => 'Background Image',
                    'name'          => 'background_image',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'library'       => 'all',
                    'wrapper'       => array('width' => '50%'),
                ),

                array(
                    'key'           => 'field_404_bg_image_mob',
                    'label'         => 'Background Image Mobile',
                    'name'          => 'background_image_mob',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'library'       => 'all',
                    'wrapper'       => array('width' => '50%'),
                ),
            ),
        ),

//        THANK YOU PAGE
        array(
            'key'   => 'tab_thank_you_page',
            'label' => 'Thank You Page Settings',
            'name'  => '',
            'type'  => 'tab',
            'placement' => 'top',
        ),

        array(
            'key'    => 'field_thank_you_wrapper',
            'label'  => 'Thank You Page Settings',
            'name'   => 'thank_you_page',
            'type'   => 'group',
            'layout' => 'block',
            'sub_fields' => array(
                array(
                    'key'     => 'field_thank_you_main_title',
                    'label'   => 'Main Title',
                    'name'    => 'main_title',
                    'type'    => 'text',
                    'wrapper' => array('width' => '100%'),
                ),
                array(
                    'key'     => 'field_thank_you_title',
                    'label'   => 'Title',
                    'name'    => 'title',
                    'type'    => 'text',
                    'wrapper' => array('width' => '100%'),
                ),
                array(
                    'key'     => 'field_thank_you_description',
                    'label'   => 'Description',
                    'name'    => 'description',
                    'type'    => 'textarea',
                    'wrapper' => array('width' => '100%'),
                ),
                array(
                    'key'     => 'field_thank_you_link_text',
                    'label'   => 'Link Text',
                    'name'    => 'link_text',
                    'type'    => 'text',
                    'wrapper' => array('width' => '100%'),
                ),
                array(
                    'key'   => 'field_thank_you_link_url',
                    'label' => 'Link URL',
                    'name'  => 'link_url',
                    'type'  => 'url',
                    'wrapper' => array('width' => '100%'),
                ),
                array(
                    'key'           => 'field_thank_you_bg_image',
                    'label'         => 'Background Image',
                    'name'          => 'background_image',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'library'       => 'all',
                    'wrapper'       => array('width' => '50%'),
                ),
                array(
                    'key'           => 'field_thank_you_bg_image_mob',
                    'label'         => 'Background Image Mobile',
                    'name'          => 'background_image_mob',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'library'       => 'all',
                    'wrapper'       => array('width' => '50%'),
                ),
            ),
        ),
    ),
    'location' => array(
        array(
            array(
                'param'    => 'options_page',
                'operator' => '==',
                'value'    => 'global_settings',
            ),
        ),
    ),
));
