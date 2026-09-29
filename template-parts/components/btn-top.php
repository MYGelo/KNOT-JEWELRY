<?php
/**
 * Floating navigation buttons: "back" (all pages except the front page) and "to top".
 * Fallback URL is used when there is no same-site history to go back to.
 */
$back_label = get_field('back_button_label', 'option') ?: 'Назад';

$back_fallback = apply_filters('wpml_home_url', home_url('/'));
// After an order, going back in history would return to the submitted form.
$back_use_history = !is_page_template('thank-you.php');

if (is_singular('blog')) {
    $blog_page_id = apply_filters('wpml_object_id', get_option('page_for_posts'), 'page', true);
    $back_fallback = $blog_page_id ? get_permalink($blog_page_id) : ($back_fallback);
} elseif (is_singular('authors')) {
    $back_fallback = get_post_type_archive_link('authors') ?: $back_fallback;
} elseif (function_exists('wc_get_page_permalink') && (!$back_use_history || is_product() || is_cart() || is_checkout())) {
    $back_fallback = wc_get_page_permalink('shop') ?: $back_fallback;
}
?>
<?php if (!is_front_page()) : ?>
<a href="<?= esc_url($back_fallback) ?>" class="back-btn"<?= $back_use_history ? '' : ' data-no-history' ?> aria-label="<?= esc_attr($back_label) ?>">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
    <span class="back-btn__label"><?= esc_html($back_label) ?></span>
</a>
<?php endif; ?>

<button type="button" class="scroll-top-btn" aria-label="<?= esc_attr__('Вгору', 'knot') ?>">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M6 11l6-6 6 6"/></svg>
</button>
