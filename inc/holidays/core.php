<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The holidays the site knows about, in priority order (first active one wins
 * if two ever overlap). Each is defined by a calendar date; the decoration is
 * active during the Mon–Sun week that contains that date.
 *
 * Extend with the 'knot_holiday_definitions' filter to add more holidays.
 *
 * @return array<string,array{label:string,month:int,day:int,greeting:string}>
 */
function knot_holiday_definitions(): array {
	$defs = array(
		'new_year'  => array(
			'label'    => 'Новий рік',
			'month'    => 1,
			'day'      => 1,
			'greeting' => 'Вітаємо з Новим роком! ✨',
		),
		'march8'    => array(
			'label'    => '8 Березня',
			'month'    => 3,
			'day'      => 8,
			'greeting' => 'Зі святом весни! 🌷',
		),
		'halloween' => array(
			'label'    => 'Хелловін',
			'month'    => 10,
			'day'      => 31,
			'greeting' => 'Моторошно гарні прикраси 🎃',
		),
	);

	return apply_filters( 'knot_holiday_definitions', $defs );
}

/** Allowed per-holiday modes. */
function knot_holiday_modes(): array {
	return array(
		'auto' => 'Авто (за періодом)',
		'on'   => 'Завжди увімкнено',
		'off'  => 'Вимкнено',
	);
}

/** How long the decoration stays on in "auto" mode, centred on the holiday. */
function knot_holiday_periods(): array {
	return array(
		'1'     => array( 'label' => 'Тиждень', 'days' => 7 ),
		'2'     => array( 'label' => '2 тижні', 'days' => 14 ),
		'3'     => array( 'label' => '3 тижні', 'days' => 21 ),
		'month' => array( 'label' => 'Місяць',  'days' => 30 ),
	);
}

function knot_holiday_period_days( string $value ): int {
	$periods = knot_holiday_periods();

	return isset( $periods[ $value ] ) ? (int) $periods[ $value ]['days'] : 7;
}

/**
 * Saved settings merged over defaults, so every known holiday always has a
 * valid mode and banner, whatever is in the option.
 *
 * @return array<string,array{mode:string,banner:string}>
 */
function knot_holiday_get_settings(): array {
	$saved = get_option( KNOT_HOLIDAYS_OPTION, array() );
	$saved = is_array( $saved ) ? $saved : array();

	$out = array();
	foreach ( knot_holiday_definitions() as $slug => $def ) {
		$mode = $saved[ $slug ]['mode'] ?? 'auto';
		if ( ! isset( knot_holiday_modes()[ $mode ] ) ) {
			$mode = 'auto';
		}
		$period = $saved[ $slug ]['period'] ?? '1';
		if ( ! isset( knot_holiday_periods()[ $period ] ) ) {
			$period = '1';
		}
		$out[ $slug ] = array(
			'mode'   => $mode,
			'period' => $period,
			'banner' => (string) ( $saved[ $slug ]['banner'] ?? '' ),
		);
	}

	return $out;
}

/**
 * Monday–Sunday bounds of the week that contains month/day, for a given year.
 *
 * @return array{0:DateTimeImmutable,1:DateTimeImmutable}|null
 */
function knot_holiday_window_for_year( int $month, int $day, int $year, int $days ): ?array {
	$tz   = wp_timezone();
	$date = DateTimeImmutable::createFromFormat( '!Y-n-j', "{$year}-{$month}-{$day}", $tz );
	if ( ! $date ) {
		return null;
	}
	// Symmetric window: half the period before the holiday, half after — so
	// "Тиждень" splits evenly around the date, "2 тижні" = a week before and a
	// week after, and so on.
	$half  = intdiv( $days, 2 );
	$start = $date->modify( '-' . $half . ' days' );
	$end   = $date->modify( '+' . $half . ' days' );

	return array( $start, $end );
}

/**
 * Is today inside the holiday's week? Checks the occurrence in the previous,
 * current and next year, so New Year's week (which straddles Dec/Jan) and any
 * year boundary are handled the same cheap way.
 */
