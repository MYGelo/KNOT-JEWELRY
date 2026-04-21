<?php
$button_text = get_field( 'button_text', 'option' );
$button_link = get_field( 'button_link', 'option' );
$footer_social = get_field('footer_social_repeater', 'option');
$logo = get_field( 'header_logo', 'option' );
?>

    <!doctype html>
<html <?php language_attributes(); ?>>

    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta
                name="viewport"
                content="width=device-width, initial-scale=1, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"
        >
        <title>
            <?php
            $title = '';
            if ( class_exists( 'WPSEO_Frontend' ) ) {
                $yoast = WPSEO_Frontend::get_instance();
                $title = $yoast->title( get_the_ID() );
                if ( ! $title ) {
                    $title = bloginfo( 'name' );
                    if ( ! is_front_page() ) {
                        $title = get_the_title() . ' - ' . bloginfo( 'name' );
                    }
                }
            }

            echo $title;
            ?>
        </title>

        <?php $site_url = get_site_url(); ?>
        <link
                rel="preconnect"
                href="<?= $site_url ?>"
        >
        <link
                rel="dns-prefetch"
                href="<?= $site_url ?>"
        >

        <?php include_once __DIR__ . '/inc/preloads.php' ?>

        <?php wp_head(); ?>
    </head>

<body <?php body_class( 'page__body' ); ?>>
<?php wp_body_open(); ?>

    <header
            class="header bg_header"
            id="header"
    >
        <div class="container bg_header">
            <div class="flex_wrapper">
                <div class="logo_wrapper">
                    <a
                            href="/"
                            class="site-logo"
                    >
                         <?php if (!empty($logo['url'])): ?>
                             <picture>
                                 <!-- Mobile --> <source srcset="<?= $logo['sizes']['medium_large']; ?>" media="(max-width: 551px)">
                                 <!-- Desktop --><source srcset="<?= $logo['url']; ?>" media="(min-width: 552px)">
                                 <img
                                     class=""
                                     src="<?= esc_url($logo['sizes']['large'] ?: $logo['sizes']['medium_large']); ?>"
                                     alt="<?= esc_attr($logo['alt'] ?: $logo['title']); ?>"
                                     width="<?= esc_attr($logo['width'] ?? ''); ?>"
                                     height="<?= esc_attr($logo['height'] ?? ''); ?>"
                                     fetchpriority="high"
                                 >
                             </picture>
                         <?php endif; ?>
                    </a>
                </div>

                <div class="nav_box nav_box--desc">
                    <?php wp_nav_menu( [
                        'theme_location' => 'main-menu',
                        'container'      => false,
                        'menu_class'     => 'header_menu',
                    ] ); ?>
                </div>

                <div class="user_box">
                    <?php if ( ! empty( $button_text ) && ! empty( $button_link ) ): ?>
                        <button
                                class="main-btn"
                                onclick="window.location.href='<?= esc_url( $button_link ); ?>'"
                        >
                            <?= wp_kses_post( $button_text ); ?>
                        </button>
                    <?php endif; ?>

                    <?php //get_template_part('template-parts/components/basket'); ?>
                </div>

                <span
                        class="burger hide_desktop"
                        data-action="toggleMobileMenu"
                >
                <span></span>
                <span></span>
                <span></span>
            </span>
            </div>
        </div>

        <div class="nav_box nav_box--mobile">
            <?php wp_nav_menu( [
                'theme_location' => 'main-menu',
                'container'      => false,
                'menu_class'     => 'header_menu',
            ] ); ?>
            <?php if ($footer_social): ?>
                <li class="header__social">
                    <?php foreach ($footer_social as $social): ?>
                        <a class="header__social-link media-bounce"
                           href="<?= esc_url($social['url']); ?>"
                           target="_blank"
                           rel="noopener"
                        >

                            <?php if (!empty($social['icon']['url'])): ?>
                                <picture>
                                    <!-- Mobile --> <source srcset="<?= $social['icon']['sizes']['medium_large']; ?>" media="(max-width: 551px)">
                                    <!-- Desktop --><source srcset="<?= $social['icon']['url']; ?>" media="(min-width: 552px)">
                                    <img
                                            class=""
                                            src="<?= esc_url($social['icon']['sizes']['large'] ?: $social['icon']['sizes']['medium_large']); ?>"
                                            alt="<?= esc_attr($social['icon']['alt'] ?: $social['icon']['title']); ?>"
                                            width="<?= esc_attr($social['icon']['width'] ?? ''); ?>"
                                            height="<?= esc_attr($social['icon']['height'] ?? ''); ?>"
                                            loading="lazy"
                                            decoding="async"
                                    >
                                </picture>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </li>
            <?php endif; ?>
        </div>
    </header>

<?php //get_template_part('template-parts/components/header/cart-popup'); ?>