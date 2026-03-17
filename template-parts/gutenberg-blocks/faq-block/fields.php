<?php
acf_add_local_field_group(array(
	'key'      => 'faq-block',
	'title'    => 'FAQ',
	'fields'   => array(
        array(
            'key' => 'field_faq_title_key',
            'label' => __('FAQ Title', 'dnt'),
            'name' => 'faq_title',
            'type' => 'text',
        ),
        array(
            'key' => 'field_faq_items_key',
            'label' => __('FAQ Items', 'dnt'),
            'name' => 'faq_items',
            'type' => 'repeater',
            'layout' => 'block',
            'sub_fields' => array(
                array(
                    'key' => 'field_faq_question_key',
                    'label' => __('Question', 'dnt'),
                    'name' => 'title',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_faq_answer_key',
                    'label' => __('Answer', 'dnt'),
                    'name' => 'description',
                    'type' => 'wysiwyg',
                ),
            ),
        ),
	),
	'location' => array(
		array(
			array(
				'param'    => 'block',
				'operator' => '==',
				'value'    => 'acf/faq-block',
			),
		),
	),
));
