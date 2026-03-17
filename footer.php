<?php
$footer_logo = get_field('footer_logo', 'option') ?? null;
$footer_social = get_field('footer_social_repeater', 'option');

// footer_bottom_group
$footer_bottom = get_field('footer_bottom_group', 'option');
$link_1 = $footer_bottom['footer_link_1'] ?? null;
$link_2 = $footer_bottom['footer_link_2'] ?? null;
$footer_copyright = $footer_bottom['footer_copyright'] ?? null;
$bg_footer_bottom_group = $footer_bottom['bg_footer_bottom_group'] ?? null;

// Company Info
$company_info = get_field('footer_company_info', 'option') ?? null;
$company_name = $company_info['company_name'] ?? '';
$company_email = $company_info['email'] ?? '';
$company_address = $company_info['address'] ?? '';
$company_city_zip = $company_info['city_zip'] ?? '';
?>

<footer class="footer" id="footer">
    <div class="container">
        <div class="footer__wrapper">

            <div class="footer__logo-social">

                <?php if (!empty($footer_logo['url'])):?>
                    <a class="footer__logo" href="/" target="_self">
                        <img src="<?=$footer_logo['url']?>" alt="<?=$footer_logo['title']?>">
                    </a>
                <?php endif;?>
            </div>

            <ul class="footer__content footer__menu">
                <?php wp_nav_menu([
                    'theme_location' => 'menu-footer',
                    'container'      => false,
                    'menu_class'     => 'footer__menu',
                    'items_wrap'     => '%3$s'
                ]); ?>

                <?php if (!empty($company_info)): ?>
                    <li class="footer__company-info">
                        <?php if(!empty($company_name)): ?>
                            <p class="footer__company-name"><?= wp_kses_post($company_name); ?></p>
                        <?php endif; ?>

                        <?php if(!empty($company_email)): ?>
                            <a class="company footer__company-email"
                               href="mailto:<?= esc_attr($company_email); ?>"
                            ><?= wp_kses_post($company_email); ?></a>
                        <?php endif; ?>

                        <?php if(!empty($company_address && $company_city_zip)): ?>
                            <p class="company footer__company-address">
                                <?= esc_html($company_address); ?><br>
                                <?= esc_html($company_city_zip); ?>
                            </p>
                        <?php endif; ?>
                    </li>
                <?php endif; ?>
            </ul>

            <?php if ($footer_social): ?>
                <li class="footer__social">
                    <?php foreach ($footer_social as $social): ?>
                        <a class="footer__social-link media-bounce"
                           href="<?= esc_url($social['url']); ?>"
                           target="_blank"
                           rel="noopener"
                        >
                            <img src="<?= esc_url($social['icon']); ?>" alt="Social Icon">
                        </a>
                    <?php endforeach; ?>
                </li>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer__bottom-group"
         style="background-image: url('<?= esc_url($bg_footer_bottom_group); ?>')"
    >
        <div class="container">
            <div class="footer__links-wrapper">

                <?php if (!empty($link_1['url'])):?>
                    <a class="footer__link"
                       href="<?= esc_url($link_1['url']); ?>"
                       target="<?= esc_attr($link_1['target']) ?: '_self'; ?>"
                    ><?= wp_kses_post($link_1['title']); ?></a>
                <?php endif;?>

                <?php if (!empty($link_2['url'])):?>
                    <a class="footer__link"
                       href="<?= esc_url($link_2['url']); ?>"
                       target="<?= esc_attr($link_2['target']) ?: '_self'; ?>"
                    ><?= wp_kses_post($link_2['title']); ?></a>
                <?php endif;?>

                <?php if (!empty($footer_copyright['url'])):?>
                    <a class="footer__link"
                       href="<?= esc_url($footer_copyright['url']); ?>"
                       target="<?= esc_attr($footer_copyright['target']) ?: '_self'; ?>"
                    >© <?= date('Y'); ?> <?= wp_kses_post($footer_copyright['title']); ?></a>
                <?php endif;?>

            </div>
        </div>
    </div>
</footer>


<?php //get_template_part('template-parts/popups/example-popup'); ?>

<?php wp_footer(); ?>

</body>
</html>

