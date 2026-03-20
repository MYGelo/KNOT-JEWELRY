<?php
get_header();

// Получаем группу 404
$page_404 = get_field('page_404', 'option');

// Безопасные значения
$main_title = $page_404['main_title'] ?? '404';
$title = $page_404['title'] ?? 'Page not found';
$description = $page_404['description'] ?? '';
$link_text = $page_404['link_text'] ?? 'Go back home';
$bg = $page_404['background_image'] ?? [];
$bg_mob = $page_404['background_image_mob'] ?? '';

//require_once get_template_directory() . '/inc/utils/UtilityService.php';
//$bg = UtilityService::getImage($bg ?: null);
?>

    <main >

        <style>
            .error-404 {
                position: relative;
                padding-top: clamp(100px,10.07vw,145px);
                padding-bottom: clamp(32px,3.612vw,52px);
                min-height: 661px;

                .error-404__wrapper {
                    display: flex;
                    flex-direction: column;
                    align-items: flex-start;
                    max-width: clamp(443px,47.0833vw,678px);
                    padding: clamp(24px,2.78vw,40px);
                    border-radius: 16px;
                }

                h1,h2,p,a {
                    color: var(--white);
                }

                h1 {
                    font-size: clamp(132px,11.112vw,160px);
                    font-family: var(--font);
                    font-weight: 400;
                    margin-bottom: 16px;
                }

                h2 {
                    font-size: 32px;
                    margin-bottom: 24px;
                }

                p {
                    margin-bottom: 32px;
                }

                .error-404__bg img {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    z-index: -1;
                    border-radius: 0 0 32px 32px;
                }
            }
        </style>
        <section class="error-404">
            <div class="container">

                <div class="error-404__wrapper main-blur">
                    <?php if ($main_title): ?>
                        <h1><?php echo esc_html($main_title); ?></h1>
                    <?php endif; ?>

                    <?php if ($title): ?>
                        <h2><?php echo esc_html($title); ?></h2>
                    <?php endif; ?>

                    <?php if ($description): ?>
                        <p><?php echo esc_html($description); ?></p>
                    <?php endif; ?>

                    <?php if ($link_text): ?>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="main-btn">
                            <?php echo esc_html($link_text); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($bg && !empty($bg['url'])): ?>
                    <div class="error-404__bg">
                        <picture>
                            <!-- Mobile --> <source srcset="<?= !empty($bg_mob['sizes']['large']) ? $bg_mob['sizes']['large'] : $bg['sizes']['medium_large']; ?>" media="(max-width: 551px)">
                            <!-- Desktop --><source srcset="<?= $bg['url']; ?>" media="(min-width: 552px)">
                            <img
                                    src="<?= $bg['url'] ?>"
                                    alt="<?= $bg['alt'] ?: $bg['title'] ?>"
                                    width="<?= esc_attr($bg['width'] ?? '') ?>"
                                    height="<?= esc_attr($bg['height'] ?? '') ?>"
                                    fetchpriority="high"
                            >
                        </picture>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

<?php get_footer(); ?>