<?php

if (!isset($total_pages) || !isset($paged)) return;

// Optional: "24 з 121" counter + "load more" button rendered above the numbers.
$total_posts = isset($total_posts) ? (int) $total_posts : 0;
$shown_posts = isset($shown_posts) ? (int) $shown_posts : 0;

if ($total_posts > 0) {
    printf(
        '<p class="blog__count" data-count-shown="%1$d" data-count-total="%2$d"><span data-count-current>%1$d</span> з %2$d</p>',
        $shown_posts,
        $total_posts
    );
}

if ($paged < $total_pages) {
    printf(
        '<button type="button" class="blog__load-more main-btn seven" data-load-more data-next-page="%d">Показати ще</button>',
        $paged + 1
    );
}

if ($total_pages <= 1) return;

echo '<div class="blog__pagination">';

$dots_threshold = 7;

// Optional real URL base (current page + active filters, minus paged). When set,
// each page becomes a crawlable <a href> for SEO / no-JS; JS still upgrades the
// click to AJAX via data-page. Page 1 drops the paged param for a clean URL.
$page_base_url = $page_base_url ?? '';

$make_href = function($page) use ($page_base_url) {
    if ($page_base_url === '') {
        return '?pagenum=' . $page;
    }
    $url = ($page <= 1)
        ? remove_query_arg('pagenum', $page_base_url)
        : add_query_arg('pagenum', $page, $page_base_url);
    return esc_url(str_replace('%2C', ',', $url));
};

$btn = function($page, $current) use ($make_href) {
    $active = ($page === $current) ? 'active btn-animate third' : '';
    return '<a class="page-num '.$active.'" href="'.$make_href($page).'" data-page="'.$page.'">'.$page.'</a>';
};

if ($total_pages <= $dots_threshold) {

    for ($i = 1; $i <= $total_pages; $i++) {
        echo $btn($i, $paged);
    }

} else {

    echo $btn(1, $paged);

    if ($paged <= 3) {

        for ($i = 2; $i <= 4; $i++) {
            echo $btn($i, $paged);
        }

        echo '<span class="page-num dots">...</span>';

    } elseif ($paged >= $total_pages - 2) {

        echo '<span class="page-num dots">...</span>';

        for ($i = $total_pages - 3; $i < $total_pages; $i++) {
            echo $btn($i, $paged);
        }

    } else {

        echo '<span class="page-num dots">...</span>';

        for ($i = $paged - 1; $i <= $paged + 1; $i++) {
            echo $btn($i, $paged);
        }

        echo '<span class="page-num dots">...</span>';
    }

    echo $btn($total_pages, $paged);
}

echo '</div>';