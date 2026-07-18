<?php
$block_anchor = $block['anchor'] ?? '';
$extra        = !empty($block['className']) ? $block['className'] : '';

$title           = get_field('viewed_title') ?: 'Ви переглядали';
$tap_text        = get_field('viewed_tap_text') ?: '';
$exclude_current = (bool) get_field('viewed_exclude_current');

$current_id = ($exclude_current && is_singular('post')) ? get_the_ID() : 0;

get_template_part('template-parts/components/viewed-section', null, [
	'title'   => $title,
	'tap'     => $tap_text,
	'exclude' => $current_id,
	'extra'   => $extra,
	'anchor'  => $block_anchor,
]);
