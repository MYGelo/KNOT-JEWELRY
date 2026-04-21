<?php
$text = get_field('text');

$block_anchor = $block['anchor'] ?? '';
$block_classes = 'example-block';
if (!empty($block['className'])) {
	$block_classes .= ' ' . $block['className'];
}
?>

<section
	class="<?= esc_attr($block_classes) ?>"
	id="<?= esc_attr($block_anchor) ?>"
>
	<div class="container">

	</div>
</section>