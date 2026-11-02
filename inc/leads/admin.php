<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ------------------------------------------------------------------ helpers */

function knot_lead_is_list_screen(): bool {
	global $pagenow;

	return 'edit.php' === $pagenow && ( $_GET['post_type'] ?? '' ) === KNOT_LEAD_CPT;
}

/** meta_query clauses from ?lead_status= / ?lead_type= / ?lead_tg= / ?s= */
function knot_lead_meta_filters( array $src ): array {
	$clauses = array();

	$status = sanitize_key( $src['lead_status'] ?? '' );
	if ( isset( knot_lead_statuses()[ $status ] ) ) {
		$clauses[] = 'new' === $status
			? array(
				'relation' => 'OR',
				array( 'key' => '_lead_status', 'value' => 'new' ),
				array( 'key' => '_lead_status', 'compare' => 'NOT EXISTS' ),
			)
			: array( 'key' => '_lead_status', 'value' => $status );
	}

	$type = sanitize_key( $src['lead_type'] ?? '' );
	if ( isset( knot_lead_types()[ $type ] ) ) {
		$clauses[] = array( 'key' => '_lead_type', 'value' => $type );
	}

	$tg = sanitize_key( $src['lead_tg'] ?? '' );
	if ( in_array( $tg, array( 'sent', 'client', 'error', 'pending' ), true ) ) {
		$clauses[] = array( 'key' => '_lead_tg_status', 'value' => $tg );
	}

	$search = trim( wp_unslash( $src['s'] ?? '' ) );
	if ( '' !== $search ) {
		$clauses[] = array(
			'relation' => 'OR',
			array( 'key' => '_lead_name', 'value' => $search, 'compare' => 'LIKE' ),
			array( 'key' => '_lead_phone', 'value' => $search, 'compare' => 'LIKE' ),
			array( 'key' => '_lead_telegram', 'value' => $search, 'compare' => 'LIKE' ),
			array( 'key' => '_lead_comment', 'value' => $search, 'compare' => 'LIKE' ),
		);
	}

	return $clauses ? array_merge( array( 'relation' => 'AND' ), $clauses ) : array();
}

function knot_lead_count_by_status(): array {
	global $wpdb;

	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT COALESCE(pm.meta_value, 'new') AS s, COUNT(*) AS c
		 FROM {$wpdb->posts} p
		 LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_lead_status'
		 WHERE p.post_type = %s AND p.post_status = 'publish'
		 GROUP BY s",
		KNOT_LEAD_CPT
	) );

	$out = array();
	foreach ( (array) $rows as $row ) {
		$out[ $row->s ] = (int) $row->c;
	}

	return $out;
}

function knot_lead_count_tg_errors(): int {
	global $wpdb;

	return (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_lead_tg_status' AND pm.meta_value = 'error'
		 WHERE p.post_type = %s AND p.post_status = 'publish'",
		KNOT_LEAD_CPT
	) );
}

function knot_lead_badge( string $label, string $kind, string $title = '' ): string {
	return sprintf(
		'<span class="knot-badge knot-badge--%s"%s>%s</span>',
		esc_attr( $kind ),
		$title ? ' title="' . esc_attr( $title ) . '"' : '',
		esc_html( $label )
	);
}

function knot_lead_status_badge( string $status ): string {
	$kinds = array( 'new' => 'blue', 'work' => 'amber', 'done' => 'green', 'cancel' => 'gray' );

	return knot_lead_badge( knot_lead_statuses()[ $status ] ?? $status, $kinds[ $status ] ?? 'gray' );
}

function knot_lead_tg_badge( array $lead ): string {
	switch ( $lead['tg_status'] ) {
		case 'sent':
			return knot_lead_badge( '✓ Надіслано', 'green' );
		case 'client':
			return knot_lead_badge( '✓ Браузер', 'green', 'Доставку в Telegram виконує браузер відвідувача' );
		case 'error':
			return knot_lead_badge( '! Помилка', 'red', $lead['tg_error'] );
		default:
			return knot_lead_badge( 'Очікує', 'gray' );
	}
}

/* ----------------------------------------------------------- list: columns */

