<?php

if (function_exists('acf_register_block_type')) {
	acf_register_block_type(array(
		'name'            => 'viewed-posts',
		'title'           => __('Viewed Posts'),
		'render_template' => __DIR__ . '/template.php',
		'mode'            => 'edit',
		'keywords'        => array('viewed', 'recently', 'переглянуті'),
		'supports'        => array(
			'anchor' => true,
		),
		'enqueue_assets'  => static function () {
			// Reuse the in-stock card styles (cards share the same markup).
			$in_stock_style = get_template_directory() . '/template-parts/gutenberg-blocks/in-stock/assets/style.css';
			if (file_exists($in_stock_style)) {
				wp_enqueue_style(
					'block-in-stock-style',
					get_template_directory_uri() . '/template-parts/gutenberg-blocks/in-stock/assets/style.css',
					array(),
					filemtime($in_stock_style)
				);
			}

			$style = __DIR__ . '/assets/style.css';
			if (file_exists($style)) {
				$uri_base = get_template_directory_uri() . str_replace(
					str_replace('\\', '/', get_template_directory()),
					'',
					str_replace('\\', '/', __DIR__)
				);
				wp_enqueue_style('block-viewed-posts-style', "{$uri_base}/assets/style.css", array(), filemtime($style));
			}
		},
	));

	require_once __DIR__ . '/fields.php';
}
