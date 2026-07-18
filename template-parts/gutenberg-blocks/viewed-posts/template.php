<?php
$block_anchor  = $block['anchor'] ?? '';
$block_classes = 'in-stock viewed-posts';
if (!empty($block['className'])) {
	$block_classes .= ' ' . $block['className'];
}

$title           = get_field('viewed_title') ?: 'Ви переглядали';
$tap_text        = get_field('viewed_tap_text') ?: '';
$exclude_current = (bool) get_field('viewed_exclude_current');

// Product currently open — so it can be excluded from its own "viewed" row.
$current_id = ($exclude_current && is_singular('post')) ? get_the_ID() : 0;
?>

<section
	class="<?= esc_attr($block_classes) ?>"
	<?= $block_anchor ? 'id="' . esc_attr($block_anchor) . '"' : '' ?>
	data-viewed
	data-tap="<?= esc_attr($tap_text) ?>"
	data-exclude="<?= esc_attr($current_id) ?>"
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
