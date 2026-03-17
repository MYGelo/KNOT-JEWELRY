<div id="cart_popup">
	<span
		class="overlay"
		data-action="toggleCartPopup"
	></span>

	<div class="cart_content woocommerce">
		<div class="cart_head">
			<h3>Cart</h3>
			<span data-action="toggleCartPopup">
        <svg
	        width="24"
	        height="24"
	        viewBox="0 0 24 24"
	        fill="none"
	        xmlns="http://www.w3.org/2000/svg"
        >
					<path
						d="M6 6L18 18M18 6L6 18"
						stroke="black"
						stroke-linecap="round"
					/>
				</svg>
      </span>
		</div>

		<div class="cart_body">
			<?php woocommerce_mini_cart(); ?>
		</div>
	</div>
</div>