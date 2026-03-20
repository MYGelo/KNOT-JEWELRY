<?php
// Разворачиваем переданные аргументы
extract($args);

?>

<div class="product-info">

    <!-- Название -->
    <h1 class="body-xl product-title--js"><?= esc_html(get_the_title()); ?></h1>

    <!-- Описание -->
    <?php if (get_the_content()): ?>
        <div class="product-description">
            <?= wp_kses_post(get_the_content()); ?>
        </div>
    <?php endif; ?>

    <!-- Цена -->
    <div class="product-price product-price--js">
        <?php if (!empty($price)): ?>
            <h2 class="current"><?= esc_html($price); ?> ₴</h2>
        <?php endif; ?>

        <?php if (!empty($old_price)): ?>
            <h3 class="old"><?= esc_html($old_price); ?> ₴</h3>
        <?php endif; ?>
    </div>

    <!-- Свойства продукта -->
    <ul class="product-props">
        <!-- Кастомные таксономии -->
        <?php
        // Матеріал
        $material_terms = get_the_terms(get_the_ID(), 'material');
        if ($material_terms && !is_wp_error($material_terms)) {
            $material_names = wp_list_pluck($material_terms, 'name');
            echo '<li>Матеріал: <span class="product-material--js">' . esc_html(implode(', ', $material_names)) . '</span></li>';
        }

        // Камінь
        $stone_terms = get_the_terms(get_the_ID(), 'stone');
        if ($stone_terms && !is_wp_error($stone_terms)) {
            $stone_names = wp_list_pluck($stone_terms, 'name');
            echo '<li>Камінь: <span class="product-stone--js">' . esc_html(implode(', ', $stone_names)) . '</span></li>';
        }

        // Тип виробу
        $type_terms = get_the_terms(get_the_ID(), 'product_type');
        if ($type_terms && !is_wp_error($type_terms)) {
            $type_names = wp_list_pluck($type_terms, 'name');
            echo '<li>Тип виробу: <span class="product-type--js">' . esc_html(implode(', ', $type_names)) . '</span></li>';
        }
        ?>
    </ul>

    <!-- Кнопка заказа -->
<!--    <a href="https://api.telegram.org/bot8622055916:AAG9C41IjiLjaIqSskYbfOyi0wTrX_A90Ls/sendMessage?chat_id=@KnotJewelryOrders&text=TEST_MESSAGE🚀" class="btn-buy main-btn third">Замовити виріб</a>-->
    <button
            class="btn-buy main-btn third"
            data-action="togglePopup"
            data-target="#example_popup"
    >
        Замовити виріб
    </button>
</div>