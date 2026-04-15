<?php

if (function_exists('acf_register_block_type')) {
	acf_register_block_type(array(
		'name'            => 'video-block',
		'title'           => __('Video Block'),
		'render_template' => __DIR__ . '/template.php',
		'enqueue_style'   => get_template_directory_uri() . str_replace(get_template_directory(), '', __DIR__) . '/assets/style.css',
		'mode'            => 'edit',
		'keywords'        => array(),
		'supports'        => array(
			'anchor' => true,
		),
		'enqueue_assets'  => static function () {
			$uri_base = get_template_directory_uri() . str_replace(get_template_directory(), '', __DIR__);


			$script = __DIR__ . "/script.js";
			if (file_exists($script)) {
				wp_enqueue_script("block-video-block", "{$uri_base}/script.js", null, filemtime($script), true);
			}

			// Specific styles and scripts

		},
	));

	require_once __DIR__ . '/fields.php';
}
