<div class="header__basket">
	<button data-action="toggleCartPopup">
		<img
			src="<?= get_template_directory_uri() . '/assets/images/header-basket.png' ?>"
			alt="basket"
		>

		<?php $cart_count = WC()->cart->get_cart_contents_count();
		if ($cart_count > 0): ?>
			<span class="count"><?= WC()->cart->get_cart_contents_count() ?></span>
		<?php endif; ?>
	</button>
</div>