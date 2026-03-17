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


<link
	rel="preload"
	href="<?= get_field('header_logo', 'option')['url'] ?? '' ?>"
	as="image"
>

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
		href="<?= $blockStylesUrl ?>?ver=<?= $ver ?>"
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
			href="<?= $item ?>"
			as="image"
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
<!-- Remove if no jQuery -->
<link
	rel="preload"
	href="/wp-includes/js/jquery/jquery.min.js?ver=3.7.1"
	as="script"
>
<link
	rel="preload"
	href="/wp-includes/js/jquery/jquery-migrate.min.js?ver=3.4.1"
	as="script"
>
<!-- end jQuery -->

<!-- Fonts -->
<link
	rel="preload"
	href="<?= get_template_directory_uri() ?>/assets/font/PPNeueMontreal-Book.woff2"
	as="font"
	type="font/woff2"
	crossorigin
>