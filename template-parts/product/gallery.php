<?php
$gallery = $args['gallery'] ?? [];
$thumb   = $args['thumb_id'] ?? null;

$gallery = array_filter($gallery, fn($img) => !empty($img));
$has_gallery = $thumb || !empty($gallery);

$slide_index = 0;
?>

<?php if ($has_gallery): ?>
    <div class="product-gallery">

        <!-- Main Gallery -->
        <div class="swiper gallery-main">
            <div class="swiper-wrapper">

                <?php if ($thumb): ?>
                    <div class="swiper-slide">
                        <div class="product-gallery__img-wrapper">
                            <?= wp_get_attachment_image(
                                $thumb,
                                'large',
                                false,
                                [
                                    'fetchpriority' => 'high',
                                    'loading' => 'eager',
                                    'decoding' => 'async'
                                ]
                            ); ?>
                        </div>
                    </div>
                    <?php $slide_index++; endif; ?>

                <?php foreach ($gallery as $img): ?>
                    <div class="swiper-slide">
                        <div class="product-gallery__img-wrapper">
                            <?php
                            $is_first = $slide_index === 0;
                            echo wp_get_attachment_image(
                                $img,
                                'large',
                                false,
                                $is_first
                                    ? ['fetchpriority'=>'high','loading'=>'eager','decoding'=>'async']
                                    : ['loading'=>'lazy','decoding'=>'async']
                            );
                            ?>
                        </div>
                    </div>
                    <?php $slide_index++; endforeach; ?>

            </div>

            <?php if (($thumb ? 1 : 0) + count($gallery) > 1): ?>
                <div class="swiper-button-prev">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"><path d="m12.59 27.41-10-10a2 2 0 0 1 0-2.82l10-10A2 2 0 1 1 15.4 7.4L8.83 14H28a2 2 0 1 1 0 4H8.83l6.58 6.59a2 2 0 0 1 0 2.82 2 2 0 0 1-2.82 0"/></svg>
                </div>
                <div class="swiper-button-next">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"><path d="m19.41 27.41 10-10a2 2 0 0 0 0-2.82l-10-10A2 2 0 1 0 16.6 7.4L23.17 14H4a2 2 0 1 0 0 4h19.17l-6.58 6.59a2 2 0 0 0 0 2.82 2 2 0 0 0 2.82 0"/></svg>
                </div>
            <?php endif; ?>
        </div>

        <!-- Thumbnails -->
        <?php if (!empty($gallery) && $thumb): ?>
            <div class="swiper gallery-thumbs">
                <div class="swiper-wrapper">

                    <?php if ($thumb): ?>
                        <div class="swiper-slide">
                            <div class="product-gallery__img-wrapper">
                                <?= wp_get_attachment_image($thumb, 'thumbnail', false, ['loading'=>'lazy']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($gallery as $img): ?>
                        <div class="swiper-slide">
                            <div class="product-gallery__img-wrapper">
                                <?= wp_get_attachment_image($img, 'thumbnail', false, ['loading'=>'lazy']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
                <div class="swiper-pagination"></div>
            </div>
        <?php endif; ?>

    </div>
<?php endif; ?>