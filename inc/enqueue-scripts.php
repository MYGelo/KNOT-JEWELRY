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

	$files_root = glob($base_dir . '/*.css');
	$files_subdirs = glob($base_dir . '/**/*.css', GLOB_BRACE);
	$files = array_merge($files_root ?: [], $files_subdirs ?: []);

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
    wp_enqueue_style('main-style', get_template_directory_uri().'/assets/css/main.css', [], filemtime(get_template_directory().'/assets/css/main.css'));

//    wp_enqueue_style('style', get_stylesheet_uri(), array(), null);
//	wp_enqueue_style('fonts', get_stylesheet_directory_uri() . '/assets/font/fonts.css', array(), null);
//	auto_enqueue_styles();
	wp_enqueue_style('swiper-styles', get_stylesheet_directory_uri() . '/assets/css/swiper.css', array(), null);

	//scripts
	wp_enqueue_script('main-js', get_template_directory_uri() . '/assets/js/main.js', [], filemtime(get_template_directory() . '/assets/js/main.js'), true);
	wp_enqueue_script('swiper-script', get_template_directory_uri() . '/assets/js/swiper.min.js', [], filemtime(get_template_directory() . '/assets/js/swiper.min.js'), true);
    wp_enqueue_script( 'scroll-animate', get_template_directory_uri() . '/assets/js/scroll-animate.js', [], filemtime( get_template_directory() . '/assets/js/scroll-animate.js' ), true );
    wp_enqueue_script( 'product', get_template_directory_uri() . '/assets/js/product.js', [], filemtime( get_template_directory() . '/assets/js/product.js' ), true );
    wp_enqueue_script( 'telegram-bot', get_template_directory_uri() . '/assets/js/telegram-bot.js', [], filemtime( get_template_directory() . '/assets/js/telegram-bot.js' ), true );

    wp_enqueue_script('comments-js', get_template_directory_uri().'/assets/js/comments.js', [], null, true);
    wp_localize_script('comments-js','comment_ajax',['url'=>admin_url('admin-ajax.php'), 'post_id'=>get_the_ID()]);
}

add_action('wp_enqueue_scripts', 'theme_scripts');