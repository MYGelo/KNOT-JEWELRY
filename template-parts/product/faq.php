<?php
/**
 * Product FAQ accordion: delivery, coating, sizing.
 *
 * Answers the questions a visitor asks before ordering, without pushing them
 * to another page. Size and coating are still *chosen* in the cart and the
 * order form — here we only explain them, and link to the full text on the
 * care & sizing page.
 *
 * @var array $args {
 *     @type bool $needs_ring_size Whether the sizing item applies to this product.
 * }
 */

$needs_ring_size = !empty($args['needs_ring_size']);
$post_id         = (int) ($args['post_id'] ?? get_the_ID());

$faq      = get_field('product_faq', 'option') ?: [];
$terms    = get_field('single_form_steps', 'option')['step1_text'] ?? '';
$care_url = function_exists('knot_page_url_with_block') ? knot_page_url_with_block('acf/care-sizing') : '';

// ACF only returns the field defaults once the options page has been saved, so
// the copy also lives here. Anything entered in Global Settings wins.
$defaults = [
    'coating_title' => 'Тип покриття: родій або позолота',
    'coating_text'  =>
        '<p>Срібло з часом природно темніє. Родій — метал платинової групи: тонкий шар створює захисний бар’єр від поту, косметики та побутової хімії, додає дзеркального білого блиску, краще тримається проти дрібних подряпин і не викликає алергії.</p>'
        . '<p>Позолота працює так само, але дає теплий золотистий відтінок.</p>'
        . '<p>Покриття обираєте під час оформлення замовлення. Воно впливає на фінальну вартість — я розрахую точну ціну й підтверджу її перед оплатою.</p>',
    'size_title'    => 'Як визначити свій розмір',
    'size_text'     =>
        '<p>Найточніше — приміряти тоненьку класичну каблучку в ювелірній крамниці або скористатись пальцеміром.</p>'
        . '<p>Другий варіант — застосунок на телефоні (наприклад, Ring Sizer): прикладаєте рівну, недеформовану каблучку до екрана й підганяєте коло під її внутрішній діаметр.</p>'
        . '<p>Якщо нічого з цього немає — обгорніть палець ниточкою або смужкою паперу, зробіть позначку на місці стику й повідомте мені довжину. Вимірюйте наприкінці дня, коли палець найбільший, і повторіть заміри 2–3 рази.</p>',
];

$copy = static fn(string $key): string => trim((string) ($faq[$key] ?? '')) ?: $defaults[$key];

// Anchors into the care & sizing page: 0 — sizing, 1 — care, 2 — coating.
$items = [
    [
        'title' => 'Оплата, доставка та терміни виготовлення',
        'text'  => $terms,
        'more'  => '',
        'label' => '',
    ],
    [
        'title' => $copy('coating_title'),
        'text'  => $copy('coating_text'),
        'more'  => $care_url ? $care_url . '#care-section_2' : '',
        'label' => 'Докладніше про родіювання',
    ],
];

if ($needs_ring_size) {
    $items[] = [
        'title' => $copy('size_title'),
        'text'  => $copy('size_text'),
        'more'  => $care_url ? $care_url . '#care-section_0' : '',
        'label' => 'Таблиця розмірів і поради',
    ];
}

$items = array_values(array_filter(
    $items,
    static fn(array $item): bool => $item['title'] !== '' && $item['text'] !== ''
));

if (!$items) {
    return;
}
?>

<?php // Buttons and divs rather than <details>: the panel's height is driven by
      // the script (product.js), which is what lets it animate closed as well as
      // open. Same accordion mechanics as the image-list block. ?>
<div class="product-faq" data-accordion>
    <?php foreach ($items as $index => $item): ?>
        <?php $content_id = 'product-faq-' . $post_id . '-' . $index; ?>

        <div class="product-faq__item">
            <button
                    id="<?= esc_attr($content_id); ?>-trigger"
                    class="product-faq__trigger"
                    type="button"
                    aria-expanded="false"
                    aria-controls="<?= esc_attr($content_id); ?>"
            >
                <h3 class="product-faq__title"><?= esc_html($item['title']); ?></h3>

                <span class="product-faq__icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M4 6.5 8 10.5 12 6.5" stroke="currentColor" stroke-width="1.5"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </button>

            <div
                    class="product-faq__content"
                    id="<?= esc_attr($content_id); ?>"
                    role="region"
                    aria-labelledby="<?= esc_attr($content_id); ?>-trigger"
            >
                <div class="product-faq__body">
                    <?= wp_kses_post($item['text']); ?>

                    <?php if ($item['more']): ?>
                        <p class="product-faq__more">
                            <a href="<?= esc_url($item['more']); ?>"><?= esc_html($item['label']); ?></a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
