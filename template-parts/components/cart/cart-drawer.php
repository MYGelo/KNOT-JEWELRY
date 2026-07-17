<?php
/**
 * Global cart drawer (side panel).
 * Behaviour lives in assets/js/cart.js, styles in assets/css/components/cart.css.
 */
$privacy_url = function_exists('knot_get_privacy_policy_url')
    ? knot_get_privacy_policy_url()
    : home_url('/privacy-policy/');

$form_opt   = get_field('single_form_steps', 'option');
$step1_text = $form_opt['step1_text'] ?? '';
$step1_btn  = $form_opt['step1_button'] ?? 'Продовжити';
$step2_btn  = $form_opt['step2_button'] ?? '← Назад';
?>

<div class="cart-drawer" id="cart-drawer" aria-hidden="true">

    <div class="cart-drawer__bg" data-cart-close></div>

    <aside class="cart-drawer__panel" role="dialog" aria-modal="true" aria-label="Кошик">

        <div class="cart-drawer__head">
            <h3 class="cart-drawer__title">Кошик</h3>
            <button type="button" class="cart-drawer__close" data-cart-close aria-label="Закрити">
                <span></span><span></span>
            </button>
        </div>

        <!-- STEP: cart items -->
        <div class="cart-drawer__step is-active" data-step="cart">

            <p class="cart-drawer__empty" data-cart-empty>Ваш кошик порожній</p>

            <ul class="cart-drawer__items" data-cart-items></ul>

            <div class="cart-drawer__foot" data-cart-foot hidden>
                <div class="cart-drawer__summary">
                    <span>До сплати:</span>
                    <span class="cart-drawer__total" data-cart-total>0 ₴</span>
                </div>
                <button type="button" class="btn main-btn third cart-drawer__checkout" data-cart-goto="intro">
                    Оформити замовлення
                </button>
                <p class="cart-drawer__hint" data-cart-hint hidden>Оберіть розмір для кілець, щоб продовжити.</p>
            </div>
        </div>

        <!-- STEP: form intro (obligatory) -->
        <div class="cart-drawer__step" data-step="intro">

            <button type="button" class="btn-back cart-drawer__back" data-cart-goto="cart">← Назад до кошика</button>

            <div class="steps-progress">
                <span class="step-indicator is-active"></span>
                <span class="step-indicator"></span>
            </div>

            <?php if (!empty($step1_text)): ?>
                <div class="cart-drawer__intro"><?= wp_kses_post($step1_text); ?></div>
            <?php endif; ?>

            <button type="button" class="btn main-btn third" data-cart-goto="form">
                <?= wp_kses_post($step1_btn); ?>
            </button>
        </div>

        <!-- STEP: contact form -->
        <div class="cart-drawer__step" data-step="form">

            <button type="button" class="btn-back cart-drawer__back" data-cart-goto="intro">
                <?= wp_kses_post($step2_btn); ?>
            </button>

            <div class="steps-progress">
                <span class="step-indicator"></span>
                <span class="step-indicator is-active"></span>
            </div>

            <form id="cart-order-form" class="order-form cart-order-form" novalidate>

                <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
                <input type="text" name="math-check" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">

                <div class="order-form__alert" id="cart-order-form-alert" hidden role="alert"></div>

                <div class="styled">
                    <label class="styled-label" for="cart-full-name">Ім'я та прізвище</label>
                    <input class="styled__input" type="text" id="cart-full-name" name="full-name"
                           maxlength="100" autocomplete="name" placeholder="Анна" required>
                    <span class="field-error" data-error-for="full-name"></span>
                </div>

                <div class="styled">
                    <label class="styled-label" for="cart-phone">Телефон</label>
                    <input class="styled__input" type="tel" id="cart-phone" name="your-phone"
                           inputmode="tel" autocomplete="tel" placeholder="+380 XX XXX XX XX" required>
                    <span class="field-error" data-error-for="your-phone"></span>
                </div>

                <div class="styled">
                    <label class="styled-label" for="cart-telegram">Telegram</label>
                    <input class="styled__input" type="text" id="cart-telegram" name="your-telegram"
                           maxlength="32" autocomplete="off" placeholder="@username">
                    <span class="field-error" data-error-for="your-telegram"></span>
                </div>

                <div class="styled">
                    <label class="styled-label" for="cart-instagram">Instagram</label>
                    <input class="styled__input" type="text" id="cart-instagram" name="your-instagram"
                           maxlength="30" autocomplete="off" placeholder="@username">
                    <span class="field-error" data-error-for="your-instagram"></span>
                </div>

                <div class="styled">
                    <label class="styled-label" for="cart-message">Коментар</label>
                    <textarea class="styled__input" id="cart-message" name="your-message"
                              maxlength="1000" placeholder="Ваше повідомлення (необов'язково)"></textarea>
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

                <button type="submit" class="btn main-btn third order-form__submit" id="cart-order-form-submit">
                    Надіслати замовлення
                </button>
            </form>
        </div>

    </aside>
</div>
