<?php
/**
 * Items table — a spreadsheet-like editor for products (posts).
 *
 * Lives in wp-admin on purpose: capabilities, nonces and admin styling are
 * handled by core, and the screen never appears on the public site. The
 * friendly /items-info URL just redirects here.
 */

const KNOT_ITEMS_PAGE       = 'items-info';
const KNOT_ITEMS_CAP        = 'manage_options';
const KNOT_ITEMS_PER_PAGE   = 50;
const KNOT_ITEMS_STOCK_SLUG = 'in-stock';

/* -------------------------------------------------------------
| MENU + FRIENDLY URL
------------------------------------------------------------- */

add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php',
        'Таблиця товарів',
        'Таблиця товарів',
        KNOT_ITEMS_CAP,
        KNOT_ITEMS_PAGE,
        'knot_items_render_page'
    );
});

/**
 * /items-info → the admin screen (logged-out users land on the login form).
 */
add_action('template_redirect', function () {
    $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/');

    if ($path !== KNOT_ITEMS_PAGE) {
        return;
    }

    wp_safe_redirect(admin_url('edit.php?page=' . KNOT_ITEMS_PAGE));
    exit;
});

/* -------------------------------------------------------------
| ASSETS
------------------------------------------------------------- */

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'posts_page_' . KNOT_ITEMS_PAGE) {
        return;
    }

    $css = get_template_directory() . '/assets/admin/items-table.css';
    $js  = get_template_directory() . '/assets/admin/items-table.js';

    if (file_exists($css)) {
        wp_enqueue_style('knot-items-table', get_template_directory_uri() . '/assets/admin/items-table.css', [], filemtime($css));
    }

    if (file_exists($js)) {
        wp_enqueue_script('knot-items-table', get_template_directory_uri() . '/assets/admin/items-table.js', [], filemtime($js), true);
        wp_localize_script('knot-items-table', 'knotItems', [
            'restUrl' => esc_url_raw(rest_url('site/v1/items')),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);
    }
});

/* -------------------------------------------------------------
| STOCK VALUE
------------------------------------------------------------- */

/**
 * The front end prints the `in-stock` meta as-is, so it stays a plain string:
 * "В наявності" plus an optional tail like "– 15.5 розмір". The table edits it
 * as a toggle + free-text tail and recomposes the string on save.
 */
function knot_items_stock_label(): string {
    return (string) apply_filters('knot_items_stock_label', 'В наявності');
}

function knot_items_stock_compose(string $note): string {
    $note = trim($note);
    return $note === '' ? knot_items_stock_label() : knot_items_stock_label() . ' ' . $note;
}

/** Split a stored string back into its optional tail. */
function knot_items_stock_note(string $stored): string {
    $label = knot_items_stock_label();

    if ($stored === '' || mb_strpos($stored, $label) !== 0) {
        return '';
    }

    return trim(mb_substr($stored, mb_strlen($label)));
}

/* -------------------------------------------------------------
| DATA
------------------------------------------------------------- */

/**
 * Load one page of products with everything the table needs, without N+1:
 * post caches are primed once and all terms are fetched in a single query.
 */
function knot_items_get_rows(int $paged, string $search): array {
    $query = new WP_Query([
        'post_type'      => 'post',
        'post_status'    => ['publish', 'draft', 'pending', 'private'],
        'posts_per_page' => KNOT_ITEMS_PER_PAGE,
        'paged'          => $paged,
        's'              => $search,
        'orderby'        => 'ID',
        'order'          => 'DESC',
        'fields'         => 'ids',
    ]);

    $ids = $query->posts;

    if ($ids) {
        _prime_post_caches($ids, false, true);
    }

    // All three taxonomies for every row in one go.
    $terms_by_post = [];
    if ($ids) {
        $objects = wp_get_object_terms($ids, ['material', 'stone', 'product_type'], ['fields' => 'all_with_object_id']);
        if (!is_wp_error($objects)) {
            foreach ($objects as $term) {
                $terms_by_post[$term->object_id][$term->taxonomy][] = (int) $term->term_id;
            }
        }
    }

    $rows = [];
    foreach ($ids as $id) {
        $rows[] = [
            'id'        => $id,
            'title'     => get_the_title($id),
            'price'     => get_post_meta($id, 'price', true),
            'old_price' => get_post_meta($id, 'old_price', true),
            // Availability is the `in-stock` category; the meta holds the text
            // shown next to it ("В наявності – 15.5 розмір").
            'in_stock'      => has_term(KNOT_ITEMS_STOCK_SLUG, 'category', $id),
            'in_stock_note' => knot_items_stock_note((string) get_post_meta($id, 'in-stock', true)),
            'status'    => get_post_status($id),
            'thumb'     => get_the_post_thumbnail_url($id, 'thumbnail'),
            'edit'      => get_edit_post_link($id, ''),
            'view'      => get_permalink($id),
            'terms'     => $terms_by_post[$id] ?? [],
        ];
    }

    return [
        'rows'        => $rows,
        'total'       => (int) $query->found_posts,
        'total_pages' => (int) $query->max_num_pages,
    ];
}

