<?php

if (!isset($total_pages) || !isset($paged)) return;
if ($total_pages <= 1) return;

echo '<div class="blog__pagination">';

$dots_threshold = 7;

$btn = function($page, $current) {
    $active = ($page === $current) ? 'active btn-animate third' : '';
    return '<button class="page-num '.$active.'" data-page="'.$page.'">'.$page.'</button>';
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