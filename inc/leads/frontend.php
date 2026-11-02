<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Switches the three site forms to server-side submission.
 *
 * The theme builds each form's JS config in knot_order_form_config() /
 * knot_cart_config() / the repair block and runs it through the
 * 'knot_form_frontend_config' filter. With this module on we hand the form the
 * endpoint and take the Telegram token out of the page. With the module off the
 * filter is never hooked, the token stays, and the forms talk to Telegram
 * directly as before.
 *
 * @param array  $config Config about to be passed to wp_localize_script().
 * @param string $form   'order' | 'cart' | 'repair'.
 */
add_filter( 'knot_form_frontend_config', static function ( array $config, string $form ): array {
	unset( $config['telegramBotToken'], $config['telegramChatId'] );

	$config['leadsEndpoint'] = admin_url( 'admin-ajax.php' );
	$config['leadsAction']   = KNOT_LEAD_ACTION;

	return $config;
}, 10, 2 );
