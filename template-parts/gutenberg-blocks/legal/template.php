<?php
$block_anchor = $block['anchor'] ?? '';
$block_classes = 'legal-layout';

if (!empty($block['className'])) {
    $block_classes .= ' ' . $block['className'];
}

$nav_box_title = get_field('sidebar_title');
$sections = get_field('sections') ?: [];

if (!empty($sections)) : ?>
    <section
            class="<?= esc_attr($block_classes); ?>"
        <?= $block_anchor ? 'id="' . esc_attr($block_anchor) . '"' : ''; ?>
    >
        <div class="container legal-layout__grid">

            <!-- LEFT CONTENT -->
            <div class="legal-content">

                <?php foreach ($sections as $section) :
                    $title     = $section['title'] ?? '';
                    $sub_title = $section['subtitle'] ?? '';
                    $text      = $section['content'] ?? '';

                    if (!$title && !$text) continue;

                    $anchor = sanitize_title($title);
                    ?>
                    <article class="legal-section" id="<?= esc_attr($anchor); ?>">

                        <?php if (!empty($title)) : ?>
                            <h2 class="legal-section__title">
                                <?= esc_html($title); ?>
                            </h2>
                        <?php endif; ?>

                        <?php if (!empty($sub_title)) : ?>
                            <h3 class="legal-section__subtitle">
                                <?= esc_html($sub_title); ?>
                            </h3>
                        <?php endif; ?>

                        <?php if (!empty($text)) : ?>
                            <div class="legal-section__text">
                                <?= wp_kses_post($text); ?>
                            </div>
                        <?php endif; ?>

                    </article>
                <?php endforeach; ?>

            </div>

            <!-- RIGHT STICKY NAV -->
            <aside class="legal-nav">
                <div class="legal-nav__box">

                    <?php if (!empty($nav_box_title)) : ?>
                        <h3 class="legal-nav__title">
                            <?= esc_html($nav_box_title); ?>
                        </h3>
                    <?php endif; ?>

                    <ul class="legal-nav__list">
                        <?php foreach ($sections as $section) :
                            $title = trim($section['title'] ?? '');
                            if (!$title) continue;

                            $anchor = sanitize_title($title);
                            ?>
                            <li>
                                <a href="#<?= esc_attr($anchor); ?>">
                                    <?= esc_html($title); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>

        </div>
    </section>
<?php endif; ?>