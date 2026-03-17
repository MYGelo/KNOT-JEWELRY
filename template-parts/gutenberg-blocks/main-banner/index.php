<?php

if ( function_exists( 'acf_register_block_type' ) ) {
	acf_register_block_type( array(
		'name'            => 'main-banner',
		'title'           => __( 'main Banner' ),
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
				wp_enqueue_style( 'block-main-banner-style', "{$uri_base}/assets/style.css", array(), filemtime( $style ) );
			}

			$script = __DIR__ . '/assets/script.js';
			if ( file_exists( $script ) ) {
				wp_enqueue_script( 'block-main-banner-script', "{$uri_base}/assets/script.js", array(), filemtime( $script ), true );
			}

		},
	) );

	require_once __DIR__ . '/fields.php';
}
