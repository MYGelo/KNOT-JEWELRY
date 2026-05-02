<?php
acf_add_local_field_group(array(
    'key'      => 'group_main_banner',
    'title'    => 'main Banner',
    'fields'   => array(

        // TEXT ALIGN HORIZONTAL (left / center / right)
        array(
            'key'   => 'field_main_banner_text_align_h',
            'label' => 'Text Align — Horizontal',
            'name'  => 'main_banner_text_align_h',
            'type'  => 'button_group',
            'choices' => array(
                'left'   => 'Left',
                'center' => 'Center',
                'right'  => 'Right',
            ),
            'default_value' => 'Left',
            'wrapper' => array('width' => '26'),
        ),

// TEXT ALIGN VERTICAL (top / center / bottom)
        array(
            'key'   => 'field_main_banner_text_align_v',
            'label' => 'Text Align — Vertical',
            'name'  => 'main_banner_text_align_v',
            'type'  => 'button_group',
            'choices' => array(
                'top'    => 'Top',
                'center' => 'Center',
                'bottom' => 'Bottom',
            ),
            'default_value' => 'center',
            'wrapper' => array('width' => '26'),
        ),
//shadow
        array(
            'key'   => 'field_main_banner_shadow',
            'label' => 'Shadow',
            'name'  => 'main_banner_shadow',
            'type'  => 'button_group',
            'choices' => array(
                'true'  => 'Yes',
                'false' => 'No',
            ),
            'default_value' => 'false',
            'wrapper' => array('width' => '14'),
        ),

//        min height
        array(
            'key'   => 'field_main_banner_min_height',
            'label' => 'Short Banner',
            'name'  => 'main_banner_min_height',
            'type'  => 'button_group',
            'choices' => array(
                'true'  => 'Yes',
                'false' => 'No',
            ),
            'default_value' => 'false',
            'wrapper' => array('width' => '16'),
        ),
        array(
            'key'   => 'field_main_banner_max_width',
            'label' => 'Max Width (px)',
            'name'  => 'main_banner_max_width',
            'type'  => 'number',
            'instructions' => 'Leave empty to use automatic width',
            'placeholder' => 'For example: 800',
            'min' => 0,
            'step' => 1,
            'wrapper' => array(
                'width' => '17',
            ),
        ),

        // SUB TITLE
        array(
            'key'   => 'field_main_banner_sub_title',
            'label' => 'Sub Title',
            'name'  => 'main_banner_sub_title',
            'type'  => 'text',
        ),

        // MAIN TITLE
        array(
            'key'   => 'field_main_banner_main_title',
            'label' => 'Main Title',
            'name'  => 'main_banner_main_title',
            'type'  => 'text',
        ),

        // DESCRIPTION
        array(
            'key'   => 'field_main_banner_description',
            'label' => 'Description',
            'name'  => 'main_banner_description',
            'type'  => 'wysiwyg',
        ),

        // LINK
        array(
            'key' => 'field_main_banner_links',
            'label' => 'Banner Links',
            'name' => 'main_banner_links',
            'type' => 'repeater',
            'layout' => 'row',
            'button_label' => 'Add Link',
            'sub_fields' => array(

                array(
                    'key' => 'field_main_banner_link_item',
                    'label' => 'Link',
                    'name' => 'link',
                    'type' => 'link',
                    'wrapper' => array(
                        'width' => '60',
                    ),
                ),

                array(
                    'key' => 'field_main_banner_link_style',
                    'label' => 'Style',
                    'name' => 'style',
                    'type' => 'button_group',
                    'choices' => array(
                        'primary' => 'Primary',
                        'outline' => 'Outline',
                    ),
                    'default_value' => 'primary',
                    'wrapper' => array(
                        'width' => '40',
                    ),
                ),

            ),
        ),

        // BACKGROUND IMAGE
        array(
            'key'   => 'field_main_banner_bg',
            'label' => 'Background Image',
            'name'  => 'main_banner_bg',
            'type'  => 'image',
            'return_format' => 'array',
            'preview_size'  => 'medium_large',
            'wrapper' => array(
                'width' => '50',
            ),
        ),

        // BACKGROUND IMAGE MOBILE
        array(
            'key'   => 'field_main_banner_bg_mob',
            'label' => 'Background Image MOBILE',
            'name'  => 'main_banner_bg_mob',
            'type'  => 'image',
            'return_format' => 'array',
            'preview_size'  => 'medium_large',
            'wrapper' => array(
                'width' => '50',
            ),
        ),

        // BACKGROUND VIDEO
        array(
            'key'   => 'field_main_banner_video',
            'label' => 'Background Video (MP4)',
            'name'  => 'main_banner_video',
            'type'  => 'file',
            'return_format' => 'url',
            'mime_types' => 'mp4,webm',
            'instructions' => 'Optional. Video will be shown above background image.',
            'wrapper' => array(
                'width' => '50',
            ),
        ),

        // BACKGROUND IMAGE PNG
        array(
            'key'   => 'field_main_banner_bg_png',
            'label' => 'Background Image PNG (optional)',
            'name'  => 'main_banner_bg_png',
            'type'  => 'image',
            'return_format' => 'array',
            'preview_size'  => 'medium_large',
            'wrapper' => array(
                'width' => '50',
            ),
        ),

        array(
            'key' => 'field_main_banner_features',
            'label' => 'Banner Features',
            'name' => 'main_banner_features',
            'type' => 'repeater',
            'layout' => 'row',
            'button_label' => 'Add Feature',
            'sub_fields' => array(

                array(
                    'key' => 'field_main_banner_feature_icon',
                    'label' => 'SVG Icon',
                    'name' => 'icon',
                    'type' => 'image',
                    'return_format' => 'array',
                    'wrapper' => array(
                        'width' => '50',
                    ),
                ),

                array(
                    'key' => 'field_main_banner_feature_text',
                    'label' => 'Text',
                    'name' => 'text',
                    'type' => 'text',
                    'wrapper' => array(
                        'width' => '50',
                    ),
                ),

            ),
        ),

    ),

    // BIND TO BLOCK
    'location' => array(
        array(
            array(
                'param'    => 'block',
                'operator' => '==',
                'value'    => 'acf/main-banner',
            ),
        ),
    ),
));
