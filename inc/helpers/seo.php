<?php
function site_seo_head() {

    if (is_admin()) return;

    global $post;

    $default_title = get_field('site_seo_title', 'option') ?: get_bloginfo('name');
    $default_desc  = get_field('site_seo_description', 'option') ?: get_bloginfo('description');
    $default_img   = get_field('site_og_image', 'option');

    /* HOME */

    if (is_front_page()) {

        $title = $default_title;
        $desc  = $default_desc;
        $img   = $default_img;
        $type  = 'website';
        $url   = home_url();

    }

    /* SINGLE POST */

    elseif (is_single()) {

        $title = get_the_title();

        $desc = get_the_excerpt();

        if (!$desc && !empty($post->post_content)) {
            $desc = wp_trim_words(strip_tags($post->post_content), 25);
        }

        if (!$desc) {
            $desc = $default_desc;
        }

        $img = get_the_post_thumbnail_url(get_the_ID(), 'full') ?: $default_img;

        $type = 'article';
        $url  = get_permalink();

    }

    else {

        $title = wp_get_document_title();
        $desc  = $default_desc;
        $img   = $default_img;
        $type  = 'website';
        $url   = home_url();

    }

?>

    <title><?php echo esc_html($title); ?></title>

    <meta name="description" content="<?php echo esc_attr($desc); ?>">

    <link rel="canonical" href="<?php echo esc_url($url); ?>">

    <!-- OpenGraph -->
    <meta property="og:title" content="<?php echo esc_attr($title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($desc); ?>">
    <meta property="og:type" content="<?php echo esc_attr($type); ?>">
    <meta property="og:url" content="<?php echo esc_url($url); ?>">
    <meta property="og:image" content="<?php echo esc_url($img); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
    <meta property="og:locale" content="uk_UA">

    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr($title); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($desc); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($img); ?>">

<?php
}

add_action('wp_head', 'site_seo_head', 1);