<?php
$block_anchor  = $block['anchor'] ?? '';
$block_classes = 'repair' . ( ! empty( $block['className'] ) ? ' ' . $block['className'] : '' );

$eyebrow      = get_field( 'eyebrow' );
$title        = get_field( 'title' );
$lead         = get_field( 'lead' );
$before_label = get_field( 'before_label' ) ?: 'До';
$after_label  = get_field( 'after_label' ) ?: 'Після';
$pairs        = get_field( 'comparisons' ) ?: array();
$cta_text     = get_field( 'cta_text' );
$cta_url      = get_field( 'cta_url' );

if ( empty( $pairs ) ) {
	return;
}

// Prime attachment caches once so no slide triggers its own image queries (N+1).
$img_ids = array();
foreach ( $pairs as $p ) {
	if ( ! empty( $p['before_image'] ) ) $img_ids[] = (int) $p['before_image'];
	if ( ! empty( $p['after_image'] ) )  $img_ids[] = (int) $p['after_image'];
}
$img_ids = array_filter( array_unique( $img_ids ) );
if ( $img_ids ) {
	_prime_post_caches( $img_ids, false, true );
}
?>

<section class="<?= esc_attr( $block_classes ); ?>"<?= $block_anchor ? ' id="' . esc_attr( $block_anchor ) . '"' : ''; ?>>
	<div class="container">
		<div class="repair__head">
			<?php if ( !empty($eyebrow) ) : ?>
				<div class="repair__eyebrow"><span class="repair__rule"></span><?= wp_kses_post( $eyebrow ); ?><span class="repair__rule"></div>
			<?php endif; ?>

			<?php if ( !empty($title) ) : ?>
				<h2 class="repair__title"><?= wp_kses_post( $title ); ?></h2>
			<?php endif; ?>

			<?php if ( !empty($lead) ) : ?>
				<p class="repair__lead"><?= wp_kses_post( $lead ); ?></p>
			<?php endif; ?>
		</div>

		<div class="repair__pairs">
			<?php foreach ( $pairs as $p ) :
				$before = (int) ( $p['before_image'] ?? 0 );
				$after  = (int) ( $p['after_image'] ?? 0 );
				if ( ! $before || ! $after ) {
					continue;
				}
				$bl     = $before_label;
				$al     = $after_label;
				$ititle = $p['title'] ?? '';
				$ilead  = $p['lead'] ?? '';
				?>
				<figure class="repair-item">
					<?php if ( !empty($ititle) ) : ?>
						<h3 class="repair-item__title"><?= wp_kses_post( $ititle ); ?></h3>
					<?php endif; ?>

					<div class="repair-cmp" data-repair-cmp>
						<div class="repair-cmp__after">
                            <span class="repair-cmp__tag repair-cmp__tag--r"><?= wp_kses_post( $al ); ?></span>
							<?= wp_get_attachment_image( $after, 'large', false, array(
								'class'    => 'repair-cmp__img',
								'alt'      => esc_attr( $al ),
								'loading'  => 'lazy',
								'decoding' => 'async',
							) ); ?>
						</div>

						<div class="repair-cmp__before" data-repair-before>
                            <span class="repair-cmp__tag repair-cmp__tag--l"><?= wp_kses_post( $bl ); ?></span>
							<div class="repair-cmp__inner" data-repair-inner>
								<?= wp_get_attachment_image( $before, 'large', false, array(
									'class'    => 'repair-cmp__img',
									'alt'      => esc_attr( $bl ),
									'loading'  => 'lazy',
									'decoding' => 'async',
								) ); ?>
							</div>
						</div>

						<div class="repair-cmp__handle" data-repair-handle>
							<span class="repair-cmp__grip">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<path d="M8 9l-3 3 3 3M16 9l3 3-3 3"/>
								</svg>
							</span>
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
					</div>

					<?php if ( $ilead ) : ?>
						<p class="repair-item__lead"><?= nl2br( esc_html( $ilead ) ); ?></p>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>

		<?php if (!empty( $cta_text )):?>
			<button type="button" class="repair__cta main-btn third" data-action="togglePopup" data-target="#repair_popup"><?= esc_html( $cta_text ); ?></button>
		<?php endif; ?>
	</div>

    <?php
    // Ask for the restoration popup — it's printed once in wp_footer (outside
    // <main>) so it stays clickable and survives multiple the_content passes.
    if ( ! empty( $cta_text ) ) {
        $GLOBALS['knot_repair_wants_popup'] = true;
    }
    ?>
</section>
