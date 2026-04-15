<?php

add_action('wp_ajax_send_telegram_message', 'knot_send_telegram_message');
add_action('wp_ajax_nopriv_send_telegram_message', 'knot_send_telegram_message');

function knot_send_telegram_message() {
	if (!check_ajax_referer('knot_telegram_nonce', 'nonce', false)) {
		wp_send_json_error(['message' => 'Invalid nonce'], 403);
	}

	$message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));
	if (empty($message)) {
		wp_send_json_error(['message' => 'Message is required'], 400);
	}

	$bot_token = defined('KNOT_TELEGRAM_BOT_TOKEN') ? KNOT_TELEGRAM_BOT_TOKEN : '';
	$chat_id = defined('KNOT_TELEGRAM_CHAT_ID') ? KNOT_TELEGRAM_CHAT_ID : '';
	if (empty($bot_token) || empty($chat_id)) {
		wp_send_json_error(['message' => 'Telegram credentials are not configured'], 500);
	}

	$response = wp_remote_post(
		"https://api.telegram.org/bot{$bot_token}/sendMessage",
		[
			'headers' => ['Content-Type' => 'application/json'],
			'timeout' => 15,
			'body'    => wp_json_encode([
				'chat_id' => $chat_id,
				'text'    => $message,
			]),
		]
	);

	if (is_wp_error($response)) {
		wp_send_json_error(['message' => $response->get_error_message()], 500);
	}

	$status_code = wp_remote_retrieve_response_code($response);
	if ($status_code < 200 || $status_code >= 300) {
		$body = wp_remote_retrieve_body($response);
		$error_message = 'Telegram API error';

		if (!empty($body)) {
			$decoded = json_decode($body, true);
			if (!empty($decoded['description'])) {
				$error_message = $decoded['description'];
			}
		}

		wp_send_json_error(['message' => $error_message], 500);
	}

	wp_send_json_success(['sent' => true]);
}
