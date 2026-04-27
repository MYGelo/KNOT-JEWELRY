<?php $cf7_id = get_field('post_form_shortcode', 'option');

$form = get_field('single_form_steps', 'option');

$title = $form['form_title'] ?? '';
$step1_text = $form['step1_text'] ?? '';
$step1_btn = $form['step1_button'] ?? 'Продовжити';
$step2_btn = $form['step2_button'] ?? '← Назад';
?>

<div class="popup_inner" id="example_popup">

    <span class="overlay" data-action="closePopup"></span>

    <div class="popup_content">
        <div class="popup_container">

            <div class="head">
                <?php if(!empty($title)): ?>
                    <h3><?= wp_kses_post($title) ?></h3>
                <?php endif; ?>

                <span data-action="closePopup">&times;</span>
            </div>

            <div class="body form-steps">

                <div class="steps-progress">
                    <span class="step-indicator active"></span>
                    <span class="step-indicator"></span>
                </div>

                <div class="steps-wrapper">

                    <!-- STEP 1 -->
                    <div class="form-step step-1 active">

                        <?php if(!empty($step1_text)): ?>
                            <div class="form-step__text-content"><?=wp_kses_post($step1_text);?></div>
                        <?php endif; ?>

                        <button class="btn main-btn third" data-action="nextStep">
                            <?= wp_kses_post($step1_btn) ?>
                        </button>
                    </div>

                    <!-- STEP 2 -->
                    <div class="form-step step-2">
                        <?php if ($cf7_id): ?>
                            <?= do_shortcode('[contact-form-7 id="' . $cf7_id . '"]'); ?>
                        <?php endif; ?>

                        <button class="btn-back" data-action="prevStep">
                            <?=wp_kses_post($step2_btn) ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>