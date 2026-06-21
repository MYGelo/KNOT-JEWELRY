<?php

acf_add_local_field_group(array(
	'key'   => 'group_care_sizing_block',
	'title' => 'Care & Sizing Block',
	'fields' => array(

		// ---------- HERO ----------
		array(
			'key'           => 'field_care_hero_title',
			'label'         => 'Hero Title',
			'name'          => 'hero_title',
			'type'          => 'text',
			'default_value' => 'Догляд та розміри',
		),
		array(
			'key'           => 'field_care_hero_subtitle',
			'label'         => 'Hero Subtitle',
			'name'          => 'hero_subtitle',
			'type'          => 'textarea',
			'new_lines'     => 'br',
			'default_value' => 'Гід із вибору правильного розміру та догляду за вашими срібними прикрасами.',
		),
        array(
            'key'           => 'field_care_section_margin',
            'label'         => 'Show Margin',
            'name'          => 'show_margin',
            'type'          => 'true_false',
            'ui'            => 1,
            'default_value' => 1,
            'wrapper'       => array('width' => '50'),
        ),

		// ---------- SECTIONS ----------
		array(
			'key'          => 'field_care_sections',
			'label'        => 'Sections',
			'name'         => 'sections',
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Add Section',

			'sub_fields' => array(
                array(
                    'key'     => 'field_care_section_image_position',
                    'label'   => 'Image Position',
                    'name'    => 'image_position',
                    'type'    => 'button_group',
                    'choices' => array(
                        'left'  => 'Left',
                        'right' => 'Right',
                    ),
                    'default_value' => 'left',
                ),

				array(
					'key'           => 'field_care_section_number',
					'label'         => 'Number',
					'name'          => 'number',
					'type'          => 'text',
					'wrapper'       => array('width' => '50'),
				),

				array(
					'key'           => 'field_care_section_title',
					'label'         => 'Title',
					'name'          => 'title',
					'type'          => 'text',
					'default_value' => 'Розмірна сітка кілець',
					'wrapper'       => array('width' => '50'),
				),
				array(
					'key'          => 'field_care_section_image',
					'label'        => 'Image',
					'name'         => 'image',
					'type'         => 'image',
					'return_format' => 'array',
					'preview_size' => 'medium',
				),

				// Intro (subheading + paragraph). Use H4 for the small uppercase label.
				array(
					'key'          => 'field_care_section_intro',
					'label'        => 'Intro (subheading + text)',
					'name'         => 'intro',
					'type'         => 'wysiwyg',
					'toolbar'      => 'full',
					'media_upload' => 0,
					'instructions' => 'Use a heading (H4) for the small uppercase label, then a paragraph.',
					'default_value' => '<h4>Як виміряти</h4><p>Виміряйте внутрішній діаметр кільця, яке вам добре підходить, і порівняйте його з розмірною таблицею. Якщо ви між розмірами, ми рекомендуємо обрати більший для комфортнішої посадки.</p>',
				),

				// Optional size table
				array(
					'key'           => 'field_care_section_table_enabled',
					'label'         => 'Show Table',
					'name'          => 'table_enabled',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 0,
				),
				array(
					'key'               => 'field_care_section_table_head_left',
					'label'             => 'Table — Left Header',
					'name'              => 'table_head_left',
					'type'              => 'text',
					'default_value'     => 'Внутрішній діаметр (мм)',
					'wrapper'           => array('width' => '50'),
					'conditional_logic' => array(
						array(
							array('field' => 'field_care_section_table_enabled', 'operator' => '==', 'value' => '1'),
						),
					),
				),
				array(
					'key'               => 'field_care_section_table_head_right',
					'label'             => 'Table — Right Header',
					'name'              => 'table_head_right',
					'type'              => 'text',
					'default_value'     => 'Розмір кільця',
					'wrapper'           => array('width' => '50'),
					'conditional_logic' => array(
						array(
							array('field' => 'field_care_section_table_enabled', 'operator' => '==', 'value' => '1'),
						),
					),
				),
				array(
					'key'               => 'field_care_section_table_rows',
					'label'             => 'Table Rows',
					'name'              => 'table_rows',
					'type'              => 'repeater',
					'layout'            => 'table',
					'button_label'      => 'Add Row',
					'conditional_logic' => array(
						array(
							array('field' => 'field_care_section_table_enabled', 'operator' => '==', 'value' => '1'),
						),
					),
					'sub_fields' => array(
						array(
							'key'   => 'field_care_table_col_left',
							'label' => 'Left',
							'name'  => 'col_left',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_care_table_col_right',
							'label' => 'Right',
							'name'  => 'col_right',
							'type'  => 'text',
						),
					),
				),

				// Extra content (tips / recommendations / cleaning, lists etc.)
				array(
					'key'          => 'field_care_section_extra',
					'label'        => 'Extra content (tips / lists)',
					'name'         => 'extra',
					'type'         => 'wysiwyg',
					'toolbar'      => 'full',
					'media_upload' => 0,
					'instructions' => 'Use headings (H4) for labels and bullet lists for tips / recommendations.',
				),
			),
		),
	),

	'location' => array(
		array(
			array(
				'param'    => 'block',
				'operator' => '==',
				'value'    => 'acf/care-sizing',
			),
		),
	),
));
