<?php
$main_title         = get_field('main_banner_main_title') ?? '';
$sub_title          = get_field('main_banner_sub_title') ?? '';
$description        = get_field('main_banner_description') ?? '';
$links              = get_field('main_banner_links') ?? '';
$poster             = get_field('main_banner_bg') ?? [];
$min_height         = get_field('main_banner_min_height') === 'true';
$min_height_class   = $min_height ? '' : 'min-height';
$shadow             = get_field('main_banner_shadow') === 'true';
$shadow_class       = $shadow ? ' has-shadow' : '';
$poster_mob         = get_field('main_banner_bg_mob') ?? [];
$poster_png         = get_field('main_banner_bg_png') ?? [];
$video              = get_field('main_banner_video') ?? '';

$align_h = get_field('main_banner_text_align_h') ?: 'left';
$align_v = get_field('main_banner_text_align_v') ?: 'center';

$max_width = get_field('main_banner_max_width');
$wrapper_style = $max_width ? "max-width: {$max_width}px;" : "";

$block_anchor   = $block['anchor'] ?? '';
$block_classes  = 'main-banner';


if (!empty($block['className'])) {
    $block_classes .= ' ' . $block['className'];
}
?>

<section class="<?= esc_attr($block_classes) ?> <?= esc_attr($min_height_class); ?> <?= esc_attr($shadow_class); ?>" id="<?= esc_attr($block_anchor) ?>">
    <?php get_template_part('template-parts/components/breadcrumbs'); ?>

    <div class="container">
        <div class="main-banner__text-con">
            <div class="main-banner__wrapper <?= esc_attr(" align-h-$align_h align-v-$align_v") ?>" style="<?= esc_attr($wrapper_style); ?>">
                <?php if(!empty($sub_title)): ?>
                    <p class="sub-title"><?=wp_kses_post($sub_title);?></p>
                <?php endif; ?>

                <?php if (!empty($main_title)): ?>
                    <h1 class=""><?php echo wp_kses_post($main_title); ?></h1>
                <?php endif; ?>

                <?php if ($description): ?>
                    <div class="sub-text"><?php echo wp_kses_post($description); ?></div>
                <?php endif; ?>

                <?php if($links): ?>
                    <div class="main-banner__links">
                        <?php foreach ($links as $items):
                            $style = $items['style'];
                            $link = $items['link'];
                            ?>
                            <a href="<?= esc_url($link['url']) ?>"
                               class="main-btn  <?= esc_attr($style) ?>_button "
                               target="<?= esc_attr($link['target']) ?>">
                                <?= esc_html($link['title']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if(have_rows('main_banner_features')): ?>
                    <div class="main-banner__features">

                        <?php while(have_rows('main_banner_features')): the_row();

                            $icon = get_sub_field('icon');
                            $text = get_sub_field('text');
                            $link = get_sub_field('link');

                            $feature_tag   = !empty($link['url']) ? 'a' : 'div';
                            $feature_attrs = '';
                            if (!empty($link['url'])) {
                                $feature_attrs = ' href="' . esc_url($link['url']) . '"'
                                    . ' target="' . esc_attr($link['target'] ?: '_self') . '"'
                                    . ($link['target'] === '_blank' ? ' rel="noopener"' : '');
                            }
                            ?>

                            <<?= $feature_tag ?> class="main-banner__feature<?= !empty($link['url']) ? ' main-banner__feature--link' : '' ?>"<?= $feature_attrs ?>>
                                <?php if (!empty($icon)): ?>
                                    <div class="main-banner__feature-img">
                                        <picture>
                                            <!-- Mobile --> <source srcset="<?= esc_url($icon['sizes']['medium_large']); ?>" media="(max-width: 551px)">
                                            <!-- Desktop --><source srcset="<?= esc_url($icon['url']); ?>" media="(min-width: 552px)">
                                            <img
                                                    class=""
                                                    src="<?= esc_url($icon['sizes']['large'] ?: $icon['sizes']['medium_large']); ?>"
                                                    alt="<?= esc_attr($icon['alt'] ?: $icon['title']); ?>"
                                                    width="<?= esc_attr($icon['width'] ?? ''); ?>"
                                                    height="<?= esc_attr($icon['height'] ?? ''); ?>"
                                                    loading="lazy"
                                                    decoding="async"
                                            >
                                        </picture>
                                    </div>
                                <?php endif; ?>

                                <p class="main-banner__feature-text"><?= wp_kses_post($text) ?></p>

                            </<?= $feature_tag ?>>

                        <?php endwhile; ?>

                    </div>
                <?php endif; ?>
            </div>

            <?php if (is_array($poster_png) && !empty($poster_png['url'])):
                $png_srcset = !empty($poster_png['ID']) ? wp_get_attachment_image_srcset($poster_png['ID'], 'full') : '';
                ?>
                <div class="main-banner__png">
                    <picture>
                        <!-- Mobile --> <source srcset="<?= esc_url($poster_png['sizes']['medium_large']); ?>" media="(max-width: 551px)">
                        <!-- Desktop --><source
                                srcset="<?= esc_attr($png_srcset ?: $poster_png['url']); ?>"
                                sizes="(max-width: 991px) 60vw, 40vw"
                                media="(min-width: 552px)">
                        <img
                                src="<?= esc_url($poster_png['sizes']['medium_large']); ?>"
                                alt="<?= esc_attr($poster_png['alt'] ?: $poster_png['title']); ?>"
                                width="<?= esc_attr($poster_png['width'] ?? ''); ?>"
                                height="<?= esc_attr($poster_png['height'] ?? ''); ?>"
                                fetchpriority="high"
                        >
                    </picture>
                </div>
            <?php endif; ?>

        </div>


        <?php if (is_array($poster) && !empty($poster['url'])):

            // Hand the browser every generated size instead of one hard-coded
            // URL — otherwise every screen above 552px downloads the full
            // original (2048px, ~470 KB) no matter how small it is displayed.
            $poster_srcset     = !empty($poster['ID']) ? wp_get_attachment_image_srcset($poster['ID'], 'full') : '';
            $poster_mob_id     = $poster_mob['ID'] ?? 0;
            $poster_mob_srcset = $poster_mob_id ? wp_get_attachment_image_srcset($poster_mob_id, 'large') : '';
            $poster_mob_src    = $poster_mob['sizes']['large'] ?? $poster['sizes']['medium_large'];
            ?>
            <div class="main-banner__bg">
                <picture>
                    <!-- Mobile --> <source
                            srcset="<?= esc_attr($poster_mob_srcset ?: $poster_mob_src); ?>"
                            sizes="100vw"
                            media="(max-width: 551px)">
                    <!-- Desktop --><source
                            srcset="<?= esc_attr($poster_srcset ?: $poster['url']); ?>"
                            sizes="100vw"
                            media="(min-width: 552px)">
                    <img
                            src="<?= esc_url($poster['sizes']['large'] ?: $poster['sizes']['medium_large']); ?>"
                            alt="<?= esc_attr($poster['alt'] ?: $poster['title']); ?>"
                            width="<?= esc_attr($poster['width'] ?? ''); ?>"
                            height="<?= esc_attr($poster['height'] ?? ''); ?>"
                            fetchpriority="high"
                    >
                </picture>

                <!-- VIDEO -->
                <?php if (!empty($video)): ?>
                    <?php // No <source> and preload="none": the file is several
                          // megabytes, so the script attaches it only on wide
                          // screens, on a decent connection and once visible.
                          // The poster image above stands in everywhere else. ?>
                    <video
                            class="main-banner__video"
                            muted
                            loop
                            playsinline
                            preload="none"
                            <?php // Native fallback: shows the same frame the
                                  // poster image uses if CSS or JS misbehaves. ?>
                            poster="<?= esc_url($poster['sizes']['large'] ?? $poster['url'] ?? ''); ?>"
                            data-banner-video="<?= esc_url($video); ?>"
                    ></video>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
