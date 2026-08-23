<?php
$main_title   = get_field('all-posts_main_title') ?? '';
$description  = get_field('all-posts_description') ?? '';
$is_sticky    = get_field('all-posts_use_sticky') ?? '';

$block_anchor = $block['anchor'] ?? '';
$block_classes = 'all-posts';

if (!empty($block['className'])) $block_classes .= ' ' . $block['className'];

// Catalog state from the URL (whitelisted terms) → render exactly that state
// server-side, so a direct link / refresh / crawler / no-JS all get it right.
$catalog          = site_catalog_request_filters();
$catalog_page_url = get_permalink() ?: home_url('/');

// Same cached resolver the REST endpoint uses, so SSR and AJAX agree and share
// the transient cache (plain facet combos, pages 1-3).
$catalog_results = site_catalog_get_results(
    $catalog['search'], $catalog['materials'], $catalog['stones'], $catalog['product_type'], [], $catalog['page'], 24
);

$catalog_has_filters = $catalog['search'] !== ''
    || $catalog['materials'] || $catalog['stones'] || $catalog['product_type'];

$active_materials = array_flip($catalog['materials']);
$active_stones    = array_flip($catalog['stones']);
$active_types     = array_flip($catalog['product_type']);
?>

<section class="<?= esc_attr($block_classes) ?> <?= $is_sticky ? 'sticky-item' : ''?>" id="<?= esc_attr($block_anchor) ?>"
         data-catalog-url="<?= esc_url($catalog_page_url) ?>"
         <?php // Available terms rendered server-side — saves an extra REST call on load. ?>
         <?= $catalog_has_filters ? 'data-available="' . esc_attr(wp_json_encode($catalog_results['available'])) . '"' : '' ?>>
    <div class="container">
        <div class="all-posts__wrapper">
            <?php if ($main_title): ?>
                <h2><?= wp_kses_post($main_title); ?></h2>
            <?php endif; ?>

            <div class="all-posts__title-wrapper">
                <!-- ПОИСК -->
                <div class="all-posts__search-wrap">
                    <input class="all-posts__search" type="text" id="ajax-search" placeholder="Пошук..." aria-label="Пошук" autocomplete="off" value="<?= esc_attr($catalog['search']) ?>">
                    <button type="button" class="all-posts__search-btn" id="ajax-search-icon-btn" aria-label="Пошук">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="7.5" cy="7.5" r="6" stroke="currentColor" stroke-width="1.3"/>
                            <path d="M12 12L16 16" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <div class="all-posts__suggestions" id="search-suggestions"></div>
                </div>

                <button class="all-posts__filter">
                    ФІЛЬТР
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_3836_2854)">
                            <path d="M21.0039 3.99999H14.0039" stroke="currentColor" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 4H3" stroke="currentColor" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12H12" stroke="currentColor" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 12H3" stroke="currentColor" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M20.9961 20H15.9961" stroke="currentColor" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 20H3" stroke="currentColor" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14.0039 2.00001V6.00001" stroke="currentColor" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8.00391 9.99999V14" stroke="currentColor" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15.9961 18V22" stroke="currentColor" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_3836_2854">
                                <rect width="24" height="24" fill="currentColor"/>
                            </clipPath>
                        </defs>
                    </svg>
                </button>
            </div>


            <?php if ($description): ?>
                <p><?= wp_kses_post($description); ?></p>
            <?php endif; ?>


            <div class="all-posts__posts-wrapper">
                <!-- ПОСТЫ -->
                <div id="posts-wrap" class="all-posts__posts-wrap">
                    <?= site_render_post_cards($catalog_results['post_ids']) ?>
                </div>

                <div id="ajax-pagination">
                    <?php
                    $total_pages   = $catalog_results['total_pages'];
                    $paged         = $catalog['page'];
                    $page_base_url = site_catalog_base_url($catalog);
                    include get_template_directory() . '/template-parts/components/pagination.php';?>
                </div>

                <!-- ФИЛЬТРЫ -->
                <div class="filter-dropdown__bg"></div>
                <div class="all-posts__filters">
                    <div class="filter-dropdown">
                        <div class="filter-dropdown__content">

                            <div class="filter-dropdown__item">
                                <strong>Матеріал</strong>
                                <?php foreach (get_terms(['taxonomy' => 'material', 'hide_empty' => false]) as $term): ?>
                                    <label>
                                        <input type="checkbox" class="filter-material" value="<?= esc_attr($term->slug); ?>" <?= isset($active_materials[$term->slug]) ? 'checked' : ''; ?>>
                                        <div class="filter-arrow"></div>
                                        <?= esc_html($term->name); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="filter-dropdown__item">
                                <strong>Тип виробу</strong>
                                <?php foreach (get_terms(['taxonomy' => 'product_type', 'hide_empty' => false]) as $term): ?>
                                    <label>
                                        <input type="checkbox" class="filter-product_type" value="<?= esc_attr($term->slug); ?>" <?= isset($active_types[$term->slug]) ? 'checked' : ''; ?>>
                                        <div class="filter-arrow"></div>
                                        <?= esc_html($term->name); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="filter-dropdown__item">
                                <strong>Камінь</strong>
                                <label>
                                    <input type="checkbox" class="filter-stone" value="no-stone" <?= isset($active_stones['no-stone']) ? 'checked' : ''; ?>>
                                    <div class="filter-arrow"></div>
                                    Без каменя
                                </label>
                                <?php foreach (get_terms(['taxonomy' => 'stone', 'hide_empty' => false]) as $term): ?>
                                    <label>
                                        <input type="checkbox" class="filter-stone" value="<?= esc_attr($term->slug); ?>" <?= isset($active_stones[$term->slug]) ? 'checked' : ''; ?>>
                                        <div class="filter-arrow"></div>
                                        <?= esc_html($term->name); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="filter-dropdown__buttons">
                                <button type="button" id="ajax-reset-btn">Обнулити</button>
                                <button type="button" id="ajax-search-btn" class="main-btn third">Пошук</button>
                            </div>
                        </div>
                    </div>
                    <div class="filter-dropdown__close">
                        <span></span><span></span>
                    </div>
                </div>

                <?php include(get_template_directory() . '/template-parts/components/loader/loader.php'); ?>
            </div>
        </div>
    </div>
</section>