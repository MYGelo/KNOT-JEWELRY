<?php

function knot_product_needs_ring_size(int $post_id): bool {
    $terms = get_the_terms($post_id, 'product_type');

    if (!$terms || is_wp_error($terms)) {
        return false;
    }

    $slugs = ['kabluchky', 'nabir', 'nabor', 'kabluchki'];

    foreach ($terms as $term) {
        if (in_array($term->slug, $slugs, true)) {
            return true;
        }

        $name = mb_strtolower($term->name);

        if (
            str_contains($name, 'каблуч')
            || str_contains($name, 'набір')
            || str_contains($name, 'nabir')
        ) {
            return true;
        }
    }

    return false;
}

function knot_get_ring_sizes(): array {
    $sizes = [];

    for ($size = 15; $size <= 24; $size++) {
        $sizes[] = (string) $size;

        if ($size < 24) {
            $sizes[] = $size . '.5';
        }
    }

    return $sizes;
}

function knot_get_privacy_policy_url(): string {
    $pages = get_pages([
        'meta_key'   => '_wp_page_template',
        'meta_value' => 'templates/template-privacy-policy.php',
        'number'     => 1,
    ]);

    if (!empty($pages)) {
        return get_permalink($pages[0]);
    }

    return home_url('/privacy-policy/');
}

function knot_order_form_config(): array {
    $bot_token = defined('KNOT_TELEGRAM_BOT_TOKEN')
        ? KNOT_TELEGRAM_BOT_TOKEN
        : '8622055916:AAG9C41IjiLjaIqSskYbfOyi0wTrX_A90Ls';

    $chat_id = defined('KNOT_TELEGRAM_CHAT_ID')
        ? KNOT_TELEGRAM_CHAT_ID
        : '@KnotJewelryOrders';

    return [
        'telegramBotToken' => $bot_token,
        'telegramChatId'   => $chat_id,
        'thankYouUrl'      => home_url('/thank-you-page/'),
        'needsRingSize'    => knot_product_needs_ring_size(get_the_ID()),
        'ringSizes'        => knot_get_ring_sizes(),
        'minFormTime'      => 2500,
        'resendDelay'      => 10000,
    ];
}
