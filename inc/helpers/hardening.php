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
    // Any logged-in user keeps it: the block editor asks for this endpoint
    // (author panel), and blocking it for editors would break the admin.
    if (is_user_logged_in()) {
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
    $version = get_bloginfo('version');

    if (!is_string($src) || strpos($src, 'ver=' . $version) === false) {
        return $src;
    }

    // Replace rather than remove: dropping `ver` entirely would leave browsers
    // serving stale core CSS/JS after a WordPress update. A hash still changes
    // with every version, it just doesn't announce which one.
    $salt = defined('AUTH_SALT') ? AUTH_SALT : ABSPATH;

    return add_query_arg('ver', substr(md5($version . $salt), 0, 8), $src);
}

/**
 * /readme.html and /license.txt state the version in plain text.
 *
 * NOTE: these are real files, so the web server usually serves them without
 * ever booting WordPress — this hook only catches hosts that route everything
 * through PHP. The reliable fix is to delete both files after each update, or
 * deny them in .htaccess:
 *
 *   <FilesMatch "^(readme\.html|license\.txt)$">
 *     Require all denied
 *   </FilesMatch>
 */
add_action('template_redirect', function () {
    $path = strtolower(trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/'));

    if (in_array($path, ['readme.html', 'license.txt', 'wp-config-sample.php'], true)) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
});

/* -------------------------------------------------------------
| 3. NO CODE EDITING FROM THE ADMIN
|
| If an admin account is ever compromised, the built-in theme/plugin editor
| turns it into arbitrary code execution. Nothing here is edited from the
| dashboard anyway.
------------------------------------------------------------- */

if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

/* -------------------------------------------------------------
| 4. SECURITY HEADERS
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
