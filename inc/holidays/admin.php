<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const KNOT_HOLIDAYS_SAVE = 'knot_holidays_save';

add_action( 'admin_menu', static function () {
	add_menu_page(
		'Святкове оформлення',
		'Свята',
		'manage_options',
		'knot-holidays',
		'knot_holidays_render_page',
		'dashicons-buddicons-activity',
		26
	);
} );

function knot_holidays_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = knot_holiday_get_settings();
	$active   = knot_active_holiday();
	?>
	<div class="wrap">
		<h1>Святкове оформлення</h1>
		<p class="description" style="max-width:640px">
			Для кожного свята: <strong>Авто</strong> — оформлення вмикається саме на календарний
			тиждень (пн–нд), на який припадає дата, і потім вимикається.
			<strong>Завжди увімкнено</strong> — показувати зараз незалежно від дати (для розпродажу).
			<strong>Вимкнено</strong> — не показувати. Одночасно активним може бути лише одне свято.
		</p>

		<?php if ( ! empty( $active['slug'] ) ) : ?>
			<div class="notice notice-info inline" style="margin:16px 0">
				<p>Зараз активне: <strong><?php echo esc_html( $active['label'] ); ?></strong>
					— банер: «<?php echo esc_html( $active['banner'] ); ?>».</p>
			</div>
		<?php else : ?>
			<div class="notice notice-info inline" style="margin:16px 0">
				<p>Зараз активних свят немає.</p>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( KNOT_HOLIDAYS_SAVE ); ?>">
			<?php wp_nonce_field( KNOT_HOLIDAYS_SAVE ); ?>

			<table class="form-table" role="presentation">
				<?php foreach ( knot_holiday_definitions() as $slug => $def ) :
					$mode        = $settings[ $slug ]['mode'];
					$banner      = $settings[ $slug ]['banner'];
					$period      = $settings[ $slug ]['period'];
					$days        = knot_holiday_period_days( $period );
					$auto_active = knot_holiday_is_auto_active( $def['month'], $def['day'], $days );
					$window      = knot_holiday_window_label( $def['month'], $def['day'], $days );
					?>
					<tr>
						<th scope="row">
							<?php echo esc_html( $def['label'] ); ?><br>
							<span class="description" style="font-weight:400">діє <?php echo esc_html( $window ); ?></span>
						</th>
						<td>
							<p>
								<label>Режим
									<select name="holidays[<?php echo esc_attr( $slug ); ?>][mode]">
										<?php foreach ( knot_holiday_modes() as $value => $label ) :
											$suffix = ( 'auto' === $value && $auto_active ) ? ' — зараз діє' : '';
											?>
											<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $mode, $value ); ?>>
												<?php echo esc_html( $label . $suffix ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</label>
							</p>
							<p>
								<label>Період
									<select name="holidays[<?php echo esc_attr( $slug ); ?>][period]">
										<?php foreach ( knot_holiday_periods() as $value => $meta ) : ?>
											<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $period, $value ); ?>>
												<?php echo esc_html( $meta['label'] ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</label>
								<span class="description"> — на цей період навколо дати, порівну до та після.</span>
							</p>
							<p>
								<label style="display:block;margin-bottom:4px">Текст привітання (банер)</label>
								<input type="text" class="regular-text" maxlength="120"
									name="holidays[<?php echo esc_attr( $slug ); ?>][banner]"
									value="<?php echo esc_attr( $banner ); ?>"
									placeholder="<?php echo esc_attr( $def['greeting'] ); ?>">
							</p>
							<p class="description">Порожнє поле — буде використано типовий текст.</p>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>

			<?php submit_button( 'Зберегти' ); ?>
		</form>

		<p class="description">Візуальне оформлення (кольори, частинки, банер) додамо окремо — модуль уже готовий його підхопити у святковий період.</p>
	</div>
	<?php
}

add_action( 'admin_post_' . KNOT_HOLIDAYS_SAVE, static function () {
	check_admin_referer( KNOT_HOLIDAYS_SAVE );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Недостатньо прав.' );
	}

	$input = ( isset( $_POST['holidays'] ) && is_array( $_POST['holidays'] ) )
		? wp_unslash( $_POST['holidays'] )
		: array();

	$out = array();
	foreach ( knot_holiday_definitions() as $slug => $def ) {
		$mode = sanitize_key( $input[ $slug ]['mode'] ?? 'auto' );
		if ( ! isset( knot_holiday_modes()[ $mode ] ) ) {
			$mode = 'auto';
		}
		$period = sanitize_key( $input[ $slug ]['period'] ?? '1' );
		if ( ! isset( knot_holiday_periods()[ $period ] ) ) {
			$period = '1';
		}
		$banner = sanitize_text_field( $input[ $slug ]['banner'] ?? '' );

		$out[ $slug ] = array(
			'mode'   => $mode,
			'period' => $period,
			'banner' => mb_substr( $banner, 0, 120 ),
		);
	}

	update_option( KNOT_HOLIDAYS_OPTION, $out );

	wp_safe_redirect( add_query_arg(
		array( 'page' => 'knot-holidays', 'updated' => 1 ),
		admin_url( 'admin.php' )
	) );
	exit;
} );

add_action( 'admin_notices', static function () {
	if ( ( $_GET['page'] ?? '' ) === 'knot-holidays' && isset( $_GET['updated'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Налаштування збережено.</p></div>';
	}
} );
