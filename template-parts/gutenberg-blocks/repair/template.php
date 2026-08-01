<?php
$block_anchor  = $block['anchor'] ?? '';
$block_classes = 'repair' . ( ! empty( $block['className'] ) ? ' ' . $block['className'] : '' );

$eyebrow  = get_field( 'eyebrow' );
$title    = get_field( 'title' );
$lead     = get_field( 'lead' );
$pairs    = get_field( 'comparisons' ) ?: array();
$steps    = get_field( 'steps' ) ?: array();
$cta_text = get_field( 'cta_text' );
$cta_url  = get_field( 'cta_url' );

if ( empty( $pairs ) ) {
	return;
}

// Prime attachment caches once so no slide/step triggers its own image queries (N+1).
$img_ids = array();
foreach ( $pairs as $p ) {
	if ( ! empty( $p['before_image'] ) ) $img_ids[] = (int) $p['before_image'];
	if ( ! empty( $p['after_image'] ) )  $img_ids[] = (int) $p['after_image'];
}
foreach ( $steps as $s ) {
	if ( ! empty( $s['image'] ) ) $img_ids[] = (int) $s['image'];
}
$img_ids = array_filter( array_unique( $img_ids ) );
if ( $img_ids ) {
	_prime_post_caches( $img_ids, false, true );
}
?>

<section class="<?= esc_attr( $block_classes ); ?>"<?= $block_anchor ? ' id="' . esc_attr( $block_anchor ) . '"' : ''; ?>>
	<div class="container">

		<div class="repair__head">
			<?php if ( $eyebrow ) : ?>
				<div class="repair__eyebrow"><span class="repair__rule"></span><?= esc_html( $eyebrow ); ?></div>
			<?php endif; ?>
			<?php if ( $title ) : ?>
				<h2 class="repair__title"><?= esc_html( $title ); ?></h2>
			<?php endif; ?>
			<?php if ( $lead ) : ?>
				<p class="repair__lead"><?= esc_html( $lead ); ?></p>
			<?php endif; ?>
		</div>

		<div class="repair__pairs">
			<?php foreach ( $pairs as $p ) :
				$before = (int) ( $p['before_image'] ?? 0 );
				$after  = (int) ( $p['after_image'] ?? 0 );
				if ( ! $before || ! $after ) {
					continue;
				}
				$bl  = $p['before_label'] ?: 'До';
				$al  = $p['after_label'] ?: 'Після';
				?>
				<figure class="repair-item">
					<div class="repair-cmp" data-repair-cmp>

						<div class="repair-cmp__after">
							<?= wp_get_attachment_image( $after, 'large', false, array(
								'class'    => 'repair-cmp__img',
								'alt'      => esc_attr( $al ),
								'loading'  => 'lazy',
								'decoding' => 'async',
							) ); ?>
						</div>

						<div class="repair-cmp__before" data-repair-before>
							<div class="repair-cmp__inner" data-repair-inner>
								<?= wp_get_attachment_image( $before, 'large', false, array(
									'class'    => 'repair-cmp__img',
									'alt'      => esc_attr( $bl ),
									'loading'  => 'lazy',
									'decoding' => 'async',
								) ); ?>
							</div>
						</div>

						<span class="repair-cmp__tag repair-cmp__tag--l"><?= esc_html( $bl ); ?></span>
						<span class="repair-cmp__tag repair-cmp__tag--r"><?= esc_html( $al ); ?></span>

						<div class="repair-cmp__handle" data-repair-handle>
							<span class="repair-cmp__grip">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<path d="M8 9l-3 3 3 3M16 9l3 3-3 3"/>
								</svg>
							</span>
						</div>

					</div>
					<p class="repair-item__hint">
						<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M8 13V4.5a1.5 1.5 0 0 1 3 0V12"/>
							<path d="M11 11.5v-2a1.5 1.5 0 0 1 3 0V12"/>
							<path d="M14 10.5a1.5 1.5 0 0 1 3 0V12"/>
							<path d="M17 11.5a1.5 1.5 0 0 1 3 0V16a6 6 0 0 1-6 6h-2a6 6 0 0 1-5-2.7c-.13-.2-.2-.3-.2-.3-.31-.48-1.4-2.39-3.28-5.73a1.5 1.5 0 0 1 .53-2.02 1.87 1.87 0 0 1 2.28.28L8 13"/>
							<path d="M4.5 15.5l-2 1.5M5 19l-2.4.6" opacity="0.55"/>
						</svg>
						Потягніть, щоб порівняти
					</p>
				</figure>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $steps ) ) : ?>
			<div class="repair__steps-h">Етапи роботи</div>
			<div class="repair__steps">
				<?php foreach ( $steps as $i => $s ) :
					$sid = (int) ( $s['image'] ?? 0 );
					$st  = $s['title'] ?? '';
					if ( ! $sid ) {
						continue;
					}
					$full = wp_get_attachment_image_url( $sid, 'large' );
					?>
					<button type="button" class="repair-step" data-repair-step data-full="<?= esc_url( $full ); ?>" data-title="<?= esc_attr( $st ); ?>">
						<span class="repair-step__media">
							<?= wp_get_attachment_image( $sid, 'medium', false, array(
								'class'    => 'repair-step__img',
								'alt'      => esc_attr( $st ),
								'loading'  => 'lazy',
								'decoding' => 'async',
							) ); ?>
							<span class="repair-step__zoom" aria-hidden="true">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
							</span>
						</span>
						<span class="repair-step__t"><span class="repair-step__n"><?= esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><?= esc_html( $st ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<div class="repair-modal" data-repair-modal role="dialog" aria-modal="true" aria-label="Перегляд етапу" hidden>
				<div class="repair-modal__backdrop" data-repair-close></div>
				<button type="button" class="repair-modal__close" data-repair-close aria-label="Закрити">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
				</button>
				<button type="button" class="repair-modal__nav repair-modal__nav--prev" data-repair-prev aria-label="Попередній">
					<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
				</button>
				<figure class="repair-modal__figure">
					<img class="repair-modal__img" data-repair-modal-img src="" alt="">
					<figcaption class="repair-modal__cap" data-repair-modal-cap></figcaption>
				</figure>
				<button type="button" class="repair-modal__nav repair-modal__nav--next" data-repair-next aria-label="Наступний">
					<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
				</button>
			</div>
		<?php endif; ?>

		<?php if ( $cta_url && $cta_text ) : ?>
			<a class="repair__cta main-btn third" href="<?= esc_url( $cta_url ); ?>"><?= esc_html( $cta_text ); ?></a>
		<?php endif; ?>

	</div>
</section>
