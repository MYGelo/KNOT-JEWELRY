<?php
/**
 * Holidays module — seasonal site decoration (Halloween, 8 March, New Year …).
 *
 * This file wires the module up. The actual visual styles (CSS/JS, particles)
 * are added later under inc/holidays/assets/<slug>.css|js and are loaded only
 * during the holiday's active week. Until those files exist the module changes
 * nothing visible — it only decides which holiday is active and adds a body
 * class, so logic and admin can ship before the design is chosen.
 *
 * Self-contained: everything lives in inc/holidays/. To switch it off, remove
 * the require_once from functions.php, or define in wp-config.php:
 *   define( 'KNOT_HOLIDAYS_ENABLED', false );
 *
 * To move it elsewhere: copy inc/holidays/ and require this file.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'KNOT_HOLIDAYS_ENABLED' ) && ! KNOT_HOLIDAYS_ENABLED ) {
	return;
}

define( 'KNOT_HOLIDAYS_ACTIVE', true );
define( 'KNOT_HOLIDAYS_DIR', __DIR__ );
define( 'KNOT_HOLIDAYS_OPTION', 'knot_holidays' );

require_once __DIR__ . '/core.php';

if ( is_admin() ) {
	require_once __DIR__ . '/admin.php';
}
