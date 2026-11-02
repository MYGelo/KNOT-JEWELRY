<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const KNOT_LEAD_ACTION = 'knot_submit_lead';

add_action( 'wp_ajax_' . KNOT_LEAD_ACTION, 'knot_lead_intake' );
add_action( 'wp_ajax_nopriv_' . KNOT_LEAD_ACTION, 'knot_lead_intake' );

function knot_lead_fail( string $message, int $code = 400 ): void {
	wp_send_json_error( array( 'message' => $message ), $code );
}

/** Crude per-IP throttle: 1 request / 3 s and 20 / hour. Keeps junk out of the table. */
function knot_lead_throttled(): bool {
	$key = 'knot_lead_rl_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$log = get_transient( $key );
	$log = is_array( $log ) ? $log : array();
	$now = time();
	$log = array_values( array_filter( $log, static fn( $t ) => $t > $now - HOUR_IN_SECONDS ) );

	if ( count( $log ) >= 20 || ( $log && end( $log ) > $now - 3 ) ) {
		return true;
	}

	$log[] = $now;
	set_transient( $key, $log, HOUR_IN_SECONDS );

	return false;
}

function knot_lead_clean_text( $value, int $max ): string {
	$value = is_scalar( $value ) ? (string) wp_unslash( $value ) : '';

	return mb_substr( sanitize_textarea_field( $value ), 0, $max );
}

function knot_lead_clean_items( $raw ): array {
	$data = json_decode( is_scalar( $raw ) ? (string) wp_unslash( $raw ) : '', true );
	if ( ! is_array( $data ) ) {
		return array();
	}

	$items = array();
	foreach ( array_slice( $data, 0, 50 ) as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$items[] = array(
			'title'    => knot_lead_clean_text( $row['title'] ?? '', 200 ),
			'price'    => knot_lead_clean_text( $row['price'] ?? '', 40 ),
			'qty'      => max( 1, min( 99, (int) ( $row['qty'] ?? 1 ) ) ),
			'size'     => knot_lead_clean_text( $row['size'] ?? '', 10 ),
			'coating'  => knot_lead_clean_text( $row['coating'] ?? '', 60 ),
			'material' => knot_lead_clean_text( $row['material'] ?? '', 80 ),
			'stone'    => knot_lead_clean_text( $row['stone'] ?? '', 80 ),
			'type'     => knot_lead_clean_text( $row['type'] ?? '', 80 ),
			'link'     => esc_url_raw( (string) ( $row['link'] ?? '' ) ),
		);
	}

	return $items;
}

function knot_lead_intake(): void {
	if ( knot_lead_throttled() ) {
		knot_lead_fail( 'Забагато спроб. Спробуйте за хвилину.', 429 );
	}

	$type = sanitize_key( wp_unslash( $_POST['type'] ?? '' ) );
	if ( ! isset( knot_lead_types()[ $type ] ) ) {
		knot_lead_fail( 'Невідомий тип заявки.' );
	}

	$name  = knot_lead_clean_text( $_POST['name'] ?? '', 100 );
	$phone = preg_replace( '/[^\d+]/', '', knot_lead_clean_text( $_POST['phone'] ?? '', 30 ) );

	if ( '' === $name || strlen( preg_replace( '/\D/', '', $phone ) ) < 10 ) {
		knot_lead_fail( 'Вкажіть ім\'я та телефон.' );
	}

	$items = knot_lead_clean_items( $_POST['items'] ?? '' );

	$id = wp_insert_post( array(
		'post_type'   => KNOT_LEAD_CPT,
		'post_status' => 'publish',
		'post_title'  => $name . ' · ' . $phone,
	), true );

	if ( is_wp_error( $id ) || ! $id ) {
		knot_lead_fail( 'Не вдалося зберегти заявку.', 500 );
	}

	$meta = array(
		'_lead_type'      => $type,
		'_lead_status'    => 'new',
		'_lead_name'      => $name,
		'_lead_phone'     => $phone,
		'_lead_telegram'  => knot_lead_clean_text( $_POST['telegram'] ?? '', 80 ),
		'_lead_instagram' => knot_lead_clean_text( $_POST['instagram'] ?? '', 200 ),
		'_lead_comment'   => knot_lead_clean_text( $_POST['comment'] ?? '', 2000 ),
		'_lead_page'      => esc_url_raw( wp_unslash( $_POST['page'] ?? '' ) ),
		'_lead_total'     => knot_lead_clean_text( $_POST['total'] ?? '', 40 ),
		'_lead_message'   => knot_lead_clean_text( $_POST['message'] ?? '', 3500 ),
		'_lead_tg_status' => 'pending',
		'_lead_items'     => wp_json_encode( $items, JSON_UNESCAPED_UNICODE ),
	);

	foreach ( $meta as $key => $value ) {
		update_post_meta( $id, $key, wp_slash( $value ) );
	}

	// The lead is safe in the database at this point. In relay mode the server
	// sends it to Telegram (best effort, retried by cron); otherwise the browser
	// delivers it and we just mark the record so the admin knows.
	if ( knot_leads_server_relay() ) {
		knot_lead_send_telegram( $id );
	} else {
		update_post_meta( $id, '_lead_tg_status', 'client' );
	}

	wp_send_json_success( array( 'id' => $id ) );
}
