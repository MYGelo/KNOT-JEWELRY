<?php

if (!defined('ABSPATH')) exit;

function custom_template_register_menus()
{
	register_nav_menus(array(
		'main-menu'   => esc_html__('Main Menu'),
		'menu-footer' => esc_html__('Footer Menu'),
	));
}

add_action('after_setup_theme', 'custom_template_register_menus');

function add_file_types_to_uploads($file_types)
{
	$new_filetypes = array();
	$new_filetypes['svg'] = 'image/svg+xml';
	$file_types = array_merge($file_types, $new_filetypes);
	return $file_types;
}

add_filter('upload_mimes', 'add_file_types_to_uploads');

// remove block-library styles
add_action('wp_enqueue_scripts', function () {
	wp_dequeue_style('wp-block-library');
	wp_dequeue_style('wp-block-library-theme');
	wp_dequeue_style('wc-block-style');
}, 100);

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

// get name of first block on the page for styles preload
function get_first_block_name_on_page($post_id = null)
{
	if (!$post_id) {
		$post_id = get_the_ID();
	}

	$blocks = knot_get_parsed_blocks($post_id);

	if (empty($blocks) || !isset($blocks[0]['blockName'])) {
		return null;
	}

	$name = $blocks[0]['blockName'];
	if ($name && strpos($name, '/') !== false) {
		$parts = explode('/', $name);
		return end($parts);
	}

	return $name;
}

/**
 * parse_blocks() for a post, memoised per request — several helpers need the
 * block list and the parser is not free.
 */
function knot_get_parsed_blocks($post_id): array
{
	static $cache = [];

	$post_id = (int) $post_id;
	if ($post_id <= 0) {
		return [];
	}

	if (!isset($cache[$post_id])) {
		// 'raw' — the default 'display' context runs the post_content filters,
		// which third-party code can use to alter markup before we parse it.
		$cache[$post_id] = parse_blocks((string) get_post_field('post_content', $post_id, 'raw'));
	}

	return $cache[$post_id];
}

/**
 * wp_localize_script() concatenates when called twice for the same handle,
 * which would duplicate the whole payload (block assets are enqueued early and
 * again by ACF at render time). This adds the data only once.
 */
function knot_localize_once(string $handle, string $object_name, array $data): void
{
	$existing = wp_scripts()->get_data($handle, 'data');

	if (is_string($existing) && strpos($existing, 'var ' . $object_name . ' =') !== false) {
		return;
	}

	wp_localize_script($handle, $object_name, $data);
}

/**
 * URL of the published page that contains a given block.
 *
 * Lets templates link to the catalog or the size guide without hard-coding a
 * slug. Cached, and the cache is dropped whenever a page is saved.
 */
function knot_page_url_with_block(string $block_name): string
{
	$cache_key = 'knot_page_with_block_' . md5($block_name);
	$cached    = get_transient($cache_key);

	if (is_string($cached)) {
		return $cached;
	}

	$url = '';

	$pages = get_posts([
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 50,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	]);

	// The front page is the most likely host, so check it first.
	$front = (int) get_option('page_on_front');
	if ($front) {
		array_unshift($pages, $front);
	}

	foreach (array_unique($pages) as $page_id) {
		if (has_block($block_name, $page_id)) {
			$url = (string) get_permalink($page_id);
			break;
		}
	}

	set_transient($cache_key, $url, DAY_IN_SECONDS);

	return $url;
}

add_action('save_post_page', function () {
	global $wpdb;
	$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_knot_page_with_block_%'");
});

/** Catalog page URL (the page holding the all-posts block). */
function knot_catalog_url(): string
{
	$url = knot_page_url_with_block('acf/all-posts');

	return $url ?: home_url('/');
}

/**
 * Does the current request actually render a Swiper slider?
 *
 * swiper.min.js is ~150 KB, so it should not be shipped to pages that have no
 * slider at all (legal pages, contact, care & sizing, 404 …).
 */
