<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const KNOT_LEAD_TG_MAX_TRIES = 5;

/**
 * Send a lead to Telegram (server side) and record the outcome on the lead.
 * Safe to call repeatedly: used for the first attempt, cron retries and the
 * manual "send again" button.
 */
function knot_lead_send_telegram( int $id ): bool {
	if ( KNOT_LEAD_CPT !== get_post_type( $id ) ) {
		return false;
	}

	$tries = (int) get_post_meta( $id, '_lead_tg_tries', true ) + 1;
	update_post_meta( $id, '_lead_tg_tries', $tries );

	if ( ! function_exists( 'knot_telegram_credentials' ) ) {
		return knot_lead_tg_failed( $id, $tries, 'Не знайдено налаштувань Telegram' );
	}

	[ $token, $chat_id ] = knot_telegram_credentials();
	if ( ! $token || ! $chat_id ) {
		return knot_lead_tg_failed( $id, $tries, 'Не задано токен або chat id' );
	}

	$text = knot_lead_telegram_text( $id );

	$response = wp_remote_post( 'https://api.telegram.org/bot' . $token . '/sendMessage', array(
		'timeout' => 8,
		'headers' => array( 'Content-Type' => 'application/json' ),
		'body'    => wp_json_encode( array( 'chat_id' => $chat_id, 'text' => $text ) ),
	) );

	if ( is_wp_error( $response ) ) {
		return knot_lead_tg_failed( $id, $tries, $response->get_error_message() );
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $body['ok'] ) ) {
		$why = is_array( $body ) && ! empty( $body['description'] )
			? (string) $body['description']
			: 'HTTP ' . wp_remote_retrieve_response_code( $response );

		return knot_lead_tg_failed( $id, $tries, $why );
	}

	update_post_meta( $id, '_lead_tg_status', 'sent' );
	delete_post_meta( $id, '_lead_tg_error' );
	wp_clear_scheduled_hook( 'knot_lead_tg_retry', array( $id ) );

	return true;
}

function knot_lead_tg_failed( int $id, int $tries, string $reason ): bool {
	update_post_meta( $id, '_lead_tg_status', 'error' );
	update_post_meta( $id, '_lead_tg_error', mb_substr( $reason, 0, 300 ) );

	// Back off: 5, 10, 15, 20 minutes, then leave it for the manual button.
	if ( $tries < KNOT_LEAD_TG_MAX_TRIES && ! wp_next_scheduled( 'knot_lead_tg_retry', array( $id ) ) ) {
		wp_schedule_single_event( time() + 5 * MINUTE_IN_SECONDS * $tries, 'knot_lead_tg_retry', array( $id ) );
	}

	return false;
}

add_action( 'knot_lead_tg_retry', static function ( $id ) {
	$id = (int) $id;
	if ( 'sent' !== get_post_meta( $id, '_lead_tg_status', true ) ) {
		knot_lead_send_telegram( $id );
	}
} );

/** The text exactly as the form built it, plus a pointer to the admin record. */
function knot_lead_telegram_text( int $id ): string {
	$lead = knot_lead_get( $id );
	$text = $lead['message'];

	if ( '' === trim( $text ) ) {
		$text = implode( "\n", array_filter( array(
			'Нова заявка з сайту KNŌT JEWELRY:',
			'',
			'👤 Ім\'я: ' . $lead['name'],
			'📞 Телефон: ' . $lead['phone'],
			'💬 Telegram: ' . ( $lead['telegram'] ?: '-' ),
			'📷 Instagram: ' . ( $lead['instagram'] ?: '-' ),
			'📝 ' . ( $lead['comment'] ?: '-' ),
			knot_lead_items_summary( $lead ),
			$lead['page'],
		), static fn( $v ) => null !== $v ) );
	}

	$text .= "\n\n🗂 Заявка #" . $id;

	// Telegram caps a message at 4096 characters.
	return mb_substr( $text, 0, 4000 );
}
