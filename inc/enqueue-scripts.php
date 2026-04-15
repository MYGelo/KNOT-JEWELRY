<?php

function auto_enqueue_styles()
{
	$base_dir = get_stylesheet_directory() . '/assets/css';
	$base_url = get_stylesheet_directory_uri() . '/assets/css';

	$exclude = [
		'header.css',
		'global.css',
		'popup.css',
		'cart-popup.css',
		'swiper.css',
	];

	if (!is_dir($base_dir)) {
		error_log("CSS directory not found: " . $base_dir);
		return;
	}

	$files = [];
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($base_dir, FilesystemIterator::SKIP_DOTS)
	);

	foreach ($iterator as $file_info) {
		if (!$file_info instanceof SplFileInfo || $file_info->getExtension() !== 'css') {
			continue;
		}

		$files[] = $file_info->getPathname();
	}

	if (empty($files)) {
		error_log("No CSS files found in: " . $base_dir);
		return;
	}

	foreach ($files as $file_path) {
		$filename = basename($file_path);
		if (in_array($filename, $exclude)) {
			continue;
		}

		$relative_path = str_replace($base_dir . '/', '', $file_path);
		$file_url = $base_url . '/' . $relative_path;

		$handle = 'style-' . sanitize_title(str_replace([
				'/',
				'.css',
			], [
				'-',
				'',
			], $relative_path));

		wp_enqueue_style($handle, esc_url($file_url), [], filemtime($file_path));
	}
}

function theme_scripts()
{
	if (!is_user_logged_in()) {
		wp_deregister_style('dashicons');
	}

	//styles
	wp_enqueue_style('style', get_stylesheet_uri(), array(), null);
	wp_enqueue_style('fonts', get_stylesheet_directory_uri() . '/assets/font/fonts.css', array(), null);
	auto_enqueue_styles();
	$swiper_css_path = get_template_directory() . '/assets/css/swiper.css';
	if (file_exists($swiper_css_path)) {
		wp_enqueue_style('swiper-styles', get_stylesheet_directory_uri() . '/assets/css/swiper.css', array(), filemtime($swiper_css_path));
	}


	// uncomment next line to remove jQuery if woocommerce isn't use
	//wp_deregister_script('jquery');

	//scripts
	wp_enqueue_script('main-js', get_template_directory_uri() . '/assets/js/main.js', [], filemtime(get_template_directory() . '/assets/js/main.js'), true);
	$swiper_js_path = get_template_directory() . '/assets/js/swiper.min.js';
	if (file_exists($swiper_js_path)) {
		wp_enqueue_script('swiper-script', get_template_directory_uri() . '/assets/js/swiper.min.js', [], filemtime($swiper_js_path), true);
	}
	wp_enqueue_script('scroll-animate', get_template_directory_uri() . '/assets/js/scroll-animate.js', [], filemtime(get_template_directory() . '/assets/js/scroll-animate.js'), true);
	wp_enqueue_script('product', get_template_directory_uri() . '/assets/js/product.js', [], filemtime(get_template_directory() . '/assets/js/product.js'), true);
	wp_enqueue_script('telegram-bot', get_template_directory_uri() . '/assets/js/telegram-bot.js', [], filemtime(get_template_directory() . '/assets/js/telegram-bot.js'), true);

	wp_localize_script('telegram-bot', 'knotTelegram', [
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce'    => wp_create_nonce('knot_telegram_nonce'),
	]);

}

add_action('wp_enqueue_scripts', 'theme_scripts');