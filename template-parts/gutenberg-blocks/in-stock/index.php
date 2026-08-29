<?php

if ( function_exists( 'acf_register_block_type' ) ) {
	acf_register_block_type( array(
		'name'            => 'in-stock',
		'title'           => __( 'In Stock' ),
		'render_template' => __DIR__ . '/template.php',
		'mode'            => 'edit',
		'keywords'        => array(),
		'supports'        => array(
			'anchor' => true,
		),
		'enqueue_assets'  => static function () {
			// Fix for Windows
			$theme_dir   = str_replace( '\\', '/', get_template_directory() );
			$current_dir = str_replace( '\\', '/', __DIR__ );

			$uri_base = get_template_directory_uri() . str_replace( $theme_dir, '', $current_dir );

			$style = __DIR__ . '/assets/style.css';
			if ( file_exists( $style ) ) {
				wp_enqueue_style( 'block-in-stock-style', "{$uri_base}/assets/style.css", array(), filemtime( $style ) );
			}

			// No script of its own any more: the slider, autoplay and card flip
			// come from assets/js/viewed.js, which drives every strip of these
			// cards. assets/script.js can be deleted.

		},
	) );

	require_once __DIR__ . '/fields.php';
}
