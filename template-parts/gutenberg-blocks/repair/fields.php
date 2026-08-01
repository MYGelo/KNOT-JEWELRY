<?php

acf_add_local_field_group(array(
	'key'    => 'group_repair_block',
	'title'  => 'Repair / Restoration Block',
	'fields' => array(

		// ---------- HEAD ----------
		array(
			'key'           => 'field_repair_eyebrow',
			'label'         => 'Eyebrow',
			'name'          => 'eyebrow',
			'type'          => 'text',
			'default_value' => 'Майстерня',
			'wrapper'       => array( 'width' => '50' ),
		),
		array(
			'key'           => 'field_repair_title',
			'label'         => 'Title',
			'name'          => 'title',
			'type'          => 'text',
			'default_value' => 'Друге життя виробу',
			'wrapper'       => array( 'width' => '50' ),
		),
		array(
			'key'         => 'field_repair_lead',
			'label'       => 'Lead',
			'name'        => 'lead',
			'type'        => 'textarea',
			'new_lines'   => '',
			'rows'        => 2,
		),

        array(
            'key'           => 'field_repair_before_label',
            'label'         => 'Before label',
            'name'          => 'before_label',
            'type'          => 'text',
            'default_value' => 'До',
            'wrapper'       => array( 'width' => '50' ),
        ),
        array(
            'key'           => 'field_repair_after_label',
            'label'         => 'After label',
            'name'          => 'after_label',
            'type'          => 'text',
            'default_value' => 'Після',
            'wrapper'       => array( 'width' => '50' ),
        ),

		// ---------- BEFORE / AFTER PAIRS ----------
		array(
			'key'          => 'field_repair_comparisons',
			'label'        => 'Before / After',
			'name'         => 'comparisons',
			'type'         => 'repeater',
			'layout'       => 'block',
			'collapsed'    => 'field_repair_item_title',
			'button_label' => 'Add pair',
			'min'          => 1,
			'sub_fields'   => array(

                array(
                    'key'           => 'field_repair_item_title',
                    'label'         => 'Title',
                    'name'          => 'title',
                    'type'          => 'text',
                ),

				array(
					'key'           => 'field_repair_before_image',
					'label'         => 'Before image',
					'name'          => 'before_image',
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'medium',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_repair_after_image',
					'label'         => 'After image',
					'name'          => 'after_image',
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'medium',
					'wrapper'       => array( 'width' => '50' ),
				),

                array(
                    'key'         => 'field_repair_item_description',
                    'label'       => 'Lead',
                    'name'        => 'lead',
                    'type'        => 'textarea',
                    'new_lines'   => '',
                    'rows'        => 4,
                ),
			),
		),

		// ---------- CTA (optional) ----------
		array(
			'key'           => 'field_repair_cta_text',
			'label'         => 'CTA Text',
			'name'          => 'cta_text',
			'type'          => 'text',
			'default_value' => 'Замовити реставрацію',
		),

	),

	'location' => array(
		array(
			array(
				'param'    => 'block',
				'operator' => '==',
				'value'    => 'acf/repair',
			),
		),
	),
));
