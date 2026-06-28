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

    // Дефолтні терміни — тільки якщо ще не створені (один раз за весь час)
    // get_option() з autoload=yes безкоштовний: вже в пам'яті при кожному запиті
    if (get_option('knot_default_terms_seeded')) {
        return;
    }

    $materials = ['Позолота', 'Срібло'];
    foreach ($materials as $term) {
        if (!term_exists($term, 'material')) {
            wp_insert_term($term, 'material');
        }
    }

    $stones = ['Гранат', 'Лабрадорит', 'Місячний камінь', 'Опал', 'Халцедон'];
    foreach ($stones as $term) {
        if (!term_exists($term, 'stone')) {
            wp_insert_term($term, 'stone');
        }
    }

    $types = ['Каблучки', 'Сережки'];
    foreach ($types as $term) {
        if (!term_exists($term, 'product_type')) {
            wp_insert_term($term, 'product_type');
        }
    }

    update_option('knot_default_terms_seeded', true); // autoload=yes за замовчуванням
});
