<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Switches the three site forms to store submissions in WordPress.
 *
 * Two delivery modes, chosen by the KNOT_LEADS_SERVER_RELAY constant
 * (wp-config.php). Default is false — browser delivery — because it works on
 * every host, including shared hosting that blocks outbound requests
 * (InfinityFree): the server only stores the lead, the browser sends it to
 * Telegram as before, so the token stays in the page.
 *
 * Set KNOT_LEADS_SERVER_RELAY to true on a host whose PHP can reach
 * api.telegram.org: then the server relays to Telegram and the token is kept
 * out of the page.
 *
 * With the whole module off this filter is never hooked, so the forms fall back
 * to their original browser-to-Telegram behaviour untouched.
 *
 * @param array  $config Config about to be passed to the form JS.
 * @param string $form   'order' | 'cart' | 'repair'.
 */
add_filter( 'knot_form_frontend_config', static function ( array $config, string $form ): array {
	$config['leadsEndpoint'] = admin_url( 'admin-ajax.php' );
	$config['leadsAction']   = KNOT_LEAD_ACTION;

	$relay = knot_leads_server_relay();
	$config['leadsRelay'] = $relay;

	if ( $relay ) {
		// Server relays to Telegram — no need to expose the token.
		unset( $config['telegramBotToken'], $config['telegramChatId'] );
	}
	// Otherwise the browser sends to Telegram directly, so the token must stay.

	return $config;
}, 10, 2 );

/** Whether PHP should relay leads to Telegram itself. Default: no (browser does). */
function knot_leads_server_relay(): bool {
	return defined( 'KNOT_LEADS_SERVER_RELAY' ) ? (bool) KNOT_LEADS_SERVER_RELAY : false;
}
