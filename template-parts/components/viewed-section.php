<?php
/**
 * Recently-viewed section shell (populated client-side by viewed.js).
 * Shared by the viewed-posts block and the single-product auto output.
 *
 * @var array $args ['title','tap','exclude','extra','anchor']
 */
$title   = $args['title']   ?? 'Ви переглядали';
$tap     = $args['tap']     ?? '';
$exclude = (int) ($args['exclude'] ?? 0);
$extra   = $args['extra']   ?? '';
$anchor  = $args['anchor']  ?? '';
?>

<section
	class="in-stock viewed-posts <?= esc_attr($extra) ?>"
	<?= $anchor ? 'id="' . esc_attr($anchor) . '"' : '' ?>
	data-viewed
	data-tap="<?= esc_attr($tap) ?>"
	data-exclude="<?= esc_attr($exclude) ?>"
	hidden
>
	<div class="container">
		<div class="stock__wrapper">

			<?php if ($title): ?>
				<h2 data-viewed-title><?= esc_html($title) ?></h2>
			<?php endif; ?>

			<div class="swiper in-stock-slider viewed-slider">
				<div class="swiper-wrapper" data-viewed-list></div>
			</div>

		</div>
	</div>
</section>
