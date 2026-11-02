<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const KNOT_LEAD_CPT = 'knot_lead';

function knot_lead_statuses(): array {
	return array(
		'new'    => 'Нова',
		'work'   => 'В роботі',
		'done'   => 'Виконана',
		'cancel' => 'Скасована',
	);
}

function knot_lead_types(): array {
	return array(
		'product' => 'Товар',
		'cart'    => 'Кошик',
		'repair'  => 'Реставрація',
	);
}

add_action( 'init', static function () {
	$admin = 'manage_options';

	register_post_type( KNOT_LEAD_CPT, array(
		'labels'              => array(
			'name'               => 'Заявки',
			'singular_name'      => 'Заявка',
			'menu_name'          => 'Заявки',
			'all_items'          => 'Усі заявки',
			'edit_item'          => 'Заявка',
			'search_items'       => 'Шукати заявки',
			'not_found'          => 'Заявок поки немає',
			'not_found_in_trash' => 'У кошику порожньо',
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
		'menu_position'       => 25,
		'menu_icon'           => 'dashicons-email-alt',
		'supports'            => false,
		'map_meta_cap'        => true,
		'capability_type'     => 'post',
		'capabilities'        => array(
			'edit_posts'             => $admin,
			'edit_others_posts'      => $admin,
			'edit_published_posts'   => $admin,
			'edit_private_posts'     => $admin,
			'publish_posts'          => $admin,
			'read_private_posts'     => $admin,
			'delete_posts'           => $admin,
			'delete_others_posts'    => $admin,
			'delete_published_posts' => $admin,
			'delete_private_posts'   => $admin,
			'create_posts'           => 'do_not_allow',
		),
	) );
} );

/**
 * Lead as a plain array — the one place that knows the meta layout.
 */
function knot_lead_get( int $id ): array {
	$items = json_decode( (string) get_post_meta( $id, '_lead_items', true ), true );

	return array(
		'id'         => $id,
		'date'       => get_post_time( 'd.m.Y H:i', false, $id ),
		'type'       => (string) get_post_meta( $id, '_lead_type', true ),
		'status'     => (string) get_post_meta( $id, '_lead_status', true ) ?: 'new',
		'name'       => (string) get_post_meta( $id, '_lead_name', true ),
		'phone'      => (string) get_post_meta( $id, '_lead_phone', true ),
		'telegram'   => (string) get_post_meta( $id, '_lead_telegram', true ),
		'instagram'  => (string) get_post_meta( $id, '_lead_instagram', true ),
		'comment'    => (string) get_post_meta( $id, '_lead_comment', true ),
		'page'       => (string) get_post_meta( $id, '_lead_page', true ),
		'total'      => (string) get_post_meta( $id, '_lead_total', true ),
		'items'      => is_array( $items ) ? $items : array(),
		'message'    => (string) get_post_meta( $id, '_lead_message', true ),
		'note'       => (string) get_post_meta( $id, '_lead_note', true ),
		'tg_status'  => (string) get_post_meta( $id, '_lead_tg_status', true ) ?: 'pending',
		'tg_error'   => (string) get_post_meta( $id, '_lead_tg_error', true ),
		'tg_tries'   => (int) get_post_meta( $id, '_lead_tg_tries', true ),
	);
}

/** Short human summary of what was ordered, for list / CSV. */
function knot_lead_items_summary( array $lead ): string {
	if ( 'repair' === $lead['type'] ) {
		return 'Реставрація';
	}

	$parts = array();
	foreach ( $lead['items'] as $item ) {
		$line = (string) ( $item['title'] ?? '' );
		if ( ! empty( $item['size'] ) ) {
			$line .= ', р. ' . $item['size'];
		}
		if ( ! empty( $item['coating'] ) && 'Тип покриття' !== $item['coating'] ) {
			$line .= ', ' . $item['coating'];
		}
		if ( ( $item['qty'] ?? 1 ) > 1 ) {
			$line .= ' ×' . (int) $item['qty'];
		}
		$parts[] = $line;
	}

	return implode( '; ', array_filter( $parts ) );
}

/** Optional auto-purge (personal data): define KNOT_LEADS_RETENTION_DAYS. */
add_action( 'init', static function () {
	if ( ! defined( 'KNOT_LEADS_RETENTION_DAYS' ) || (int) KNOT_LEADS_RETENTION_DAYS < 30 ) {
		return;
	}
	if ( ! wp_next_scheduled( 'knot_leads_purge' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'knot_leads_purge' );
	}
} );

add_action( 'knot_leads_purge', static function () {
	if ( ! defined( 'KNOT_LEADS_RETENTION_DAYS' ) ) {
		return;
	}
	$ids = get_posts( array(
		'post_type'      => KNOT_LEAD_CPT,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'posts_per_page' => 200,
		'date_query'     => array( array( 'before' => (int) KNOT_LEADS_RETENTION_DAYS . ' days ago' ) ),
	) );
	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}
} );
