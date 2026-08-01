<?php

if ( function_exists( 'acf_register_block_type' ) ) {
	acf_register_block_type( array(
		'name'            => 'repair',
		'title'           => __( 'Repair / Restoration' ),
		'render_template' => __DIR__ . '/template.php',
		'mode'            => 'edit',
		'keywords'        => array( 'repair', 'restoration', 'before', 'after' ),
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
				wp_enqueue_style( 'block-repair-style', "{$uri_base}/assets/style.css", array(), filemtime( $style ) );
			}

			$script = __DIR__ . '/assets/script.js';
			if ( file_exists( $script ) ) {
				wp_enqueue_script( 'block-repair-script', "{$uri_base}/assets/script.js", array(), filemtime( $script ), true );
			}

			// Restoration-request form → Telegram (own IDs, own config).
			$form_js = __DIR__ . '/assets/repair-form.js';
			if ( file_exists( $form_js ) ) {
				wp_enqueue_script( 'block-repair-form', "{$uri_base}/assets/repair-form.js", array(), filemtime( $form_js ), true );

				$credentials = function_exists( 'knot_telegram_credentials' ) ? knot_telegram_credentials() : array( '', '' );

				wp_localize_script( 'block-repair-form', 'knotRepairForm', array(
					'telegramBotToken' => $credentials[0] ?? '',
					'telegramChatId'   => $credentials[1] ?? '',
					'thankYouUrl'      => home_url( '/thank-you-page/' ),
					'privacyUrl'       => function_exists( 'knot_get_privacy_policy_url' ) ? knot_get_privacy_policy_url() : '/privacy-policy/',
					'minFormTime'      => 2500,
					'resendDelay'      => 10000,
				) );
			}

		},
	) );

	require_once __DIR__ . '/fields.php';

	// Print the restoration popup once, in the footer (outside <main>), when a
	// repair block on the page asked for it. Outside <main> it stays clickable
	// (main gets pointer-events:none while a popup is open) and it survives the
	// multiple the_content passes that broke the inline render.
	add_action( 'wp_footer', static function () {
		if ( ! empty( $GLOBALS['knot_repair_wants_popup'] ) ) {
			get_template_part( 'template-parts/popups/repair-popup' );
		}
	} );
}
