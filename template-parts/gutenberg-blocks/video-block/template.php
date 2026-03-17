<?php
$video = get_field('video');
$video_poster = get_field('video_poster');

$block_anchor = $block['anchor'] ?? '';
$block_classes = 'video-block';
if (!empty($block['className'])) {
	$block_classes .= ' ' . $block['className'];
}
?>

<section
	class="<?= esc_attr($block_classes) ?>"
	id="<?= esc_attr($block_anchor) ?>"
>
	<div class="container">
		<?php if (!empty($video)): ?>
			<div
				class="video_box"
				data-video-box
			>
				<video
					playsinline
					preload="none"
					poster="<?= $video_poster['url'] ?>"
				>
					<source
						src="<?= $video['url'] ?>"
						type="video/mp4"
					/>
				</video>

				<?php get_template_part('template-parts/components/video-controls'); ?>
			</div>
		<?php endif; ?>
	</div>
</section>