<?php

if (!class_exists('ACF')) {
	return;
}

function create_maintenance_page()
{
	$page_title = 'Maintenance Page';
	$page_slug = 'maintenance';

	$existing_page = get_page_by_path($page_slug);
	if (!$existing_page) {
		wp_insert_post([
			'post_title'  => $page_title,
			'post_name'   => $page_slug,
			'post_status' => 'publish',
			'post_type'   => 'page',
			'post_author' => get_current_user_id(),
		]);
	}
}

add_action('after_switch_theme', 'create_maintenance_page');


function maintenance_redirect()
{
	$slug = 'maintenance';

	if (current_user_can('manage_options')) {
		return;
	}

	if (is_admin() || strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false) {
		return;
	}

	if (is_page($slug)) {
		return;
	}

	wp_redirect(home_url('/' . $slug));
	exit;
}

if (get_field('maintenance_mode', 'option')) {
	add_action('template_redirect', 'maintenance_redirect');
}