add_filter( 'manage_' . KNOT_LEAD_CPT . '_posts_columns', static function () {
	return array(
		'cb'        => '<input type="checkbox" />',
		'lead_id'   => '№',
		'lead_who'  => 'Клієнт',
		'lead_type' => 'Тип',
		'lead_what' => 'Склад',
		'lead_stat' => 'Статус',
		'lead_tg'   => 'Telegram',
		'lead_date' => 'Дата',
	);
} );

add_filter( 'list_table_primary_column', static function ( $column, $screen ) {
	return 'edit-' . KNOT_LEAD_CPT === $screen ? 'lead_id' : $column;
}, 10, 2 );

add_action( 'manage_' . KNOT_LEAD_CPT . '_posts_custom_column', static function ( $column, $id ) {
	$lead = knot_lead_get( (int) $id );

	switch ( $column ) {
		case 'lead_id':
			printf( '<strong><a href="%s">#%d</a></strong>', esc_url( get_edit_post_link( $id ) ), (int) $id );
			break;
		case 'lead_who':
			echo '<strong>' . esc_html( $lead['name'] ) . '</strong><br>' . esc_html( $lead['phone'] );
			if ( $lead['telegram'] ) {
				echo '<br><span class="description">' . esc_html( $lead['telegram'] ) . '</span>';
			}
			break;
		case 'lead_type':
			echo esc_html( knot_lead_types()[ $lead['type'] ] ?? $lead['type'] );
			break;
		case 'lead_what':
			echo esc_html( mb_strimwidth( knot_lead_items_summary( $lead ), 0, 110, '…' ) );
			if ( $lead['total'] ) {
				echo '<br><span class="description">' . esc_html( $lead['total'] ) . '</span>';
			}
			break;
		case 'lead_stat':
			echo knot_lead_status_badge( $lead['status'] ); // phpcs:ignore WordPress.Security.EscapeOutput
			break;
		case 'lead_tg':
			echo knot_lead_tg_badge( $lead ); // phpcs:ignore WordPress.Security.EscapeOutput
			break;
		case 'lead_date':
			echo esc_html( $lead['date'] );
			break;
	}
}, 10, 2 );

add_filter( 'post_row_actions', static function ( $actions, $post ) {
	if ( KNOT_LEAD_CPT !== $post->post_type ) {
		return $actions;
	}
	unset( $actions['inline hide-if-no-js'], $actions['view'] );

	return $actions;
}, 10, 2 );

add_filter( 'months_dropdown_results', static function ( $months, $post_type ) {
	return KNOT_LEAD_CPT === $post_type ? array() : $months;
}, 10, 2 );

/* ---------------------------------------------------- list: views, filters */

add_filter( 'views_edit-' . KNOT_LEAD_CPT, static function ( $views ) {
	$counts = knot_lead_count_by_status();
	$errors = knot_lead_count_tg_errors();
	$base   = admin_url( 'edit.php?post_type=' . KNOT_LEAD_CPT );
	$cur    = sanitize_key( $_GET['lead_status'] ?? '' );
	$cur_tg = sanitize_key( $_GET['lead_tg'] ?? '' );

	$link = static function ( string $label, string $url, int $n, bool $active ): string {
		return sprintf( '<a href="%s"%s>%s <span class="count">(%d)</span></a>', esc_url( $url ), $active ? ' class="current"' : '', esc_html( $label ), $n );
	};

	$out = array( 'all' => $link( 'Усі', $base, array_sum( $counts ), '' === $cur && '' === $cur_tg ) );
	foreach ( knot_lead_statuses() as $key => $label ) {
		$out[ 'lead_' . $key ] = $link( $label, add_query_arg( 'lead_status', $key, $base ), $counts[ $key ] ?? 0, $cur === $key );
	}
	if ( $errors ) {
		$out['lead_tg_error'] = $link( 'Помилка Telegram', add_query_arg( 'lead_tg', 'error', $base ), $errors, 'error' === $cur_tg );
	}
	if ( isset( $views['trash'] ) ) {
		$out['trash'] = $views['trash'];
	}

	return $out;
} );

