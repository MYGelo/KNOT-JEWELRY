<?php
acf_add_local_field_group(array(
	'key'      => 'example-block',
	'title'    => 'Example Block',
	'fields'   => array(
		array(
			'key'   => 'text',
			'label' => 'Some Text Field',
			'name'  => 'text',
			'type'  => 'text',
		),
	),
	'location' => array(
		array(
			array(
				'param'    => 'block',
				'operator' => '==',
				'value'    => 'acf/example-block',
			),
		),
	),
));