/* -------------------------------------------------------------
| RENDER
------------------------------------------------------------- */

function knot_items_render_page(): void {
    if (!current_user_can(KNOT_ITEMS_CAP)) {
        wp_die(__('Недостатньо прав.'));
    }

    $paged  = max(1, absint($_GET['paged'] ?? 1));
    $search = sanitize_text_field(wp_unslash($_GET['s'] ?? ''));

    $data = knot_items_get_rows($paged, $search);

    $taxonomies = [
        'material'     => ['label' => 'Матеріал', 'terms' => get_cached_terms('material')],
        'product_type' => ['label' => 'Тип виробу', 'terms' => get_cached_terms('product_type')],
        'stone'        => ['label' => 'Камінь', 'terms' => get_cached_terms('stone')],
    ];

    ?>
    <div class="wrap knot-items">
        <h1 class="wp-heading-inline">Таблиця товарів</h1>
        <p class="knot-items__subtitle">
            Знайдено: <strong><?= esc_html($data['total']) ?></strong>.
            Редагуйте прямо в таблиці — зміни зберігаються тільки після натискання «Зберегти зміни».
        </p>

        <form method="get" class="knot-items__search">
            <input type="hidden" name="page" value="<?= esc_attr(KNOT_ITEMS_PAGE) ?>">
            <input type="search" name="s" value="<?= esc_attr($search) ?>" placeholder="Пошук за назвою…">
            <button type="submit" class="button">Шукати</button>
            <?php if ($search): ?>
                <a class="button-link" href="<?= esc_url(admin_url('edit.php?page=' . KNOT_ITEMS_PAGE)) ?>">Скинути</a>
            <?php endif; ?>
        </form>

        <div class="knot-items__bar">
            <button type="button" class="button button-primary" data-items-save disabled>Зберегти зміни</button>
            <span class="knot-items__status" data-items-status aria-live="polite"></span>
        </div>

        <?php // A grid-based list rather than a <table>: the same layout has to
              // collapse into cards on phones, which fights table semantics. ?>
        <div class="knot-items__list">

            <div class="knot-items__head" aria-hidden="true">
                <span></span>
                <span>ID</span>
                <span></span>
                <span>Назва</span>
                <span>Ціна, ₴</span>
                <span></span>
            </div>

            <?php if (empty($data['rows'])): ?>
                <p class="knot-items__empty">Нічого не знайдено.</p>
            <?php endif; ?>

            <?php foreach ($data['rows'] as $row): ?>
                <article class="knot-items__item" data-item-row data-item-id="<?= esc_attr($row['id']) ?>">

                    <div class="knot-items__main">
                        <button type="button" class="knot-items__toggle" data-items-toggle aria-expanded="false" aria-label="Показати деталі">
                            <span class="dashicons dashicons-arrow-right"></span>
                        </button>

                        <span class="knot-items__id" data-label="ID"><?= esc_html($row['id']) ?></span>

                        <span class="knot-items__thumb">
                            <?php if ($row['thumb']): ?>
                                <img src="<?= esc_url($row['thumb']) ?>" alt="" width="40" height="40" loading="lazy">
                            <?php endif; ?>
                        </span>

                        <span class="knot-items__title" data-label="Назва">
                            <input type="text" class="knot-items__input" data-field="title" value="<?= esc_attr($row['title']) ?>">
                            <?php if ($row['status'] !== 'publish'): ?>
                                <span class="knot-items__badge"><?= esc_html($row['status']) ?></span>
                            <?php endif; ?>
                        </span>

                        <span class="knot-items__price" data-label="Ціна, ₴">
                            <input type="number" min="0" step="1" class="knot-items__input" data-field="price" value="<?= esc_attr($row['price']) ?>">
                        </span>

                        <span class="knot-items__links">
                            <?php if ($row['view']): ?><a href="<?= esc_url($row['view']) ?>" target="_blank" rel="noopener">Переглянути</a><?php endif; ?>
                            <?php if ($row['edit']): ?><a href="<?= esc_url($row['edit']) ?>">Редактор</a><?php endif; ?>
                        </span>
                    </div>

                    <div class="knot-items__details" data-item-details hidden>
                        <div class="knot-items__grid">

                            <div class="knot-items__field knot-items__field--wide knot-items__stock">
                                <label class="knot-items__check">
                                    <input type="checkbox" class="knot-items__input knot-items__switch-input" data-field="in_stock" <?= checked($row['in_stock'], true, false) ?>>
                                    <span class="knot-items__switch" aria-hidden="true"></span>
                                    <span class="knot-items__check-text">В наявності</span>
                                </label>

                                <label class="knot-items__field">
                                    <span>Уточнення (необов’язково)</span>
                                    <input type="text" class="knot-items__input" data-field="in_stock_note"
                                           value="<?= esc_attr($row['in_stock_note']) ?>"
                                           placeholder="– 15.5 розмір"
                                           <?= $row['in_stock'] ? '' : 'disabled' ?>>
                                </label>
                            </div>

                            <?php foreach ($taxonomies as $tax => $conf):
                                $selected = $row['terms'][$tax] ?? [];
                                ?>
                                <label class="knot-items__field">
                                    <span><?= esc_html($conf['label']) ?></span>
                                    <select multiple size="4" class="knot-items__input" data-field="tax" data-taxonomy="<?= esc_attr($tax) ?>">
                                        <?php foreach ((array) $conf['terms'] as $term): ?>
                                            <option value="<?= esc_attr($term->term_id) ?>" <?= in_array((int) $term->term_id, $selected, true) ? 'selected' : '' ?>>
                                                <?= esc_html($term->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            <?php endforeach; ?>

                        </div>
                    </div>

                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($data['total_pages'] > 1): ?>
            <div class="tablenav"><div class="tablenav-pages">
                <?= paginate_links([
                    'base'    => add_query_arg('paged', '%#%'),
                    'format'  => '',
                    'current' => $paged,
                    'total'   => $data['total_pages'],
                ]) ?>
            </div></div>
        <?php endif; ?>

        <div class="knot-items__bar">
            <button type="button" class="button button-primary" data-items-save disabled>Зберегти зміни</button>
        </div>
    </div>
    <?php
}

/* -------------------------------------------------------------
| SAVE (REST)
------------------------------------------------------------- */

add_action('rest_api_init', function () {
    register_rest_route('site/v1', '/items', [
        'methods'             => 'POST',
        'callback'            => 'knot_items_save',
        'permission_callback' => static fn() => current_user_can(KNOT_ITEMS_CAP),
    ]);
});

function knot_items_save(WP_REST_Request $request) {
    $items = $request->get_param('items');

    if (!is_array($items) || empty($items)) {
        return new WP_Error('knot_items_empty', 'Немає даних для збереження.', ['status' => 400]);
    }

    $saved  = [];
    $failed = [];

    foreach ($items as $item) {
        $id = absint($item['id'] ?? 0);

        // Every row is authorised on its own — never trust the incoming ID.
        if (!$id || get_post_type($id) !== 'post' || !current_user_can('edit_post', $id)) {
            $failed[] = $id;
            continue;
        }

        if (array_key_exists('title', $item)) {
            $title = sanitize_text_field((string) $item['title']);
            if ($title !== '' && $title !== get_the_title($id)) {
                wp_update_post(['ID' => $id, 'post_title' => $title]);
            }
        }

        foreach (['price' => 'price', 'old_price' => 'old_price'] as $key => $meta_key) {
            if (!array_key_exists($key, $item)) continue;

            $raw = trim((string) $item[$key]);
            // Whole hryvnias only.
            $raw === '' ? delete_post_meta($id, $meta_key) : update_post_meta($id, $meta_key, absint($raw));
        }

        // Availability = membership in the `in-stock` category (other categories
        // are left untouched) + the display text kept in the `in-stock` meta.
        if (array_key_exists('in_stock', $item)) {
            $in_stock = !empty($item['in_stock']) && $item['in_stock'] !== 'false';
            $stock_term = get_term_by('slug', KNOT_ITEMS_STOCK_SLUG, 'category');

            if ($stock_term && !is_wp_error($stock_term)) {
                $current = wp_get_object_terms($id, 'category', ['fields' => 'ids']);
                $current = is_wp_error($current) ? [] : array_map('intval', $current);

                $next = $in_stock
                    ? array_unique(array_merge($current, [(int) $stock_term->term_id]))
                    : array_diff($current, [(int) $stock_term->term_id]);

                if (array_values($next) !== array_values($current)) {
                    wp_set_object_terms($id, array_values($next), 'category', false);
                }
            }

            if (!$in_stock) {
                delete_post_meta($id, 'in-stock');
            } else {
                $note = sanitize_text_field((string) ($item['in_stock_note'] ?? ''));
                update_post_meta($id, 'in-stock', knot_items_stock_compose($note));
            }
        }

        // Taxonomies: term IDs are verified to belong to the taxonomy they claim.
        if (!empty($item['tax']) && is_array($item['tax'])) {
            foreach (['material', 'stone', 'product_type'] as $taxonomy) {
                if (!array_key_exists($taxonomy, $item['tax'])) continue;

                $ids = array_map('absint', (array) $item['tax'][$taxonomy]);
                $ids = array_values(array_filter($ids, static function ($term_id) use ($taxonomy) {
                    $term = get_term($term_id, $taxonomy);
                    return $term && !is_wp_error($term);
                }));

                wp_set_object_terms($id, $ids, $taxonomy, false);
            }
        }

        $saved[] = $id;
    }

    // Catalog caches are invalidated by the standard save_post / meta / term
    // hooks these functions fire, so nothing extra is needed here.

    return rest_ensure_response([
        'saved'  => $saved,
        'failed' => $failed,
    ]);
}
