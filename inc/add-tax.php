<?php
/**
 * Product taxonomies.
 *
 * The catalog lives entirely inside the all-posts block (filter state is kept
 * in query params on whatever page the block sits on), so these taxonomies get
 * NO public archives — that would create a second, competing set of URLs for
 * the same products. Admin UI and REST stay on.
 */

add_action('init', function () {

    $shared = [
        'hierarchical'       => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'show_admin_column'  => true,
        'public'             => false,
        'publicly_queryable' => false,
        'query_var'          => false,
        'rewrite'            => false,
    ];

    // ===============================
    // 1. Таксономія: Матеріал
    // ===============================
    register_taxonomy('material', ['post'], array_merge($shared, [
        'labels' => [
            'name'          => 'Матеріал',
            'singular_name' => 'Матеріал',
            'menu_name'     => 'Матеріал',
            'all_items'     => 'Всі матеріали',
            'edit_item'     => 'Редагувати матеріал',
            'add_new_item'  => 'Додати матеріал',
        ],
    ]));

    // ===============================
    // 2. Таксономія: Камінь
    // ===============================
    register_taxonomy('stone', ['post'], array_merge($shared, [
        'labels' => [
            'name'          => 'Камінь',
            'singular_name' => 'Камінь',
            'menu_name'     => 'Камінь',
            'all_items'     => 'Всі камені',
            'edit_item'     => 'Редагувати камінь',
            'add_new_item'  => 'Додати камінь',
        ],
    ]));

    // ===============================
    // 3. Таксономія: Тип виробу
    // ===============================
    register_taxonomy('product_type', ['post'], array_merge($shared, [
        'labels' => [
            'name'          => 'Тип виробу',
            'singular_name' => 'Тип виробу',
            'menu_name'     => 'Тип виробу',
            'all_items'     => 'Всі типи',
            'edit_item'     => 'Редагувати тип',
            'add_new_item'  => 'Додати тип',
        ],
    ]));

});

/**
 * One-time term seeding / slug normalisation.
 *
 * Latin (transliterated) slugs keep filter URLs readable — `?m=sriblo` instead
 * of percent-encoded Cyrillic. Runs in admin or WP-CLI only (never writes to the
 * DB on a visitor request) and is gated by a version option; bump it to re-run.
 */
add_action('admin_init', 'knot_normalize_product_terms');

function knot_normalize_product_terms(): void {

    if (get_option('knot_terms_slug_version') === '4') {
        return;
    }

    $map = [
        'material' => [
            'Позолота' => 'pozolota',
            'Срібло'   => 'sriblo',
        ],
        'stone' => [
            'Гранат'          => 'granat',
            'Лабрадорит'      => 'labradoryt',
            'Місячний камінь' => 'misiachnyi-kamin',
            'Опал'            => 'opal',
            'Халцедон'        => 'khaltsedon',
        ],
        'product_type' => [
            'Каблучки' => 'kabluchky',
            'Сережки'  => 'serezhky',
        ],
    ];

    // Match by SLUG first (a renamed term keeps its slug), then by name. This
    // avoids re-creating a duplicate when the editor renames a term.
    foreach ($map as $taxonomy => $terms) {
        foreach ($terms as $name => $slug) {

            $existing = get_term_by('slug', $slug, $taxonomy);

            if (!$existing) {
                $existing = get_term_by('name', $name, $taxonomy);
            }

            if (!$existing) {
                wp_insert_term($name, $taxonomy, ['slug' => $slug]);
                continue;
            }

            if ($existing->slug !== $slug) {
                wp_update_term($existing->term_id, $taxonomy, ['slug' => $slug]);
            }
        }
    }

    // Fallback: any other term still carrying a Cyrillic / percent-encoded slug
    // (e.g. added by hand) gets transliterated too.
    $translit = static function (string $text): string {
        $table = [
            'а'=>'a','б'=>'b','в'=>'v','г'=>'h','ґ'=>'g','д'=>'d','е'=>'e','є'=>'ie','ж'=>'zh',
            'з'=>'z','и'=>'y','і'=>'i','ї'=>'i','й'=>'i','к'=>'k','л'=>'l','м'=>'m','н'=>'n',
            'о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'kh','ц'=>'ts',
            'ч'=>'ch','ш'=>'sh','щ'=>'shch','ь'=>'','ю'=>'iu','я'=>'ia','ъ'=>'','ы'=>'y','э'=>'e','ё'=>'e',
        ];
        return sanitize_title(strtr(mb_strtolower($text, 'UTF-8'), $table));
    };

    foreach (array_keys($map) as $taxonomy) {
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
        if (!is_array($terms)) continue;

        foreach ($terms as $term) {
            if (preg_match('/%|[^\x00-\x7F]/', $term->slug)) {
                $new = $translit($term->name);
                if ($new && $new !== $term->slug) {
                    wp_update_term($term->term_id, $taxonomy, ['slug' => $new]);
                }
            }
        }
    }

    update_option('knot_terms_slug_version', '4');
}
