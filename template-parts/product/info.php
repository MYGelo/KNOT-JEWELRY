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
    // Also read by the cart button further down, so it is resolved once here.
    $needs_ring_size = knot_product_needs_ring_size(get_the_ID());

    get_template_part('template-parts/product/faq', null, [
        'needs_ring_size' => $needs_ring_size,
    ]);
    ?>

    <!-- Цена -->
    <div class="product-price  product-price--js">
        <?php if (!empty($price)): ?>
            <h2 class="current"><?= esc_html($price); ?> ₴</h2>
        <?php endif; ?>

        <?php if (!empty($old_price)): ?>
            <h3 class="old " ><?= esc_html($old_price); ?> ₴</h3>
        <?php endif; ?>

        <!-- Наличие -->
        <?php if (has_category('in-stock') && !empty($in_stock)): ?>
            <p class="product-stock "><?=$in_stock?></p>
        <?php endif; ?>
    </div>



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