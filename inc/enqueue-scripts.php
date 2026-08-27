<?php

/**
 * Cache-busting version for a theme asset, from its modification time.
 *
 * Passing null as the version leaves the URL without ?ver=, so browsers and the
 * host keep serving the copy they already have after a deploy. That is how an
 * updated stylesheet can silently fail to reach visitors while the markup is
 * already new. The mtime changes with every upload, so the URL changes with it.
 *
 * @param string $relative_path Path inside the theme, e.g. 'assets/js/cart.js'.
 * @return string|int Version string for wp_enqueue_*.
 */
function knot_asset_version(string $relative_path) {
	$file = get_stylesheet_directory() . '/' . ltrim($relative_path, '/');

	return file_exists($file)
		? filemtime($file)
		: (wp_get_theme()->get('Version') ?: '1.0');
}

function auto_enqueue_styles()
{
	$base_dir = get_stylesheet_directory() . '/assets/css';
	$base_url = get_stylesheet_directory_uri() . '/assets/css';

	$exclude = [
		'header.css',
		'global.css',
		'popup.css',
		'swiper.css',
        'product.css',
        'single-comments.css',
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
	wp_enqueue_style('style', get_stylesheet_uri(), array(), knot_asset_version('style.css'));
	wp_enqueue_style('fonts', get_stylesheet_directory_uri() . '/assets/font/fonts.css', array(), knot_asset_version('assets/font/fonts.css'));
    wp_enqueue_style('popup', get_stylesheet_directory_uri() . '/assets/css/components/popup.css', array(), knot_asset_version('assets/css/components/popup.css'));
	auto_enqueue_styles();

	//scripts
	wp_enqueue_script('main-js', get_template_directory_uri() . '/assets/js/main.js', [], filemtime(get_template_directory() . '/assets/js/main.js'), true);
	// ~150 KB — registered always (blocks may depend on the handle), but only
	// shipped to pages that actually render a slider.
	wp_register_script('swiper-script', get_template_directory_uri() . '/assets/js/swiper.min.js', [], filemtime(get_template_directory() . '/assets/js/swiper.min.js'), true);

	if (knot_page_needs_swiper()) {
		wp_enqueue_script('swiper-script');
		wp_enqueue_style('swiper-css', get_template_directory_uri() . '/assets/css/swiper.css', [], filemtime(get_template_directory() . '/assets/css/swiper.css'));
	}

	// Navigation speed: hover-prefetch + progress bar (global)
	$nav_js = get_template_directory() . '/assets/js/nav-speed.js';
	if (file_exists($nav_js)) {
		wp_enqueue_script('nav-speed', get_template_directory_uri() . '/assets/js/nav-speed.js', [], filemtime($nav_js), true);
	}

	// Recently viewed: record current product + render the block (global)
	$viewed_js = get_template_directory() . '/assets/js/viewed.js';
	if (file_exists($viewed_js)) {
		wp_enqueue_script('viewed-js', get_template_directory_uri() . '/assets/js/viewed.js', ['swiper-script'], filemtime($viewed_js), true);
		wp_localize_script('viewed-js', 'knotViewed', [
			'restUrl'   => esc_url_raw(rest_url('site/v1/viewed')),
			'currentId' => is_singular('post') ? get_the_ID() : 0,
		]);
	}

	// Cart (global)
	$cart_js = get_template_directory() . '/assets/js/cart.js';
	if (file_exists($cart_js)) {
		wp_enqueue_script('cart-js', get_template_directory_uri() . '/assets/js/cart.js', [], filemtime($cart_js), true);
		wp_localize_script('cart-js', 'knotCart', knot_cart_config());
	}


    if (is_singular('post')) {
        wp_enqueue_style('product', get_stylesheet_directory_uri() . '/assets/css/components/product.css', array(), knot_asset_version('assets/css/components/product.css'));
        wp_enqueue_style('single-comments', get_stylesheet_directory_uri() . '/assets/css/components/single-comments.css', array(), knot_asset_version('assets/css/components/single-comments.css'));

        // Recently-viewed section (auto-rendered before comments) reuses in-stock card styles.
        $in_stock_style = get_template_directory() . '/template-parts/gutenberg-blocks/in-stock/assets/style.css';
        if (file_exists($in_stock_style)) {
            wp_enqueue_style('block-in-stock-style', get_template_directory_uri() . '/template-parts/gutenberg-blocks/in-stock/assets/style.css', array(), filemtime($in_stock_style));
        }
        $viewed_style = get_template_directory() . '/template-parts/gutenberg-blocks/viewed-posts/assets/style.css';
        if (file_exists($viewed_style)) {
            wp_enqueue_style('block-viewed-posts-style', get_template_directory_uri() . '/template-parts/gutenberg-blocks/viewed-posts/assets/style.css', array(), filemtime($viewed_style));
        }

        wp_enqueue_script( 'product', get_template_directory_uri() . '/assets/js/product.js', [], filemtime( get_template_directory() . '/assets/js/product.js' ), true );
        wp_enqueue_script( 'order-form-js', get_template_directory_uri() . '/assets/js/order-form.js', [], filemtime( get_template_directory() . '/assets/js/order-form.js' ), true );
        wp_localize_script( 'order-form-js', 'knotOrderForm', knot_order_form_config() );

        wp_enqueue_script('comments-js', get_template_directory_uri().'/assets/js/comments.js', [], knot_asset_version('assets/js/comments.js'), true);
        wp_localize_script('comments-js','comment_ajax',['url'=>admin_url('admin-ajax.php'), 'post_id'=>get_the_ID()]);
    }
}

add_action('wp_enqueue_scripts', 'theme_scripts');