function knot_page_needs_swiper(): bool
{
	static $needs = null;

	if ($needs !== null) {
		return $needs;
	}

	// Product pages: gallery, its popup and the "recently viewed" strip.
	if (is_singular('post')) {
		return $needs = true;
	}

	if (!is_singular()) {
		return $needs = false;
	}

	$post = get_queried_object();
	if (!$post instanceof WP_Post) {
		return $needs = false;
	}

	$blocks = knot_get_parsed_blocks($post->ID);

	// A reusable block hides its real content behind a reference — play safe.
	foreach ($blocks as $block) {
		if (($block['blockName'] ?? '') === 'core/block') {
			return $needs = true;
		}
	}

	$slider_blocks = ['in-stock', 'reviews', 'add-comments', 'select-post', 'viewed-posts'];

	$names = [];
	knot_collect_acf_block_names($blocks, $names);

	return $needs = (bool) array_intersect($names, $slider_blocks);
}

/**
 * Collect the acf/* block names used on a page (including nested ones).
 */
function knot_collect_acf_block_names(array $blocks, array &$names): void
{
	foreach ($blocks as $block) {
		if (!empty($block['blockName']) && strpos($block['blockName'], 'acf/') === 0) {
			$names[] = substr($block['blockName'], 4);
		}

		if (!empty($block['innerBlocks'])) {
			knot_collect_acf_block_names($block['innerBlocks'], $names);
		}
	}
}

/**
 * Enqueue the assets of every ACF block on the page *before* wp_head prints.
 *
 * Block CSS is normally registered while the block renders — that happens in
 * the body, so the stylesheet would be printed in the footer (FOUC). Here we
 * only parse the content (cheap, no rendering) and call each block's own
 * enqueue_assets callback, which keeps handles and localisations identical.
 */
add_action('wp_enqueue_scripts', function () {
	if (is_admin() || !is_singular() || !function_exists('acf_get_block_types')) {
		return;
	}

	$post = get_queried_object();
	if (!$post instanceof WP_Post) {
		return;
	}

	$names = [];
	knot_collect_acf_block_names(knot_get_parsed_blocks($post->ID), $names);

	if (!$names) {
		return;
	}

	$block_types = acf_get_block_types();

	foreach (array_unique($names) as $name) {
		$type = $block_types['acf/' . $name] ?? null;

		if ($type && !empty($type['enqueue_assets']) && is_callable($type['enqueue_assets'])) {
			call_user_func($type['enqueue_assets'], $type);
		}
	}
}, 20);

// get images URLs of first block on the page for preload
function get_images_from_first_block_on_page($post_id = null)
{
	if (!$post_id) {
		$post_id = get_the_ID();
	}

	if (!$post_id) {
		return [];
	}

	// Rendering the whole content just to find the hero image executes every
	// block a second time, so the result is cached per revision and only
	// recomputed after the page is edited.
	$stamp  = (string) get_post_field('post_modified_gmt', $post_id);
	$cached = get_post_meta($post_id, '_knot_preload_images', true);

	if (is_array($cached) && ($cached['stamp'] ?? null) === $stamp && isset($cached['images'])) {
		return (array) $cached['images'];
	}

	$content = apply_filters('the_content', get_post_field('post_content', $post_id, 'raw'));

	if (empty($content)) {
		update_post_meta($post_id, '_knot_preload_images', ['stamp' => $stamp, 'images' => []]);
		return [];
	}

	$html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $content . '</body></html>';

	libxml_use_internal_errors(true);

	$dom = new DOMDocument();
	$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
	libxml_clear_errors();

	$xpath = new DOMXPath($dom);

	$images = [];

	$nodes = $xpath->query('//body/*[self::section or self::div or self::article or self::header or self::main or self::footer]');

	if ($nodes->length > 0) {
		$first = $nodes->item(0);

		$imgNodes = $xpath->query('.//img[@fetchpriority="high"]', $first);

		foreach ($imgNodes as $img) {
			$src = $img->getAttribute('src');
			if ($src) {
				$images[] = $src;
			}
		}
	}

	update_post_meta($post_id, '_knot_preload_images', ['stamp' => $stamp, 'images' => $images]);

	return $images;
}

add_theme_support('post-thumbnails', ['post']);


