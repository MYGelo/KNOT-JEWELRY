<?php
$block_anchor = $block['anchor'] ?? '';
$block_classes = 'author';
if (!empty($block['className'])) {
	$block_classes .= ' ' . $block['className'];
}

$data = array(
    'photo' => get_field('photo'),
    'title' => get_field('title'),
    'position' => get_field('position'),
    'short_bio' => get_field('short_bio'),
    'socials' => get_field('social_repeater','option'),
    'p_articles' => get_field('p_articles'),
);
?>

<section
    class="<?= esc_attr($block_classes); ?>"
    <?= $block_anchor ? 'id="' . esc_attr($block_anchor) . '"' : ''; ?>
>
    <div class="container">
        <div class="author__wrapper">
            <div class="author__photo">
                <?php if (!empty($data['photo']['url'])):?>
                    <picture>
                        <!-- Mobile --> <source srcset="<?= $data['photo']['sizes']['medium_large']; ?>" media="(max-width: 551px)">
                        <!-- Desktop --><source srcset="<?= $data['photo']['url']; ?>" media="(min-width: 552px)">
                        <img
                                class=""
                                src="<?= esc_url($data['photo']['sizes']['large'] ?: $data['photo']['sizes']['medium_large']); ?>"
                                alt="<?= esc_attr($data['photo']['alt'] ?: $data['photo']['title']); ?>"
                                width="<?= esc_attr($data['photo']['width'] ?? ''); ?>"
                                height="<?= esc_attr($data['photo']['height'] ?? ''); ?>"
                                loading="lazy"
                                decoding="async"
                        >
                    </picture>
                <?php endif; ?>
            </div>

            <div class="author__info">

                <div class="author__text-content">
                    <?php if(!empty($data['title'])): ?>
                        <h2><?= esc_html($data['title']); ?></h2>
                    <?php endif; ?>

                    <?php if ($data['position']): ?>
                        <p class="author__position"><?= esc_html($data['position']); ?></p>
                    <?php endif; ?>

                    <?php if ($data['short_bio']): ?>
                        <div class="author__bio"><?= wp_kses_post($data['short_bio']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="auhtor__socials-wrapper">
                    <div class="author__socials">
                        <?php foreach ($data['socials'] as $social):
                            $socials_logo = $social['icon'];
                            $socials_url = $social['url'];

                            if (!empty($socials_url)): ?>
                                <a class="author__social header__social-link media-bounce"
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
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>