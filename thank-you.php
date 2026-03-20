<?php
/* Template Name: Thank You Page */
get_header();

$thank_you = get_field('thank_you_page', 'option');

$main_title = $thank_you['main_title'] ?? '';
$title = $thank_you['title'] ?? 'Dziękujemy za Twoje zgłoszenie!';
$description = $thank_you['description'] ?? 'Otrzymaliśmy Twoje dane i wkrótce się z Tobą skontaktujemy.
Tymczasem możesz zobaczyć nasze aktualne oferty lub dowiedzieć się więcej o nieruchomościach.';
$link_url = $thank_you['link_url'] ?? home_url('/');
$link_text = $thank_you['link_text'] ?? 'Zobacz oferty';
$bg = $thank_you['background_image'] ?? [];
$bg_mob = $thank_you['background_image_mob'] ?? '';

?>

<main >
    <section class="thank-you">
        <style>
            .thank-you {
                position: relative;
                padding-top: clamp(100px,10.07vw,145px);
                padding-bottom: clamp(32px,3.612vw,52px);
                min-height: 661px;

                .thank-you__wrapper {
                    display: flex;
                    flex-direction: column;
                    align-items: flex-start;
                    max-width: clamp(443px,47.0833vw,678px);
                    padding: clamp(24px,2.78vw,40px);
                    border-radius: 16px;
                    margin: 0 auto;
                    
                    @media (max-width: 991px) {
                        & a {
                            width: 100%;
                            text-align: center;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                    }
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

                .thank-you__bg img {
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
        <div class="container">

            <div class="thank-you__wrapper main-blur">
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
                    <a href="<?= !empty($link_url) ? esc_url($link_url) : '/';?>" class="main-btn third">
                        <?= esc_html($link_text); ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($bg && !empty($bg['url'])): ?>
                <div class="thank-you__bg">
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
