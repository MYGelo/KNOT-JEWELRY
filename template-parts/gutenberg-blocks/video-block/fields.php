<?php

acf_add_local_field_group(array(
	'key'      => 'video-block',
	'title'    => 'Video Block',
	'fields'   => array(
		array(
			'key'           => 'video',
			'label'         => 'Video',
			'type'          => 'file',
			'return_format' => 'array',
			'mime_types'    => 'mp4,webm,ogg',
			'wrapper'       => ['width' => '50%'],
		),
		array(
			'key'           => 'video_poster',
			'label'         => 'Video Poster',
			'type'          => 'image',
			'return_format' => 'array',
			'wrapper'       => ['width' => '50%'],
		),
	),
	'location' => array(
		array(
			array(
				'param'    => 'block',
				'operator' => '==',
				'value'    => 'acf/video-block',
			),
		),
	),
));
