<?php add_action('init', function() {

    // ===============================
    // 1. Таксономія: Матеріал
    // ===============================
    register_taxonomy('material', ['post'], [
        'labels' => [
            'name' => 'Матеріал',
            'singular_name' => 'Матеріал',
            'menu_name' => 'Матеріал',
            'all_items' => 'Всі матеріали',
            'edit_item' => 'Редагувати матеріал',
            'add_new_item' => 'Додати матеріал',
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'public' => true,
    ]);

    // Создаем термины
    $materials = ['Позолота', 'Срібло'];
    foreach ($materials as $term) {
        if (!term_exists($term, 'material')) {
            wp_insert_term($term, 'material');
        }
    }

    // ===============================
    // 2. Таксономія: Камінь
    // ===============================
    register_taxonomy('stone', ['post'], [
        'labels' => [
            'name' => 'Камінь',
            'singular_name' => 'Камінь',
            'menu_name' => 'Камінь',
            'all_items' => 'Всі камені',
            'edit_item' => 'Редагувати камінь',
            'add_new_item' => 'Додати камінь',
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'public' => true,
    ]);

    // Создаем термины
    $stones = ['Гранат', 'Лабрадорит', 'Місячний камінь', 'Опал', 'Халцедон'];
    foreach ($stones as $term) {
        if (!term_exists($term, 'stone')) {
            wp_insert_term($term, 'stone');
        }
    }

    // ===============================
    // 3. Таксономія: Тип виробу
    // ===============================
    register_taxonomy('product_type', ['post'], [
        'labels' => [
            'name' => 'Тип виробу',
            'singular_name' => 'Тип виробу',
            'menu_name' => 'Тип виробу',
            'all_items' => 'Всі типи',
            'edit_item' => 'Редагувати тип',
            'add_new_item' => 'Додати тип',
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'public' => true,
    ]);

    // Создаем термины
    $types = ['Каблучки', 'Сережки'];
    foreach ($types as $term) {
        if (!term_exists($term, 'product_type')) {
            wp_insert_term($term, 'product_type');
        }
    }

});