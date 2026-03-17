<?php
// Разворачиваем переданные аргументы
extract($args);
?>

<div class="product-info">

    <!-- Название -->
    <h1 class="body-xl"><?= esc_html(get_the_title()); ?></h1>

    <!-- Описание -->
    <?php if (get_the_content()): ?>
        <div class="product-description">
            <?= wp_kses_post(get_the_content()); ?>
        </div>
    <?php endif; ?>

    <!-- Цена -->
    <div class="product-price">
        <?php if (!empty($price)): ?>
            <h2 class="current"><?= esc_html($price); ?> ₴</h2>
        <?php endif; ?>

        <?php if (!empty($old_price)): ?>
            <h3 class="old"><?= esc_html($old_price); ?> ₴</h3>
        <?php endif; ?>
    </div>

    <!-- Свойства продукта -->
    <ul class="product-props">
        <!-- Поля из ACF/meta -->
        <?php if (!empty($metal)): ?>
            <li><strong>Метал:</strong> <?= esc_html($metal); ?></li>
        <?php endif; ?>

        <?php if (!empty($test)): ?>
            <li><strong>Проба:</strong> <?= esc_html($test); ?></li>
        <?php endif; ?>

        <!-- Кастомные таксономии -->
        <?php
        // Матеріал
        $material_terms = get_the_terms(get_the_ID(), 'material');
        if ($material_terms && !is_wp_error($material_terms)) {
            $material_names = wp_list_pluck($material_terms, 'name');
            echo '<li><strong>Матеріал:</strong> ' . esc_html(implode(', ', $material_names)) . '</li>';
        }

        // Камінь
        $stone_terms = get_the_terms(get_the_ID(), 'stone');
        if ($stone_terms && !is_wp_error($stone_terms)) {
            $stone_names = wp_list_pluck($stone_terms, 'name');
            echo '<li><strong>Камінь:</strong> ' . esc_html(implode(', ', $stone_names)) . '</li>';
        }

        // Тип виробу
        $type_terms = get_the_terms(get_the_ID(), 'product_type');
        if ($type_terms && !is_wp_error($type_terms)) {
            $type_names = wp_list_pluck($type_terms, 'name');
            echo '<li><strong>Тип виробу:</strong> ' . esc_html(implode(', ', $type_names)) . '</li>';
        }
        ?>
    </ul>

    <!-- Кнопка заказа -->
    <a href="#order" class="btn-buy main-btn third">Замовити виріб</a>

</div>