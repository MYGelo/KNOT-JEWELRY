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

/**
 * 13.0 … 24.0 in half steps, one decimal throughout — the same notation the
 * sizing table on the care page uses, and the same list the availability
 * selector in wp-admin offers, so a stored size always matches an option.
 */
function knot_get_ring_sizes(): array {
    $sizes = [];

    for ($half = 26; $half <= 48; $half++) {
        $sizes[] = number_format($half / 2, 1, '.', '');
    }

    return $sizes;
}

/**
 * Coating options, single source for the product page, the order popup and the
 * cart. The first entry is the "nothing chosen" placeholder.
 */
function knot_get_coatings(): array {
    return ['Тип покриття', 'Родій (білий)', 'Позолота'];
}

/** Notice shown once a coating is picked — the price depends on it. */
function knot_get_coating_notice(): string {
    return '⚠️ Фінальна вартість залежить від типу покриття та ваги виробу. Я з радістю розрахую точну ціну й підтверджу її під час оформлення замовлення.';
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

function knot_telegram_credentials(): array {
    $bot_token = defined('KNOT_TELEGRAM_BOT_TOKEN')
        ? KNOT_TELEGRAM_BOT_TOKEN
        : '8622055916:AAG9C41IjiLjaIqSskYbfOyi0wTrX_A90Ls';

    $chat_id = defined('KNOT_TELEGRAM_CHAT_ID')
        ? KNOT_TELEGRAM_CHAT_ID
        : '@KnotJewelryOrders';

    return [$bot_token, $chat_id];
}

function knot_order_form_config(): array {
    [$bot_token, $chat_id] = knot_telegram_credentials();

    $config = [
        'telegramBotToken' => $bot_token,
        'telegramChatId'   => $chat_id,
        'thankYouUrl'      => home_url('/thank-you-page/'),
        'needsRingSize'    => knot_product_needs_ring_size(get_the_ID()),
        'ringSizes'        => knot_get_ring_sizes(),
        'coatings'         => knot_get_coatings(),
        'minFormTime'      => 2500,
        'resendDelay'      => 10000,
    ];

    return apply_filters('knot_form_frontend_config', $config, 'order');
}

/**
 * Global cart config (not tied to a single product).
 */
function knot_cart_config(): array {
    [$bot_token, $chat_id] = knot_telegram_credentials();

    $config = [
        'telegramBotToken' => $bot_token,
        'telegramChatId'   => $chat_id,
        'thankYouUrl'      => home_url('/thank-you-page/'),
        'ringSizes'        => knot_get_ring_sizes(),
        'coatings'         => knot_get_coatings(),
        'privacyUrl'       => knot_get_privacy_policy_url(),
        'currency'         => '₴',
        'minFormTime'      => 2500,
        'resendDelay'      => 10000,
        'maxItems'         => 50,
        'maxQty'           => 99,
    ];

    return apply_filters('knot_form_frontend_config', $config, 'cart');
}

/**
 * Secondary "write in Instagram" fallback for step 1 of the order / cart /
 * repair forms. One shared source (global options: inst_fallback_link +
 * inst_fallback_text), one look across all three forms. Prints nothing when no
 * link is set. The link is an external contact channel, not a form submission.
 */
function knot_form_ig_fallback(): void {
    $link = get_field('inst_fallback_link', 'option');
    $url  = is_array($link) ? ($link['url'] ?? '') : '';
    if (empty($url)) {
        return;
    }

    $title  = (is_array($link) && !empty($link['title'])) ? $link['title'] : 'Написати в Instagram';
    $target = (is_array($link) && !empty($link['target'])) ? $link['target'] : '_blank';
    $hint   = get_field('inst_fallback_text', 'option');
    if ('' === (string) $hint) {
        $hint = 'Залишилися питання перед замовленням?';
    }
    ?>
    <div class="form-ig">
        <div class="form-ig__divider"><span>або</span></div>

        <?php if (!empty($hint)): ?>
            <p class="form-ig__hint">
                <svg class="form-ig__hint-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 16v-4M12 8h.01"/>
                </svg>
                <?= esc_html($hint); ?>
            </p>
        <?php endif; ?>

        <a class="form-ig__btn" href="<?= esc_url($url); ?>" target="<?= esc_attr($target); ?>" rel="noopener noreferrer">
            <svg class="form-ig__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <rect x="2" y="2" width="20" height="20" rx="6" fill="none" stroke="currentColor" stroke-width="2"/>
                <circle cx="12" cy="12" r="5" fill="none" stroke="currentColor" stroke-width="2"/>
                <circle cx="17.6" cy="6.4" r="1.5" fill="currentColor"/>
            </svg>
            <span><?= esc_html($title); ?></span>
        </a>
    </div>
    <?php
}
