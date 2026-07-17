document.addEventListener('DOMContentLoaded', function () {
    if (typeof knotOrderForm === 'undefined') return;

    const form = document.getElementById('order-form');
    if (!form) return;

    const config = knotOrderForm;
    const alertBox = document.getElementById('order-form-alert');
    const submitBtn = document.getElementById('order-form-submit');
    const phoneInput = document.getElementById('order-phone');
    const ringSizeSelect = document.getElementById('order-ring-size');
    const ringSizeNotice = document.getElementById('ring-size-notice');
    const coatingSelect = document.getElementById('order-coating');
    const coatingNotice = document.getElementById('coating-notice');
    const needsRingSize = form.dataset.needsRingSize === '1';

    const LAST_SEND_KEY = 'knot_last_order_send';
    let formStartTime = Date.now();
    let userInteracted = false;
    let isSubmitting = false;

    const PHONE_PREFIX = '+380 ';
    const PHONE_PREFIX_LEN = PHONE_PREFIX.length;
    const NAME_REGEX = /^[\p{L}][\p{L}'-]{1,49}(?:\s+[\p{L}][\p{L}'-]{1,49})*$/u;
    const PHONE_REGEX = /^\+380\d{9}$/;
    const TELEGRAM_REGEX = /^[a-zA-Z0-9_]{5,32}$/;
    const INSTAGRAM_REGEX = /^[a-zA-Z0-9._]{1,30}$/;

    ['mousemove', 'scroll', 'keydown', 'touchstart'].forEach(function (evt) {
        document.addEventListener(evt, function () {
            userInteracted = true;
        }, { once: true });
    });

    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-action="togglePopup"][data-target="#example_popup"]')) {
            resetFormTimer();
        }

        if (event.target.closest('#example_popup [data-action="nextStep"]')) {
            resetFormTimer();
        }
    });

    if (phoneInput) {
        phoneInput.addEventListener('focus', function () {
            if (!getNationalDigits(phoneInput.value)) {
                phoneInput.value = PHONE_PREFIX;
                phoneInput.setSelectionRange(PHONE_PREFIX_LEN, PHONE_PREFIX_LEN);
            }
        });

        phoneInput.addEventListener('keydown', function (event) {
            const start = phoneInput.selectionStart;
            const end = phoneInput.selectionEnd;

            if (
                (event.key === 'Backspace' && start <= PHONE_PREFIX_LEN && end <= PHONE_PREFIX_LEN)
                || (event.key === 'Delete' && start < PHONE_PREFIX_LEN)
            ) {
                event.preventDefault();
            }
        });

        phoneInput.addEventListener('paste', function (event) {
            event.preventDefault();

            const pasted = (event.clipboardData || window.clipboardData).getData('text') || '';
            applyPhoneValue(pasted);
        });

        phoneInput.addEventListener('input', function () {
            applyPhoneValue(phoneInput.value);
        });

        phoneInput.addEventListener('blur', function () {
            if (!getNationalDigits(phoneInput.value)) {
                phoneInput.value = '';
            }
        });
    }

    if (ringSizeSelect && ringSizeNotice) {
        ringSizeSelect.addEventListener('change', function () {
            const value = parseFloat(ringSizeSelect.value);
            ringSizeNotice.hidden = !(value > 19);
        });
    }

    if (coatingSelect && coatingNotice) {
        coatingSelect.addEventListener('change', function () {
            coatingNotice.hidden = !(coatingSelect.value && coatingSelect.value !== 'Без');
        });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (isSubmitting) return;

        clearErrors();

        const data = collectFormData();
        const errors = validateForm(data);

        if (Object.keys(errors).length) {
            showErrors(errors);
            return;
        }

        const spamError = validateAntiSpam(data);
        if (spamError) {
            showAlert(spamError);
            return;
        }

        isSubmitting = true;
        setSubmitting(true);

        sendToTelegram(data)
            .then(function () {
                localStorage.setItem(LAST_SEND_KEY, String(Date.now()));
                window.location.href = config.thankYouUrl;
            })
            .catch(function () {
                showAlert('Не вдалося надіслати замовлення. Спробуйте ще раз або напишіть нам у соцмережах.');
            })
            .finally(function () {
                isSubmitting = false;
                setSubmitting(false);
            });
    });

    function resetFormTimer() {
        formStartTime = Date.now();
    }

    function collectFormData() {
        const productTitle = document.querySelector('.product-title--js')?.textContent.trim() || '';
        const productMaterial = document.querySelector('.product-material--js')?.textContent.trim() || '';
        const productStone = document.querySelector('.product-stone--js')?.textContent.trim() || '';
        const productType = document.querySelector('.product-type--js')?.textContent.trim() || '';
        const productPrice = document.querySelector('.product-price--js .current')?.textContent.trim() || '';

        return {
            'full-name': form.querySelector('[name="full-name"]')?.value.trim() || '',
            'your-phone': normalizePhone(form.querySelector('[name="your-phone"]')?.value || ''),
            'ring-size': form.querySelector('[name="ring-size"]')?.value || '',
            coating: form.querySelector('[name="coating"]')?.value || '',
            'your-telegram': form.querySelector('[name="your-telegram"]')?.value.trim() || '',
            'your-instagram': form.querySelector('[name="your-instagram"]')?.value.trim() || '',
            'your-message': form.querySelector('[name="your-message"]')?.value.trim() || '',
            'privacy-policy': form.querySelector('[name="privacy-policy"]')?.checked ? '1' : '',
            website: form.querySelector('[name="website"]')?.value.trim() || '',
            'math-check': form.querySelector('[name="math-check"]')?.value.trim() || '',
            'product-title': productTitle,
            'product-material': productMaterial,
            'product-stone': productStone,
            'product-type': productType,
            'product-price': productPrice,
            'product-link': window.location.href,
        };
    }

    function validateForm(data) {
        const errors = {};

        if (!data['full-name']) {
            errors['full-name'] = 'Вкажіть ім\'я та прізвище';
        } else if (data['full-name'].length > 100 || !NAME_REGEX.test(data['full-name'])) {
            errors['full-name'] = 'Вкажіть коректне ім\'я та прізвище (мінімум 2 символи, лише літери)';
        }

        if (!data['your-phone']) {
            errors['your-phone'] = 'Вкажіть номер телефону';
        } else if (!PHONE_REGEX.test(data['your-phone'])) {
            errors['your-phone'] = 'Використовуйте формат +380 XX XXX XX XX';
        }

        if (needsRingSize) {
            if (!data['ring-size']) {
                errors['ring-size'] = 'Оберіть розмір каблучки';
            } else if (!isValidRingSize(data['ring-size'])) {
                errors['ring-size'] = 'Оберіть коректний розмір';
            }
        }

        if (data['your-telegram']) {
            const telegram = cleanUsername(data['your-telegram']);
            if (!TELEGRAM_REGEX.test(telegram)) {
                errors['your-telegram'] = 'Некоректний Telegram username';
            }
        }

        if (data['your-instagram']) {
            const instagram = cleanUsername(data['your-instagram']);
            if (!INSTAGRAM_REGEX.test(instagram)) {
                errors['your-instagram'] = 'Некоректний Instagram username';
            }
        }

        if (data['your-message'].length > 1000) {
            errors['your-message'] = 'Коментар занадто довгий (максимум 1000 символів)';
        }

        if (!data['privacy-policy']) {
            errors['privacy-policy'] = 'Потрібна згода з правилами';
        }

        return errors;
    }

    function validateAntiSpam(data) {
        if (data.website) {
            return 'Помилка відправки. Спробуйте ще раз.';
        }

        if (data['math-check']) {
            return 'Помилка відправки. Спробуйте ще раз.';
        }

        if (Date.now() - formStartTime < config.minFormTime) {
            return 'Зачекайте кілька секунд перед відправкою.';
        }

        if (!userInteracted) {
            return 'Помилка відправки. Спробуйте ще раз.';
        }

        const lastSend = localStorage.getItem(LAST_SEND_KEY);
        if (lastSend && Date.now() - Number(lastSend) < config.resendDelay) {
            return 'Зачекайте трохи перед повторною відправкою.';
        }

        return '';
    }

    function isValidRingSize(value) {
        return Array.isArray(config.ringSizes) && config.ringSizes.includes(String(value));
    }

    function getNationalDigits(value) {
        let digits = String(value || '').replace(/\D/g, '');

        if (digits.startsWith('380')) {
            digits = digits.slice(3);
        } else if (digits.startsWith('0')) {
            digits = digits.slice(1);
        }

        return digits.slice(0, 9);
    }

    function formatPhoneDisplay(nationalDigits) {
        nationalDigits = getNationalDigits(nationalDigits);

        let formatted = PHONE_PREFIX;

        if (nationalDigits.length > 0) {
            formatted += nationalDigits.slice(0, 2);
        }

        if (nationalDigits.length > 2) {
            formatted += ' ' + nationalDigits.slice(2, 5);
        }

        if (nationalDigits.length > 5) {
            formatted += ' ' + nationalDigits.slice(5, 7);
        }

        if (nationalDigits.length > 7) {
            formatted += ' ' + nationalDigits.slice(7, 9);
        }

        return formatted;
    }

    function applyPhoneValue(value) {
        const formatted = formatPhoneDisplay(value);
        phoneInput.value = formatted;
        phoneInput.setSelectionRange(formatted.length, formatted.length);
    }

    function normalizePhone(value) {
        const national = getNationalDigits(value);

        if (national.length !== 9) {
            return '';
        }

        return '+380' + national;
    }

    function cleanUsername(value) {
        if (!value) return '';
        value = value.trim();

        if (value.startsWith('http')) {
            try {
                const url = new URL(value);
                return url.pathname.replace(/\//g, '');
            } catch (e) {
                return '';
            }
        }

        return value.replace(/^@+/, '');
    }

    function formatTelegram(value) {
        const username = cleanUsername(value);
        return username ? '@' + username : '';
    }

    function formatInstagramLink(value) {
        const username = cleanUsername(value);
        return username ? 'https://www.instagram.com/' + username : '-';
    }

    function sendToTelegram(data) {
        const telegram = formatTelegram(data['your-telegram']);
        const instagramLink = formatInstagramLink(data['your-instagram']);
        const ringSizeLine = needsRingSize
            ? buildRingSizeLine(data['ring-size'])
            : '';
        const coating = data.coating || 'Без';
        const coatingLine = coating !== 'Без'
            ? '🎨 Покриття: ' + coating + '\n⚠️ Обрано покриття — ціна може змінитися'
            : '🎨 Покриття: Без';

        const message = [
            '💎 Нове замовлення з сайту KNŌT JEWELRY:',
            '',
            '👤 Ім\'я: ' + data['full-name'],
            '',
            '📞 Телефон: ' + data['your-phone'],
            '💬 Telegram: ' + (telegram || '-'),
            '📷 Instagram: ' + (instagramLink || '-'),
            '',
            '📝 Коментар:',
            data['your-message'] || '-',
            '',
            '💍 Товар: ' + data['product-title'],
            '💰 Ціна: ' + (data['product-price'] || '-'),
            ringSizeLine,
            coatingLine,
            '⚙️ Матеріал: ' + (data['product-material'] || '-'),
            '💎 Камінь: ' + (data['product-stone'] || '-'),
            '📦 Тип: ' + (data['product-type'] || '-'),
            '',
            '🔗 Посилання: ' + data['product-link'],
        ].filter(Boolean).join('\n');

        const url = 'https://api.telegram.org/bot' + config.telegramBotToken + '/sendMessage';

        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                chat_id: config.telegramChatId,
                text: message,
            }),
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (result) {
                if (!result.ok) {
                    throw new Error(result.description || 'Telegram error');
                }

                return result;
            });
    }

    function buildRingSizeLine(size) {
        const lines = ['📏 Розмір: ' + (size || '-')];

        if (parseFloat(size) > 19) {
            lines.push('⚠️⚠️⚠️ Розмір > 19 ⚠️⚠️⚠️');
        }

        return lines.join('\n');
    }

    function clearErrors() {
        form.querySelectorAll('.field-error').forEach(function (el) {
            el.textContent = '';
        });

        form.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });

        if (alertBox) {
            alertBox.hidden = true;
            alertBox.textContent = '';
        }
    }

    function showErrors(errors) {
        Object.keys(errors).forEach(function (fieldName) {
            const errorEl = form.querySelector('[data-error-for="' + fieldName + '"]');
            const input = form.querySelector('[name="' + fieldName + '"]');

            if (errorEl) {
                errorEl.textContent = errors[fieldName];
            }

            if (input) {
                input.classList.add('is-invalid');
            }
        });

        const firstInvalid = form.querySelector('.is-invalid, [data-error-for]:not(:empty)');
        if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function showAlert(message) {
        if (!alertBox) return;

        alertBox.textContent = message;
        alertBox.hidden = false;
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function setSubmitting(state) {
        if (!submitBtn) return;

        submitBtn.disabled = state;
        submitBtn.textContent = state ? 'Надсилаємо…' : 'Надіслати замовлення';
    }
});
