<?php

function knot_is_yoast_active(): bool {
    return defined('WPSEO_VERSION') || class_exists('WPSEO_Options');
}

function knot_seo_get_context(): array {
    $default_title = get_field('site_seo_title', 'option') ?: get_bloginfo('name');
    $default_desc  = get_field('site_seo_description', 'option') ?: get_bloginfo('description');
    $default_img   = get_field('site_og_image', 'option');

    if (is_array($default_img)) {
        $default_img = $default_img['url'] ?? '';
    }

    $title = $default_title;
    $desc  = $default_desc;
    $img   = $default_img ?: '';
    $type  = 'website';
    $url   = is_singular() ? get_permalink() : home_url('/');

    if (is_singular()) {
        $title = get_the_title();

        if (is_singular('post')) {
            $desc = get_the_excerpt();

            if (!$desc) {
                $desc = wp_trim_words(wp_strip_all_tags(get_post_field('post_content', get_the_ID())), 25);
            }

            $thumb = get_the_post_thumbnail_url(get_the_ID(), 'full');
            $img   = $thumb ?: $default_img;
            $type  = 'article';
        } else {
            $desc = has_excerpt() ? get_the_excerpt() : $default_desc;
        }

        if (!$desc) {
            $desc = $default_desc;
        }
    } elseif (is_front_page() || is_home()) {
        $url = home_url('/');
    }

    return compact('title', 'desc', 'img', 'type', 'url');
}

function knot_seo_google_verification_tag(): void {
    $google_verification = trim((string) get_field('google_site_verification', 'option'));

    if ($google_verification === '') {
        return;
    }

    echo '<meta name="google-site-verification" content="' . esc_attr($google_verification) . '">' . "\n";
}

function site_seo_head() {
    if (is_admin()) {
        return;
    }

    if (knot_is_yoast_active()) {
        knot_seo_google_verification_tag();
        return;
    }

    $ctx = knot_seo_get_context();
    ?>

    <title><?php echo esc_html($ctx['title']); ?></title>

    <meta name="description" content="<?php echo esc_attr($ctx['desc']); ?>">
    <link rel="canonical" href="<?php echo esc_url($ctx['url']); ?>">

    <?php if (!empty($ctx['img'])): ?>
        <meta property="og:image" content="<?php echo esc_url($ctx['img']); ?>">
        <meta name="twitter:image" content="<?php echo esc_url($ctx['img']); ?>">
    <?php endif; ?>

    <meta property="og:title" content="<?php echo esc_attr($ctx['title']); ?>">
    <meta property="og:description" content="<?php echo esc_attr($ctx['desc']); ?>">
    <meta property="og:type" content="<?php echo esc_attr($ctx['type']); ?>">
    <meta property="og:url" content="<?php echo esc_url($ctx['url']); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
    <meta property="og:locale" content="uk_UA">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr($ctx['title']); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($ctx['desc']); ?>">

    <?php knot_seo_google_verification_tag(); ?>

    <?php
}

add_action('wp_head', 'site_seo_head', 1);

function knot_seo_robots(array $robots): array {
    if (get_field('maintenance_mode', 'option')) {
        $robots['noindex']  = true;
        $robots['nofollow'] = true;
    }

    return $robots;
}

add_filter('wp_robots', 'knot_seo_robots');

function knot_seo_robots_txt(string $output, bool $public): string {
    if (!$public || knot_is_yoast_active()) {
        return $output;
    }

    $sitemap_url = home_url('/wp-sitemap.xml');

    if (strpos($output, $sitemap_url) === false) {
        $output .= "\nSitemap: " . esc_url($sitemap_url) . "\n";
    }

    return $output;
}

add_filter('robots_txt', 'knot_seo_robots_txt', 99, 2);

function knot_seo_json_ld() {
    if (is_admin() || !is_front_page() || knot_is_yoast_active()) {
        return;
    }

    $ctx = knot_seo_get_context();

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'WebSite',
        'name'        => get_bloginfo('name'),
        'url'         => home_url('/'),
        'description' => $ctx['desc'],
        'inLanguage'  => 'uk-UA',
    ];

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}

add_action('wp_head', 'knot_seo_json_ld', 5);

function knot_disable_wp_title_tag() {
    if (knot_is_yoast_active()) {
        return;
    }

    remove_action('wp_head', '_wp_render_title_tag', 1);
}

add_action('init', 'knot_disable_wp_title_tag');

function knot_is_seo_bot_request(): bool {
    $uri = $_SERVER['REQUEST_URI'] ?? '';

    if ($uri === '') {
        return false;
    }

    return (bool) preg_match(
        '#/(robots\.txt|sitemap_index\.xml|wp-sitemap[^?]*\.xml|[^/]+-sitemap[^?]*\.xml)(\?.*)?$#i',
        $uri
    );
}
