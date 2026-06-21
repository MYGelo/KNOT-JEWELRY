<?php

if (function_exists('acf_register_block_type')) {
	acf_register_block_type(array(
		'name'            => 'care-sizing',
		'title'           => __('Care & Sizing'),
		'render_template' => __DIR__ . '/template.php',
		'mode'            => 'edit',
		'keywords'        => array('care', 'sizing', 'догляд', 'розмір'),
		'supports'        => array(
			'anchor' => true,
		),
		'enqueue_assets'  => static function () {
			// Fix for Windows
			$theme_dir = str_replace('\\', '/', get_template_directory());
			$current_dir = str_replace('\\', '/', __DIR__);

			$uri_base = get_template_directory_uri() . str_replace($theme_dir, '', $current_dir);

			$style = __DIR__ . "/assets/style.css";
			if (file_exists($style)) {
				wp_enqueue_style('care-sizing-style', "{$uri_base}/assets/style.css", array(), filemtime($style));
			}

			$script = __DIR__ . "/assets/script.js";
			if (file_exists($script)) {
				wp_enqueue_script("care-sizing-script", "{$uri_base}/assets/script.js", null, filemtime($script), true);
			}

			// Specific styles and scripts

		},
	));

	require_once __DIR__ . '/fields.php';
}
