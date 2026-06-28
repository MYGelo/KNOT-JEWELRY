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

	$content = get_the_content(null, false, $post_id);

	$blocks = parse_blocks($content);

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

// get images URLs of first block on the page for preload
function get_images_from_first_block_on_page($post_id = null)
{
	if (!$post_id) {
		$post_id = get_the_ID();
	}

	$content = apply_filters('the_content', get_post_field('post_content', $post_id));

	if (empty($content)) {
		return [];
	}

	$html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $content . '</body></html>';

	libxml_use_internal_errors(true);

	$dom = new DOMDocument();
	$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
	libxml_clear_errors();

	$xpath = new DOMXPath($dom);

	$nodes = $xpath->query('//body/*[self::section or self::div or self::article or self::header or self::main or self::footer]');
	if ($nodes->length === 0) {
		return [];
	}

	$firstSection = $nodes->item(0);

	$imgNodes = $xpath->query('.//img[@fetchpriority="high"]', $firstSection);

	$images = [];
	foreach ($imgNodes as $img) {
		$src = $img->getAttribute('src');
		if ($src) {
			$images[] = $src;
		}
	}

	return $images;
}

add_theme_support('post-thumbnails', ['post']);


