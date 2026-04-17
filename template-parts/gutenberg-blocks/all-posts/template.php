<?php
$main_title   = get_field('all-posts_main_title') ?? '';
$description  = get_field('all-posts_description') ?? '';

$block_anchor = $block['anchor'] ?? '';
$block_classes = 'all-posts';

if (!empty($block['className'])) $block_classes .= ' ' . $block['className'];

?>

<section class="<?= esc_attr($block_classes) ?>" id="<?= esc_attr($block_anchor) ?>">
    <div class="container">
        <div class="all-posts__wrapper">
            <?php if ($main_title): ?>
                <h2><?= wp_kses_post($main_title); ?></h2>
            <?php endif; ?>

            <div class="all-posts__title-wrapper">
                <!-- ПОИСК -->
                <input class="all-posts__search" type="text" id="ajax-search" placeholder="Пошук..." autocomplete="off">

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
                <div id="posts-wrap" class="all-posts__posts-wrap"></div>
                <!-- ФИЛЬТРЫ -->
                <div class="filter-dropdown__bg"></div>
                <div class="all-posts__filters">
                    <div class="filter-dropdown">
                        <div class="filter-dropdown__content">

                            <div class="filter-dropdown__item">
                                <strong>Тип виробу</strong>
                                <?php foreach (get_terms(['taxonomy' => 'product_type', 'hide_empty' => false]) as $term): ?>
                                    <label>
                                        <input type="checkbox" class="filter-product_type" value="<?= esc_attr($term->slug); ?>">
                                        <div class="filter-arrow"></div>
                                        <?= esc_html($term->name); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="filter-dropdown__item">
                                <strong>Матеріал</strong>
                                <?php foreach (get_terms(['taxonomy' => 'material', 'hide_empty' => false]) as $term): ?>
                                    <label>
                                        <input type="checkbox" class="filter-material" value="<?= esc_attr($term->slug); ?>">
                                        <div class="filter-arrow"></div>
                                        <?= esc_html($term->name); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="filter-dropdown__item">
                                <strong>Камінь</strong>
                                <?php foreach (get_terms(['taxonomy' => 'stone', 'hide_empty' => false]) as $term): ?>
                                    <label>
                                        <input type="checkbox" class="filter-stone" value="<?= esc_attr($term->slug); ?>">
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
                <!-- Лоадер -->
<!--                <div class="loader" id="ajax-loader">-->
<!--                    <div class="loader__spinner"></div>-->
<!--                </div>-->
                <div class="loader" id="ajax-loader">
                    <div class="emerald-wrap">

                        <div class="emerald-glow"></div>
                        <div class="loader__spinner"></div>
                        <div class="emerald">
                            <div class="emerald__core"></div>
                            <div class="emerald__particles"></div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Лоадер море -->
            <button class="load-more main-btn primary_button" id="ajax-load-more-btn">Load More</button>
        </div>
    </div>
</section>