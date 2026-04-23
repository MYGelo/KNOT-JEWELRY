<?php

acf_add_local_field_group(array(
    'key'   => 'group_legal_block',
    'title' => 'Legal Block',
    'fields' => array(

        array(
            'key'   => 'field_legal_sidebar_title',
            'label' => 'Sidebar Title',
            'name'  => 'sidebar_title',
            'type'  => 'text',
            'default_value' => 'Політика конфіденційності',
        ),

        array(
            'key'   => 'field_legal_sections',
            'label' => 'Sections',
            'name'  => 'sections',
            'type'  => 'repeater',
            'layout' => 'block',
            'button_label' => 'Add Section',

            'sub_fields' => array(

                array(
                    'key'   => 'field_legal_section_title',
                    'label' => 'Title',
                    'name'  => 'title',
                    'type'  => 'text',
                ),

                array(
                    'key'   => 'field_legal_section_subtitle',
                    'label' => 'Subtitle',
                    'name'  => 'subtitle',
                    'type'  => 'text',
                ),

                array(
                    'key'   => 'field_legal_section_content',
                    'label' => 'Content',
                    'name'  => 'content',
                    'type'  => 'wysiwyg',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                ),

            ),
        ),

    ),
    'location' => array(
        array(
            array(
                'param'    => 'block',
                'operator' => '==',
                'value'    => 'acf/legal-block',
            ),
        ),
    ),
));