add_action( 'restrict_manage_posts', static function ( $post_type ) {
	if ( KNOT_LEAD_CPT !== $post_type ) {
		return;
	}
	$type = sanitize_key( $_GET['lead_type'] ?? '' );
	$tg   = sanitize_key( $_GET['lead_tg'] ?? '' );

	echo '<select name="lead_type"><option value="">Усі типи</option>';
	foreach ( knot_lead_types() as $key => $label ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $type, $key, false ), esc_html( $label ) );
	}
	echo '</select><select name="lead_tg"><option value="">Telegram: усі</option>';
	foreach ( array( 'sent' => 'Надіслано', 'client' => 'Браузер', 'error' => 'Помилка', 'pending' => 'Очікує' ) as $key => $label ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $tg, $key, false ), esc_html( $label ) );
	}
	echo '</select>';
} );

add_action( 'pre_get_posts', static function ( WP_Query $q ) {
	if ( ! is_admin() || ! $q->is_main_query() || ! knot_lead_is_list_screen() ) {
		return;
	}
	$meta = knot_lead_meta_filters( $_GET );
	if ( $meta ) {
		$q->set( 'meta_query', $meta );
	}
	// Search is done over the lead's meta fields above, not the (generated) title.
	$q->set( 's', '' );
} );

add_action( 'manage_posts_extra_tablenav', static function ( $which ) {
	if ( 'top' !== $which || ! knot_lead_is_list_screen() ) {
		return;
	}
	$args = array_filter( array(
		'action'      => 'knot_leads_export',
		'lead_status' => sanitize_key( $_GET['lead_status'] ?? '' ),
		'lead_type'   => sanitize_key( $_GET['lead_type'] ?? '' ),
		'lead_tg'     => sanitize_key( $_GET['lead_tg'] ?? '' ),
		's'           => trim( wp_unslash( $_GET['s'] ?? '' ) ),
	) );
	$url  = wp_nonce_url( add_query_arg( $args, admin_url( 'admin-post.php' ) ), 'knot_leads_export' );

	printf( '<div class="alignleft actions"><a class="button" href="%s">Експорт CSV</a></div>', esc_url( $url ) );
} );

/* --------------------------------------------------------------- bulk actions */

add_filter( 'bulk_actions-edit-' . KNOT_LEAD_CPT, static function ( $actions ) {
	$new = array();
	foreach ( knot_lead_statuses() as $key => $label ) {
		$new[ 'lead_set_' . $key ] = 'Статус: ' . $label;
	}
	$new['lead_resend'] = 'Надіслати в Telegram повторно';

	unset( $actions['edit'] );

	return $new + $actions;
} );

add_filter( 'handle_bulk_actions-edit-' . KNOT_LEAD_CPT, static function ( $redirect, $action, $ids ) {
	if ( 0 === strpos( $action, 'lead_set_' ) ) {
		$status = substr( $action, 9 );
		if ( isset( knot_lead_statuses()[ $status ] ) ) {
			foreach ( $ids as $id ) {
				update_post_meta( (int) $id, '_lead_status', $status );
			}

			return add_query_arg( 'lead_bulk', count( $ids ), $redirect );
		}
	}

	if ( 'lead_resend' === $action ) {
		$ok = 0;
		foreach ( $ids as $id ) {
			update_post_meta( (int) $id, '_lead_tg_tries', 0 );
			$ok += knot_lead_send_telegram( (int) $id ) ? 1 : 0;
		}

		return add_query_arg( array( 'lead_resent' => $ok, 'lead_resent_total' => count( $ids ) ), $redirect );
	}

	return $redirect;
}, 10, 3 );

