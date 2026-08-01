<?php
$form = get_field('repair_form_steps', 'option');
$title      = $form['repair_form_title'] ?? '';
$step1_text = $form['repair_step1_text'] ?? '';
$step1_btn  = $form['repair_step1_button'] ?? 'Продовжити';
$step2_btn  = $form['repair_step2_button'] ?? '← Назад';
$inst_link = $form['repair_step1_inst_link'] ?? [];
$product_note = get_field('repair_form_settings_product-note', 'option');
$privacy_url = knot_get_privacy_policy_url();

?>

<div class="popup_inner" id="example_popup">

    <span class="overlay" data-action="closePopup"></span>

    <div class="popup_content">
        <div class="popup_container">

            <div class="head">
                <?php if (!empty($title)): ?>
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

                    <div class="form-step step-1 active">

                        <?php if (!empty($step1_text)): ?>
                            <div class="form-step__text-content"><?= wp_kses_post($step1_text); ?></div>
                        <?php endif; ?>

                        <div class="reviews__cta-wrap">
                            <?php if (!empty($inst_link['url'])): ?>
                                <a class="reviews-cta" href="<?= esc_url($inst_link['url']); ?>" target="_blank" rel="noopener noreferrer">
                                    <svg class="reviews-cta__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                        <rect x="2" y="2" width="20" height="20" rx="6" ry="6" fill="none" stroke="currentColor" stroke-width="2"/>
                                        <circle cx="12" cy="12" r="5" fill="none" stroke="currentColor" stroke-width="2"/>
                                        <circle cx="17.6" cy="6.4" r="1.5" fill="currentColor"/>
                                    </svg>
                                    <span><?= esc_html($inst_link['title'] ?: 'Написати у Instagram'); ?></span>
                                </a>
                            <?php endif; ?>

                            <button type="button" class="btn main-btn third" data-action="nextStep">
                                <?= wp_kses_post($step1_btn) ?>
                            </button>
                        </div>
                    </div>

                    <div class="form-step step-2">
                        <form
                            id="order-form"
                            class="order-form"
                        >
                            <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true" aria-label="Залиште порожнім">
                            <input type="text" name="math-check" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true" aria-label="Залиште порожнім">

                            <div class="order-form__alert" id="order-form-alert" hidden role="alert"></div>

                            <div class="styled">
                                <label class="styled-label" for="order-full-name">Ім'я та прізвище</label>
                                <input
                                    class="styled__input"
                                    type="text"
                                    id="order-full-name"
                                    name="full-name"
                                    maxlength="100"
                                    autocomplete="name"
                                    placeholder="Анна"
                                    required
                                >
                                <span class="field-error" data-error-for="full-name"></span>
                            </div>

                            <div class="styled">
                                <label class="styled-label" for="order-phone">Телефон</label>
                                <input
                                    class="styled__input"
                                    type="tel"
                                    id="order-phone"
                                    name="your-phone"
                                    inputmode="tel"
                                    autocomplete="tel"
                                    placeholder="+380 XX XXX XX XX"
                                    required
                                >
                                <span class="field-error" data-error-for="your-phone"></span>
                            </div>

                            <div class="styled">
                                <label class="styled-label" for="order-telegram">Telegram</label>
                                <input
                                    class="styled__input"
                                    type="text"
                                    id="order-telegram"
                                    name="your-telegram"
                                    maxlength="32"
                                    autocomplete="off"
                                    placeholder="@username"
                                >
                                <span class="field-error" data-error-for="your-telegram"></span>
                            </div>

                            <div class="styled">
                                <label class="styled-label" for="order-instagram">Instagram</label>
                                <input
                                    class="styled__input"
                                    type="text"
                                    id="order-instagram"
                                    name="your-instagram"
                                    maxlength="30"
                                    autocomplete="off"
                                    placeholder="@username"
                                >
                                <span class="field-error" data-error-for="your-instagram"></span>
                            </div>

                            <div class="styled">
                                <label class="styled-label" for="order-message">Коментар</label>
                                <textarea
                                    class="styled__input"
                                    id="order-message"
                                    name="your-message"
                                    maxlength="1000"
                                    placeholder="Ваше повідомлення (необов’язково)"
                                ></textarea>
                                <span class="field-error" data-error-for="your-message"></span>
                            </div>

                            <div class="styled order-form__privacy">
                                <label class="order-form__privacy-label">
                                    <input type="checkbox" name="privacy-policy" value="1" required>
                                    <span>
                                        Погоджуюсь з
                                        <a href="<?= esc_url($privacy_url) ?>" target="_blank" rel="noopener noreferrer">правилами</a>
                                    </span>
                                </label>
                                <span class="field-error" data-error-for="privacy-policy"></span>
                            </div>

                            <button type="submit" class="btn main-btn third order-form__submit" id="order-form-submit">
                                Надіслати заявку
                            </button>
                        </form>

                        <button type="button" class="btn-back" data-action="prevStep">
                            <?= wp_kses_post($step2_btn) ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
