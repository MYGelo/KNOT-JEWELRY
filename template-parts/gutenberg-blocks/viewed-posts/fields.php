<?php

acf_add_local_field_group(array(
	'key'    => 'group_viewed_posts',
	'title'  => 'Viewed Posts',
	'fields' => array(
		array(
			'key'           => 'field_viewed_title',
			'label'         => 'Title',
			'name'          => 'viewed_title',
			'type'          => 'text',
			'default_value' => 'Ви переглядали',
		),
		array(
			'key'           => 'field_viewed_tap_text',
			'label'         => 'Tap Text',
			'name'          => 'viewed_tap_text',
			'type'          => 'text',
			'default_value' => '',
		),
		array(
			'key'           => 'field_viewed_exclude_current',
			'label'         => 'Exclude current product',
			'name'          => 'viewed_exclude_current',
			'type'          => 'true_false',
			'ui'            => 1,
			'default_value' => 1,
			'instructions'  => 'Hide the item that is currently open from its own “viewed” row.',
		),
	),
	'location' => array(
		array(
			array(
				'param'    => 'block',
				'operator' => '==',
				'value'    => 'acf/viewed-posts',
			),
		),
	),
));
