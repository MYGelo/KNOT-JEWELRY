<?php

/**
 * Create Global Elements Menu Item
 */
if (function_exists('acf_add_options_page')) {

	# Settings Page
	$site_globals = acf_add_options_page(array(
		'page_title' => 'Theme Settings',
		'menu_title' => 'Theme Settings',
		'icon_url'   => 'dashicons-admin-generic',
		'redirect'   => true,
	));

	# Global Theme Settings
	$page_global_settings = acf_add_options_page(array(
		'page_title'  => 'Global Settings',
		'menu_title'  => 'Global Settings',
		'menu_slug'   => 'global_settings',
		'parent_slug' => $site_globals['menu_slug'],
	));

	# Settings for Header
	$page_settings_header = acf_add_options_page(array(
		'page_title'  => 'Settings Header',
		'menu_title'  => 'Settings Header',
		'menu_slug'   => 'settings_header',
		'parent_slug' => $site_globals['menu_slug'],
	));

	# Settings for Footer
	$page_settings_footer = acf_add_options_page(array(
		'page_title'  => 'Settings Footer',
		'menu_title'  => 'Settings Footer',
		'menu_slug'   => 'settings_footer',
		'parent_slug' => $site_globals['menu_slug'],
	));
}