function knot_holiday_is_auto_active( int $month, int $day, int $days = 7 ): bool {
	$tz    = wp_timezone();
	$today = new DateTimeImmutable( 'today', $tz );
	$year  = (int) $today->format( 'Y' );

	foreach ( array( $year - 1, $year, $year + 1 ) as $y ) {
		$window = knot_holiday_window_for_year( $month, $day, $y, $days );
		if ( $window && $today >= $window[0] && $today <= $window[1] ) {
			return true;
		}
	}

	return false;
}

/** "27.10 – 02.11": the active week shown in admin (current occurrence). */
function knot_holiday_window_label( int $month, int $day, int $days = 7 ): string {
	$tz    = wp_timezone();
	$today = new DateTimeImmutable( 'today', $tz );
	$year  = (int) $today->format( 'Y' );

	// Prefer the occurrence whose window is happening now; else this year's.
	foreach ( array( $year - 1, $year, $year + 1 ) as $y ) {
		$window = knot_holiday_window_for_year( $month, $day, $y, $days );
		if ( $window && $today >= $window[0] && $today <= $window[1] ) {
			return $window[0]->format( 'd.m' ) . ' – ' . $window[1]->format( 'd.m' );
		}
	}

	$window = knot_holiday_window_for_year( $month, $day, $year, $days );

	return $window ? $window[0]->format( 'd.m' ) . ' – ' . $window[1]->format( 'd.m' ) : '';
}

/**
 * The single active holiday right now, or [] if none. Resolved once per request.
 *
 * @return array{slug:string,label:string,month:int,day:int,greeting:string,banner:string}|array{}
 */
function knot_active_holiday(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$settings = knot_holiday_get_settings();

	foreach ( knot_holiday_definitions() as $slug => $def ) {
		$mode = $settings[ $slug ]['mode'];
		if ( 'off' === $mode ) {
			continue;
		}

		$active = ( 'on' === $mode )
			|| ( 'auto' === $mode && knot_holiday_is_auto_active(
				$def['month'], $def['day'], knot_holiday_period_days( $settings[ $slug ]['period'] )
			) );

		if ( $active ) {
			$banner = $settings[ $slug ]['banner'];
			$cache  = array_merge( array( 'slug' => $slug ), $def, array(
				'banner' => '' !== trim( $banner ) ? $banner : $def['greeting'],
			) );

			return $cache;
		}
	}

	$cache = array();

	return $cache;
}

/* ------------------------------------------------------------- front-end ---- */

// Body class: 'has-holiday' + 'holiday-<slug>' so the (later) CSS can target it.
add_filter( 'body_class', static function ( array $classes ): array {
	$holiday = knot_active_holiday();
	if ( ! empty( $holiday['slug'] ) ) {
		$classes[] = 'has-holiday';
		$classes[] = 'holiday-' . $holiday['slug'];
	}

	return $classes;
} );

/**
 * Load the active holiday's assets — but only if they exist yet. This is what
 * lets logic/admin ship before the design: drop <slug>.css / <slug>.js into
 * inc/holidays/assets/ later and they appear automatically during the holiday.
 */
add_action( 'wp_enqueue_scripts', static function () {
	$holiday = knot_active_holiday();
	if ( empty( $holiday['slug'] ) ) {
		return;
	}

	$slug = $holiday['slug'];
	$dir  = KNOT_HOLIDAYS_DIR . '/assets/';
	$uri  = get_template_directory_uri() . '/inc/holidays/assets/';

	// Shared decoration for every holiday, then an optional per-holiday override.
	foreach ( array( 'holiday', $slug ) as $name ) {
		$css = $dir . $name . '.css';
		if ( file_exists( $css ) ) {
			wp_enqueue_style( 'knot-holiday-' . $name, $uri . $name . '.css', array(), filemtime( $css ) );
		}
	}

	$shared_js = $dir . 'holiday.js';
	if ( file_exists( $shared_js ) ) {
		wp_enqueue_script( 'knot-holiday', $uri . 'holiday.js', array(), filemtime( $shared_js ), true );
		wp_localize_script( 'knot-holiday', 'knotHoliday', array(
			'slug'   => $slug,
			'banner' => $holiday['banner'],
		) );
	}

	$slug_js = $dir . $slug . '.js';
	if ( file_exists( $slug_js ) ) {
		wp_enqueue_script( 'knot-holiday-' . $slug, $uri . $slug . '.js', array( 'knot-holiday' ), filemtime( $slug_js ), true );
	}
}, 20 );
