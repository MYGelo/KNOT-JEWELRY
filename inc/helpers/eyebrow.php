<?php
/**
 * Small decorative label printed above a section title (h2), flanked by two
 * thin rules. Editable from the block's "Eyebrow" ACF field; prints nothing
 * when the text is empty. Styles: .eyebrow in assets/css/global.css.
 */
function knot_eyebrow($text): void {
	$text = is_string($text) ? trim($text) : '';
	if ($text === '') {
		return;
	}

	echo '<div class="eyebrow"><span class="eyebrow__rule"></span>'
		. wp_kses_post($text)
		. '<span class="eyebrow__rule"></span></div>';
}
