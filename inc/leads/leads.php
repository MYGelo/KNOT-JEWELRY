<?php
/**
 * Leads module — stores every order / request submitted through the site forms
 * in WordPress (custom post type "knot_lead") and relays it to Telegram from
 * the server.
 *
 * Self-contained: everything lives in inc/leads/. To switch it off, either
 *   - remove the single require_once of this file from functions.php, or
 *   - define( 'KNOT_LEADS_ENABLED', false ); in wp-config.php.
 * With the module off the forms fall back to their old behaviour (browser →
 * Telegram directly), so nothing else needs touching.
 *
 * To move it to another theme / a plugin: copy inc/leads/ and require this file.
 *
 * Optional wp-config.php constants:
 *   KNOT_LEADS_ENABLED         bool  — false disables the module
 *   KNOT_LEADS_RETENTION_DAYS  int   — auto-delete leads older than N days
 *   KNOT_TELEGRAM_BOT_TOKEN    string, KNOT_TELEGRAM_CHAT_ID string
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'KNOT_LEADS_ENABLED' ) && ! KNOT_LEADS_ENABLED ) {
	return;
}

define( 'KNOT_LEADS_ACTIVE', true );
define( 'KNOT_LEADS_DIR', __DIR__ );

require_once __DIR__ . '/post-type.php';
require_once __DIR__ . '/telegram.php';
require_once __DIR__ . '/intake.php';
require_once __DIR__ . '/frontend.php';

if ( is_admin() ) {
	require_once __DIR__ . '/admin.php';
	require_once __DIR__ . '/export.php';
}
