<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Neutralise spreadsheet formula injection (=, +, -, @ at the start of a cell). */
function knot_lead_csv_cell( $value ): string {
	$value = (string) $value;

	return preg_match( '/^[=+\-@\t\r]/', $value ) ? "'" . $value : $value;
}

add_action( 'admin_post_knot_leads_export', static function () {
	check_admin_referer( 'knot_leads_export' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Недостатньо прав.' );
	}

	$args = array(
		'post_type'      => KNOT_LEAD_CPT,
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	$meta = knot_lead_meta_filters( $_GET );
	if ( $meta ) {
		$args['meta_query'] = $meta; // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="leads-' . gmdate( 'Y-m-d' ) . '.csv"' );

	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // BOM so Excel opens UTF-8 correctly.
	fputcsv( $out, array( 'ID', 'Дата', 'Тип', 'Статус', 'Ім\'я', 'Телефон', 'Telegram', 'Instagram', 'Склад', 'Сума', 'Коментар', 'Сторінка', 'Telegram-відправка', 'Нотатка' ) );

	foreach ( get_posts( $args ) as $id ) {
		$l = knot_lead_get( (int) $id );
		fputcsv( $out, array_map( 'knot_lead_csv_cell', array(
			$l['id'],
			$l['date'],
			knot_lead_types()[ $l['type'] ] ?? $l['type'],
			knot_lead_statuses()[ $l['status'] ] ?? $l['status'],
			$l['name'],
			$l['phone'],
			$l['telegram'],
			$l['instagram'],
			knot_lead_items_summary( $l ),
			$l['total'],
			$l['comment'],
			$l['page'],
			$l['tg_status'],
			$l['note'],
		) ) );
	}

	fclose( $out );
	exit;
} );
