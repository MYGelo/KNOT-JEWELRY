<?php

require_once __DIR__ . '/inc/theme.php';

// Helpers first: maintenance-page.php calls get_field() while it loads, which
// boots ACF and fires acf/init right there — any field group registered on that
// hook must already have the functions it builds its choices from.
require_once __DIR__ . '/inc/helpers/order-form.php';

require_once __DIR__ . '/inc/helpers/stock.php';

require_once __DIR__ . '/inc/acf/acf.php';

require_once __DIR__ . '/inc/enqueue-scripts.php';

require_once __DIR__ . '/inc/maintenance-page.php';

require_once __DIR__ . '/inc/helpers/hardening.php';

require_once __DIR__ . '/inc/helpers/translit.php';

require_once __DIR__ . '/inc/helpers/product.php';

require_once __DIR__ . '/inc/admin/items-table.php';

require_once __DIR__ . '/inc/all-posts.php';

require_once __DIR__ . '/inc/add-tax.php';

require_once __DIR__ . '/inc/helpers/comments.php';

require_once __DIR__ . '/inc/helpers/seo.php';

require_once __DIR__ . '/inc/helpers/cash.php';