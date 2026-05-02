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
                    <div class=""><?php echo wp_kses_post($description); ?></div>
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

                            ?>

                            <div class="main-banner__feature">
                                <?php if (!empty($icon)): ?>
                                    <div class="main-banner__feature-img">
                                        <picture>
                                            <!-- Mobile --> <source srcset="<?= $icon['sizes']['medium_large']; ?>" media="(max-width: 551px)">
                                            <!-- Desktop --><source srcset="<?= $icon['url']; ?>" media="(min-width: 552px)">
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

                            </div>

                        <?php endwhile; ?>

                    </div>
                <?php endif; ?>
            </div>

            <?php if (is_array($poster_png) && !empty($poster_png['url'])): ?>
                <div class="main-banner__png">
                    <picture>
                        <!-- Mobile --> <source srcset="<?= $poster_png['sizes']['medium_large']; ?>" media="(max-width: 551px)">
                        <!-- Desktop --><source srcset="<?= $poster_png['url']; ?>" media="(min-width: 552px)">
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


        <?php if (is_array($poster) && !empty($poster['url'])): ?>
            <div class="main-banner__bg">
                <picture>
                    <!-- Mobile --> <source srcset="<?= $poster_mob['sizes']['large'] ?? $poster['sizes']['medium_large']; ?>" media="(max-width: 551px)">
                    <!-- Desktop --><source srcset="<?= $poster['url']; ?>" media="(min-width: 552px)">
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
                    <video
                            class="main-banner__video"
                            autoplay
                            muted
                            loop
                            playsinline
                            preload="auto"
                    >
                        <source src="<?= esc_url($video); ?>" type="video/mp4">
                    </video>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
