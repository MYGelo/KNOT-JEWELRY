<!-- Styles -->
<?php $ver = filemtime(get_template_directory() . '/assets/css/global.css'); ?>
<link
	rel="preload"
	href="<?= get_template_directory_uri() ?>/assets/css/global.css?ver=<?= $ver ?>"
	as="style"
	onload="this.rel=`stylesheet`"
>

<?php $ver = filemtime(get_template_directory() . '/assets/css/components/header.css'); ?>
<link
	rel="preload"
	href="<?= get_template_directory_uri() ?>/assets/css/components/header.css?ver=<?= $ver ?>"
	as="style"
	onload="this.rel=`stylesheet`"
>

<?php // swiper.css is now enqueued normally (and only on pages with a slider),
      // see inc/enqueue-scripts.php — loading it async here caused the sliders
      // to flash as a vertical stack before the stylesheet applied. ?>

<?php // Without the guard an empty field renders href="" and the browser
      // downloads the current page as if it were an image.
$logo_preload = get_field('header_logo', 'option')['url'] ?? '';
if ($logo_preload): ?>
	<link
		rel="preload"
		href="<?= esc_url($logo_preload) ?>"
		as="image"
	>
<?php endif; ?>

<!-- Resources for first block at the page -->
<?php
$blockToPreload = get_first_block_name_on_page();
$blockStylesPath = '/template-parts/gutenberg-blocks/' . $blockToPreload . '/assets/style.css';
if (file_exists(get_template_directory() . $blockStylesPath)):
	$blockStylesUrl = get_template_directory_uri() . $blockStylesPath;
	$ver = filemtime(get_template_directory() . $blockStylesPath);
	?>
	<link
		rel="preload"
		href="<?= esc_url($blockStylesUrl . '?ver=' . $ver) ?>"
		as="style"
		onload="this.rel=`stylesheet`"
	>
<?php endif; ?>

<?php
$imagesToPreload = get_images_from_first_block_on_page();
if (!empty($imagesToPreload)):
	foreach ($imagesToPreload as $item): ?>
		<link
			rel="preload"
			href="<?= esc_url($item) ?>"
			as="image"
            fetchpriority="high"
		>
	<?php endforeach;
endif; ?>

<!-- Scripts -->
<?php $ver = filemtime(get_template_directory() . '/assets/js/main.js'); ?>
<link
	rel="preload"
	href="<?= get_template_directory_uri() ?>/assets/js/main.js?ver=<?= $ver ?>"
	as="script"
>

<?php // Only worth preloading where a slider is actually rendered.
if (function_exists('knot_page_needs_swiper') && knot_page_needs_swiper()):
	$swiper_ver = filemtime(get_template_directory() . '/assets/js/swiper.min.js'); ?>
	<link
			rel="preload"
			href="<?= esc_url(get_template_directory_uri() . '/assets/js/swiper.min.js?ver=' . $swiper_ver) ?>"
			as="script"
	>
<?php endif; ?>

<!-- Fonts -->
<link
	rel="preload"
	href="<?= get_template_directory_uri() ?>/assets/font/TenorSans-Regular.woff2"
	as="font"
	type="font/woff2"
	crossorigin
>