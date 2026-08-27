<?php
    extract($args);
    $in_stock = get_field('in-stock');
    $product_note = get_field('single_p_settings_product-note','option');

    $note_mode = $args['note_mode'];
    $product_note_custom = $args['product_note_custom'];
?>

<div class="product-info">

    <!-- Название -->
    <h1 class="body-xl product-title--js"><?= esc_html(get_the_title()); ?></h1>

    <!-- Описание -->
    <?php if (get_the_content()): ?>
        <div class="product-description ">
            <?= wp_kses_post(get_the_content()); ?>
        </div>
    <?php endif; ?>

    <?php
    // Everything a visitor tends to ask before ordering, folded away so the page
    // stays short. Size and coating are still *chosen* in the cart and the order
    // form — here we only explain them. Long-form versions live on the care &
    // sizing page, which each item links to.
    $order_terms = get_field('single_form_steps', 'option')['step1_text'] ?? '';
    $care_url    = function_exists('knot_page_url_with_block') ? knot_page_url_with_block('acf/care-sizing') : '';

    // Defined here rather than next to the cart button: the sizing item below
    // needs it first, and the button reads the same value further down.
    $needs_ring_size = knot_product_needs_ring_size(get_the_ID());
    ?>

    <div class="product-faq">

        <?php if ($order_terms): ?>
            <details class="product-terms" name="product-faq">
                <summary class="product-terms__summary">Оплата, доставка та терміни</summary>
                <div class="product-terms__body"><?= wp_kses_post($order_terms); ?></div>
            </details>
        <?php endif; ?>

        <details class="product-terms" name="product-faq">
            <summary class="product-terms__summary">Тип покриття: родій або позолота</summary>
            <div class="product-terms__body">
                <p>Срібло з часом природно темніє. Родій — метал платинової групи: тонкий шар створює захисний бар’єр від поту, косметики та побутової хімії, додає дзеркального білого блиску, краще тримається проти дрібних подряпин і не викликає алергії.</p>
                <p>Позолота працює так само, але дає теплий золотистий відтінок.</p>
                <p>Покриття обираєте під час оформлення замовлення. Воно впливає на фінальну вартість — я розрахую точну ціну й підтверджу її перед оплатою.</p>
                <?php if ($care_url): ?>
                    <p><a href="<?= esc_url($care_url . '#care-section_2'); ?>">Докладніше про родіювання</a></p>
                <?php endif; ?>
            </div>
        </details>

        <?php if ($needs_ring_size): ?>
            <details class="product-terms" name="product-faq">
                <summary class="product-terms__summary">Як визначити свій розмір</summary>
                <div class="product-terms__body">
                    <p>Найточніше — приміряти тоненьку класичну каблучку в ювелірній крамниці або скористатись пальцеміром.</p>
                    <p>Другий варіант — застосунок на телефоні (наприклад, Ring Sizer): прикладаєте рівну, недеформовану каблучку до екрана й підганяєте коло під її внутрішній діаметр.</p>
                    <p>Якщо нічого з цього немає — обгорніть палець ниточкою або смужкою паперу, зробіть позначку на місці стику й повідомте мені довжину. Вимірюйте наприкінці дня, коли палець найбільший, і повторіть заміри 2–3 рази.</p>
                    <?php if ($care_url): ?>
                        <p><a href="<?= esc_url($care_url . '#care-section_0'); ?>">Таблиця розмірів і поради</a></p>
                    <?php endif; ?>
                </div>
            </details>
        <?php endif; ?>

    </div>

    <!-- Цена -->
    <div class="product-price  product-price--js">
        <?php if (!empty($price)): ?>
            <h2 class="current"><?= esc_html($price); ?> ₴</h2>
        <?php endif; ?>

        <?php if (!empty($old_price)): ?>
            <h3 class="old " ><?= esc_html($old_price); ?> ₴</h3>
        <?php endif; ?>
    </div>

    <!-- Наличие -->
    <?php if (has_category('in-stock') && !empty($in_stock)): ?>
        <p class="product-stock "><?=$in_stock?></p>
    <?php endif; ?>

    <!-- Свойства продукта -->
    <ul class="product-props ">
        <?php
        // Each property links into the catalog pre-filtered by that term, so the
        // page is a starting point rather than a dead end.
        $catalog_url = function_exists('knot_catalog_url') ? knot_catalog_url() : home_url('/');

        $render_prop = static function (string $taxonomy, string $label, string $param, string $js_class) use ($catalog_url) {
            $terms = get_the_terms(get_the_ID(), $taxonomy);

            if (!$terms || is_wp_error($terms)) {
                return;
            }

            $links = [];
            foreach ($terms as $term) {
                $links[] = sprintf(
                    '<a href="%s">%s</a>',
                    esc_url(add_query_arg($param, $term->slug, $catalog_url)),
                    esc_html($term->name)
                );
            }

            printf(
                '<li>%s: <span class="%s">%s</span></li>',
                esc_html($label),
                esc_attr($js_class),
                implode(', ', $links)
            );
        };

        $render_prop('material', 'Матеріал', 'material', 'product-material--js');
        $render_prop('stone', 'Камінь', 'stone', 'product-stone--js');
        $render_prop('product_type', 'Тип виробу', 'type', 'product-type--js');
        ?>
    </ul>

    <?php if ($note_mode !== 'off'): ?>

        <div class="product-note ">

            <?php if ($note_mode === 'custom' && !empty($product_note_custom)): ?>

                <?= wp_kses_post($product_note_custom); ?>

            <?php else: ?>

                <?= wp_kses_post($product_note); ?>

            <?php endif; ?>

        </div>

    <?php endif; ?>

    <?php
    $product_image = get_the_post_thumbnail_url(get_the_ID(), 'large')
        ?: get_the_post_thumbnail_url(get_the_ID(), 'medium');

    $term_names = static function (string $taxonomy): string {
        $terms = get_the_terms(get_the_ID(), $taxonomy);
        if (!$terms || is_wp_error($terms)) {
            return '';
        }
        return implode(', ', wp_list_pluck($terms, 'name'));
    };
    $product_material = $term_names('material');
    $product_stone    = $term_names('stone');
    $product_type     = $term_names('product_type');
    ?>

    <div class="product-actions">
        <button
                type="button"
                class="btn-cart main-btn second"
                data-cart-add
                data-id="<?= esc_attr(get_the_ID()); ?>"
                data-title="<?= esc_attr(get_the_title()); ?>"
                data-price="<?= esc_attr($price ?? ''); ?>"
                data-link="<?= esc_url(get_permalink()); ?>"
                data-image="<?= esc_url($product_image ?: ''); ?>"
                data-material="<?= esc_attr($product_material); ?>"
                data-stone="<?= esc_attr($product_stone); ?>"
                data-type="<?= esc_attr($product_type); ?>"
                data-needs-size="<?= $needs_ring_size ? '1' : '0'; ?>"
        >Додати в кошик</button>

        <button class="btn-buy main-btn third" data-action="togglePopup" data-target="#example_popup">Купити зараз</button>
    </div>
</div>