<?php
/**
 * Basic hardening: stop handing attackers free reconnaissance.
 *
 * None of this replaces strong passwords or updates — it just removes the
 * cheap wins: usernames, the exact WordPress version, and missing browser
 * protection headers.
 */

/* -------------------------------------------------------------
| 1. NO USER ENUMERATION
|
| /wp-json/wp/v2/users listed the real logins, which is half of a
| brute-force attempt. Logged-in editors still get the endpoint.
------------------------------------------------------------- */

add_filter('rest_endpoints', function ($endpoints) {
    if (current_user_can('list_users')) {
        return $endpoints;
    }

    unset($endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)']);

    return $endpoints;
});

/** /?author=1 redirects reveal the login slug too. */
add_action('template_redirect', function () {
    if (is_admin() || !isset($_GET['author'])) {
        return;
    }

    wp_safe_redirect(home_url('/'), 301);
    exit;
});

/* -------------------------------------------------------------
| 2. HIDE THE EXACT VERSION
------------------------------------------------------------- */

remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');

/** Version query strings on assets leak it as well. */
add_filter('style_loader_src', 'knot_strip_version_arg', 20);
add_filter('script_loader_src', 'knot_strip_version_arg', 20);

function knot_strip_version_arg($src) {
    if (is_string($src) && strpos($src, 'ver=' . get_bloginfo('version')) !== false) {
        $src = remove_query_arg('ver', $src);
    }

    return $src;
}

/** /readme.html and /license.txt state the version in plain text. */
add_action('template_redirect', function () {
    $path = strtolower(trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/'));

    if (in_array($path, ['readme.html', 'license.txt', 'wp-config-sample.php'], true)) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
});

/* -------------------------------------------------------------
| 3. SECURITY HEADERS
------------------------------------------------------------- */

add_action('send_headers', function () {
    if (is_admin()) {
        return;
    }

    // Clickjacking: the site may not be framed by other origins.
    header('X-Frame-Options: SAMEORIGIN');

    // Don't let browsers second-guess declared MIME types.
    header('X-Content-Type-Options: nosniff');

    // Send the origin only, and never to plain-HTTP destinations.
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // The site needs none of these APIs.
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()');

    // HSTS only makes sense once the site is served over HTTPS.
    if (is_ssl()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
});
