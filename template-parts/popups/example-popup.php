<?php $cf7_id = get_field('post_form_shortcode', 'option');?>
<div
	class="popup_inner"
	id="example_popup"
>
	<span
		class="overlay"
		data-action="closePopup"
	></span>

	<div class="popup_content">
		<div class="popup_container">
			<div class="head">
				<h3>Example Popup</h3>

				<span data-action="closePopup">&times;</span>
			</div>
			<div class="body">

                <?php if ($cf7_id): ?>
                    <?= do_shortcode('[contact-form-7 id="' . $cf7_id . '"]'); ?>
                <?php endif; ?>

            </div>
<!--			<div class="end">-->
<!--				Popup Footer-->
<!--			</div>-->
		</div>
	</div>
</div>