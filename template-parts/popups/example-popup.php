<?php
$form = get_field('single_form_steps', 'option');

$title      = $form['form_title'] ?? '';
$step1_text = $form['step1_text'] ?? '';
$step1_btn  = $form['step1_button'] ?? 'Продовжити';
$step2_btn  = $form['step2_button'] ?? '← Назад';

$needs_ring_size = knot_product_needs_ring_size(get_the_ID());
$ring_sizes      = knot_get_ring_sizes();
$privacy_url     = knot_get_privacy_policy_url();
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

                        <button type="button" class="btn main-btn third" data-action="nextStep">
                            <?= wp_kses_post($step1_btn) ?>
                        </button>
                    </div>

                    <div class="form-step step-2">
                        <form
                            id="order-form"
                            class="order-form"
                            novalidate
                            data-needs-ring-size="<?= $needs_ring_size ? '1' : '0' ?>"
                        >
                            <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
                            <input type="text" name="math-check" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">

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

                            <div class="styled coating-field">
                                <label class="styled-label" for="order-coating">Тип покриття</label>
                                <select class="styled__input" id="order-coating" name="coating">
                                    <option value="Без" selected>Без</option>
                                    <option value="Родій (білий)">Родій (білий)</option>
                                    <option value="Позолота">Позолота</option>
                                </select>
                                <p class="ring-size-notice" id="coating-notice" hidden>
                                    Фінальна вартість залежить від типу покриття та ваги виробу. Я з радістю розрахую точну ціну й підтверджу її під час оформлення замовлення 🤍
                                </p>
                            </div>

                            <?php if ($needs_ring_size): ?>
                                <div class="styled ring-size-field">
                                    <label class="styled-label" for="order-ring-size">Розмір</label>
                                    <select class="styled__input" id="order-ring-size" name="ring-size" required>
                                        <option value="">Оберіть розмір</option>
                                        <?php foreach ($ring_sizes as $size): ?>
                                            <option value="<?= esc_attr($size) ?>"><?= esc_html($size) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="ring-size-notice" id="ring-size-notice" hidden>
                                        Ви обрали нестандартний розмір — на такий виріб іде більше срібла, тож вартість буде трохи вищою. Я з радістю розрахую точну ціну й підтверджу її під час оформлення замовлення 🤍
                                    </p>
                                    <span class="field-error" data-error-for="ring-size"></span>
                                </div>
                            <?php endif; ?>

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
                                Надіслати замовлення
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