add_action( 'admin_notices', static function () {
	if ( ! knot_lead_is_list_screen() && ( $GLOBALS['pagenow'] ?? '' ) !== 'post.php' ) {
		return;
	}
	if ( isset( $_GET['lead_bulk'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Статус змінено: ' . (int) $_GET['lead_bulk'] . '.</p></div>';
	}
	if ( isset( $_GET['lead_resent'] ) ) {
		$ok    = (int) $_GET['lead_resent'];
		$total = (int) ( $_GET['lead_resent_total'] ?? 1 );
		printf(
			'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
			$ok === $total ? 'success' : 'error',
			esc_html( sprintf( 'Telegram: надіслано %d з %d.', $ok, $total ) )
		);
	}
} );

/* ---------------------------------------------------------------- menu bubble */

add_action( 'admin_menu', static function () {
	global $menu;

	$new = knot_lead_count_by_status()['new'] ?? 0;
	if ( ! $new || ! is_array( $menu ) ) {
		return;
	}
	foreach ( $menu as $i => $item ) {
		if ( ( $item[2] ?? '' ) === 'edit.php?post_type=' . KNOT_LEAD_CPT ) {
			$menu[ $i ][0] .= ' <span class="awaiting-mod">' . (int) $new . '</span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			break;
		}
	}
}, 99 );

/* -------------------------------------------------------------------- styles */

add_action( 'admin_head', static function () {
	$screen = get_current_screen();
	if ( ! $screen || KNOT_LEAD_CPT !== $screen->post_type ) {
		return;
	}
	?>
	<style>
		.knot-badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:12px;line-height:1.5;white-space:nowrap}
		.knot-badge--blue{background:#e6f1fb;color:#0c447c}
		.knot-badge--amber{background:#faeeda;color:#633806}
		.knot-badge--green{background:#eaf3de;color:#27500a}
		.knot-badge--red{background:#fcebeb;color:#791f1f}
		.knot-badge--gray{background:#f1efe8;color:#444441}
		.column-lead_id{width:70px}.column-lead_type,.column-lead_stat,.column-lead_tg{width:120px}.column-lead_date{width:130px}
		.knot-lead-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px 24px}
		.knot-lead-grid dt{color:#646970;font-size:12px;margin:0}.knot-lead-grid dd{margin:0 0 6px}
		.knot-lead-items{width:100%;border-collapse:collapse}.knot-lead-items th,.knot-lead-items td{text-align:left;padding:6px 8px;border-bottom:1px solid #dcdcde}
		#poststuff .knot-lead-box .inside{margin:0;padding:12px}
		.knot-lead-box textarea,.knot-lead-box select{width:100%}
	</style>
	<?php
} );

/* ------------------------------------------------------------------ edit page */

add_action( 'add_meta_boxes_' . KNOT_LEAD_CPT, static function () {
	remove_meta_box( 'submitdiv', KNOT_LEAD_CPT, 'side' );

	add_meta_box( 'knot_lead_main', 'Заявка', 'knot_lead_box_main', KNOT_LEAD_CPT, 'normal', 'high' );
	add_meta_box( 'knot_lead_status', 'Статус', 'knot_lead_box_status', KNOT_LEAD_CPT, 'side', 'high' );
	add_meta_box( 'knot_lead_tg', 'Telegram', 'knot_lead_box_tg', KNOT_LEAD_CPT, 'side', 'default' );
} );

function knot_lead_box_main( WP_Post $post ): void {
	$l = knot_lead_get( $post->ID );

	echo '<dl class="knot-lead-grid">';
	$fields = array(
		'Дата'      => $l['date'],
		'Тип'       => knot_lead_types()[ $l['type'] ] ?? $l['type'],
		'Ім\'я'     => $l['name'],
		'Телефон'   => $l['phone'],
		'Telegram'  => $l['telegram'] ?: '-',
		'Instagram' => $l['instagram'] ?: '-',
	);
	foreach ( $fields as $label => $value ) {
		printf( '<div><dt>%s</dt><dd>%s</dd></div>', esc_html( $label ), esc_html( $value ) );
	}
	echo '</dl>';

	if ( $l['items'] ) {
		echo '<h4>Склад замовлення</h4><table class="knot-lead-items"><thead><tr><th>Товар</th><th>Розмір</th><th>Покриття</th><th>Кільк.</th><th>Ціна</th></tr></thead><tbody>';
		foreach ( $l['items'] as $item ) {
			$coating = ( $item['coating'] ?? '' ) === 'Тип покриття' ? '' : ( $item['coating'] ?? '' );
			printf(
				'<tr><td>%s</td><td>%s</td><td>%s</td><td>%d</td><td>%s</td></tr>',
				! empty( $item['link'] )
					? '<a href="' . esc_url( $item['link'] ) . '" target="_blank" rel="noopener">' . esc_html( $item['title'] ) . '</a>'
					: esc_html( $item['title'] ?? '' ),
				esc_html( $item['size'] ?? '' ),
				esc_html( $coating ),
				(int) ( $item['qty'] ?? 1 ),
				esc_html( $item['price'] ?? '' )
			);
		}
		echo '</tbody></table>';
		if ( $l['total'] ) {
			echo '<p><strong>Разом: ' . esc_html( $l['total'] ) . '</strong></p>';
		}
	}

	if ( $l['comment'] ) {
		echo '<h4>Коментар</h4><p>' . nl2br( esc_html( $l['comment'] ) ) . '</p>';
	}
	if ( $l['page'] ) {
		echo '<h4>Сторінка</h4><p><a href="' . esc_url( $l['page'] ) . '" target="_blank" rel="noopener">' . esc_html( $l['page'] ) . '</a></p>';
	}
	if ( $l['message'] ) {
		echo '<details><summary>Текст, надісланий у Telegram</summary><pre style="white-space:pre-wrap">' . esc_html( $l['message'] ) . '</pre></details>';
	}
}

function knot_lead_box_status( WP_Post $post ): void {
	$l = knot_lead_get( $post->ID );

	wp_nonce_field( 'knot_lead_save', 'knot_lead_nonce' );
	echo '<input type="hidden" name="post_status" value="publish">';

	echo '<p><select name="knot_lead_status">';
	foreach ( knot_lead_statuses() as $key => $label ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $l['status'], $key, false ), esc_html( $label ) );
	}
	echo '</select></p>';

	echo '<p><label for="knot_lead_note">Нотатка менеджера</label>';
	printf( '<textarea id="knot_lead_note" name="knot_lead_note" rows="4" placeholder="Наприклад: зателефонувала, домовились на п\'ятницю">%s</textarea></p>', esc_textarea( $l['note'] ) );

	echo '<div style="display:flex;justify-content:space-between;align-items:center">';
	echo '<input type="submit" name="save" class="button button-primary" value="Зберегти">';
	printf( '<a class="submitdelete" href="%s">У кошик</a>', esc_url( get_delete_post_link( $post->ID ) ) );
	echo '</div>';
}

function knot_lead_box_tg( WP_Post $post ): void {
	$l = knot_lead_get( $post->ID );

	echo '<p>' . knot_lead_tg_badge( $l ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput
	if ( $l['tg_tries'] ) {
		echo '<p class="description">Спроб: ' . (int) $l['tg_tries'] . '</p>';
	}
	if ( 'error' === $l['tg_status'] && $l['tg_error'] ) {
		echo '<p class="description">Остання помилка: ' . esc_html( $l['tg_error'] ) . '</p>';
	}

	$url = wp_nonce_url( admin_url( 'admin-post.php?action=knot_lead_resend&lead=' . $post->ID ), 'knot_lead_resend_' . $post->ID );
	printf( '<a class="button" href="%s">Надіслати повторно</a>', esc_url( $url ) );
}

add_action( 'save_post_' . KNOT_LEAD_CPT, static function ( $id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['knot_lead_nonce'] ) || ! wp_verify_nonce( $_POST['knot_lead_nonce'], 'knot_lead_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $id ) ) {
		return;
	}

	$status = sanitize_key( $_POST['knot_lead_status'] ?? '' );
	if ( isset( knot_lead_statuses()[ $status ] ) ) {
		update_post_meta( $id, '_lead_status', $status );
	}
	update_post_meta( $id, '_lead_note', wp_slash( sanitize_textarea_field( wp_unslash( $_POST['knot_lead_note'] ?? '' ) ) ) );
} );

add_action( 'admin_post_knot_lead_resend', static function () {
	$id = (int) ( $_GET['lead'] ?? 0 );
	check_admin_referer( 'knot_lead_resend_' . $id );

	if ( ! $id || ! current_user_can( 'edit_post', $id ) || KNOT_LEAD_CPT !== get_post_type( $id ) ) {
		wp_die( 'Недостатньо прав.' );
	}

	update_post_meta( $id, '_lead_tg_tries', 0 );
	$ok = knot_lead_send_telegram( $id );

	wp_safe_redirect( add_query_arg(
		array( 'lead_resent' => $ok ? 1 : 0, 'lead_resent_total' => 1 ),
		get_edit_post_link( $id, 'raw' )
	) );
	exit;
} );
