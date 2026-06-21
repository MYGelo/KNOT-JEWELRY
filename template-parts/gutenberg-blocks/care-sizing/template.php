<?php

$block_anchor  = $block['anchor'] ?? '';
$block_classes = 'care';
if (!empty($block['className'])) {
	$block_classes .= ' ' . $block['className'];
}

$hero_title    = get_field('hero_title');
$hero_subtitle = get_field('hero_subtitle');
$sections      = get_field('sections') ?: [];
$show_margin   = get_field('show_margin') ?? '';
?>

<section
	class="<?= esc_attr($block_classes); ?>"
	<?= $block_anchor ? 'id="' . esc_attr($block_anchor) . '"' : ''; ?>
>
	<div class="container">
		<?php if ($hero_title || $hero_subtitle): ?>
			<div class="care__hero">
				<?php if ($hero_title): ?>
					<h2 class="care__hero-title"><?= esc_html($hero_title); ?></h2>
				<?php endif; ?>
				<?php if ($hero_subtitle): ?>
					<p class="care__hero-subtitle"><?= wp_kses_post($hero_subtitle); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if (!empty($sections)): ?>
			<div class="care__sections <?= $show_margin ? 'is-gap-none' : ''?>">
				<?php foreach ($sections as $section):
					$number    = $section['number'] ?? '';
					$title     = $section['title'] ?? '';
					$image     = $section['image'] ?? null;
					$position  = $section['image_position'] ?? 'left';
					$intro     = $section['intro'] ?? '';
					$extra     = $section['extra'] ?? '';
					$tbl_on    = !empty($section['table_enabled']);
					$tbl_left  = $section['table_head_left'] ?? '';
					$tbl_right = $section['table_head_right'] ?? '';
					$tbl_rows  = $section['table_rows'] ?? [];


					// Fallback ring-size data so the table renders out of the box.
					if ($tbl_on && empty($tbl_rows)) {
						$tbl_rows = [
                            ['col_left' => '40.8 мм', 'col_right' => '13.0'],
                            ['col_left' => '42.4 мм', 'col_right' => '13.5'],
                            ['col_left' => '44.0 мм', 'col_right' => '14.0'],
                            ['col_left' => '45.5 мм', 'col_right' => '14.5'],
                            ['col_left' => '47.1 мм', 'col_right' => '15.0'],
                            ['col_left' => '48.7 мм', 'col_right' => '15.5'],
                            ['col_left' => '50.2 мм', 'col_right' => '16.0'],
                            ['col_left' => '51.8 мм', 'col_right' => '16.5'],
                            ['col_left' => '53.4 мм', 'col_right' => '17.0'],
                            ['col_left' => '55.0 мм', 'col_right' => '17.5'],
                            ['col_left' => '56.5 мм', 'col_right' => '18.0'],
                            ['col_left' => '58.1 мм', 'col_right' => '18.5'],
                            ['col_left' => '59.7 мм', 'col_right' => '19.0'],
                            ['col_left' => '61.2 мм', 'col_right' => '19.5'],
                            ['col_left' => '62.8 мм', 'col_right' => '20.0'],
						];
					}
					?>
					<article class="care-section care-section--img-<?= esc_attr($position); ?>">

						<div class="care-section__media">
							<?php if ($image && !empty($image['url'])): ?>
								<img
									src="<?= esc_url($image['sizes']['large'] ?? $image['url']); ?>"
									alt="<?= esc_attr($image['alt'] ?: $title); ?>"
									loading="lazy"
								>
							<?php endif; ?>
						</div>

						<div class="care-section__body">
							<?php if ($number !== ''): ?>
								<span class="care-section__number"><?= esc_html($number); ?></span>
							<?php endif; ?>

							<?php if ($title): ?>
								<h2 class="care-section__title"><?= esc_html($title); ?></h2>
							<?php endif; ?>

							<?php if ($intro): ?>
								<div class="care-section__rte"><?= wp_kses_post($intro); ?></div>
							<?php endif; ?>

							<?php if ($tbl_on && !empty($tbl_rows)): ?>
								<table class="care-table">
									<?php if ($tbl_left || $tbl_right): ?>
										<thead>
											<tr>
												<th><?= esc_html($tbl_left); ?></th>
												<th><?= esc_html($tbl_right); ?></th>
											</tr>
										</thead>
									<?php endif; ?>
									<tbody>
										<?php foreach ($tbl_rows as $row): ?>
											<tr>
												<td><?= esc_html($row['col_left'] ?? ''); ?></td>
												<td><?= esc_html($row['col_right'] ?? ''); ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							<?php endif; ?>

							<?php if ($extra): ?>
								<div class="care-section__rte"><?= wp_kses_post($extra); ?></div>
							<?php endif; ?>
						</div>

					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
