<?php
$main_title         = get_field('main_banner_main_title') ?? '';
$title              = get_field('main_banner_title') ?? '';
$description        = get_field('main_banner_description') ?? '';
$link               = get_field('main_banner_link') ?? '';
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
    <div class="container">
        <div class="main-banner__text-con">
            <div class="main-banner__wrapper <?= esc_attr(" align-h-$align_h align-v-$align_v") ?>" style="<?= esc_attr($wrapper_style); ?>">
                <?php if ($main_title): ?>
                    <h1 class="scroll-animate"><?php echo wp_kses_post($main_title); ?></h1>
                <?php endif; ?>

                <?php if ($description): ?>
                    <p class="scroll-animate"><?php echo wp_kses_post($description); ?></p>
                <?php endif; ?>

                <?php if (!empty($link['url'])):?>
                    <div class="main-banner__link_wrap scroll-animate">
                        <a class="main-btn primary_button five"
                           data-action="togglePopup"
                           data-target="#example_popup"
                           href="<?=esc_url($link['url']);?>"
                           target="<?=esc_attr($link['target']) ?: '_self';?>"
                        ><?= wp_kses_post($link['title']);?>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_2185_6107)">
                                    <path d="M2.76502 13.8167L14.0787 2.50302M14.0787 2.50302L14.393 13.5025M14.0787 2.50302L3.07929 2.18875" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_2185_6107">
                                        <rect width="16" height="16" fill="white"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </a>
                    </div>
                <?php endif;?>
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
