document.addEventListener('DOMContentLoaded', function () {
	if (typeof knotRepairForm === 'undefined') return;

	const form = document.getElementById('repair-order-form');
	if (!form) return;

	const config = knotRepairForm;
	const alertBox = document.getElementById('repair-form-alert');
	const submitBtn = document.getElementById('repair-form-submit');
	const phoneInput = document.getElementById('repair-phone');

	const LAST_SEND_KEY = 'knot_last_repair_send';
	let formStartTime = Date.now();
	let userInteracted = false;
	let isSubmitting = false;

	const PHONE_PREFIX = '+380 ';
	const NAME_REGEX = /^[\p{L}][\p{L}'-]{1,49}(?:\s+[\p{L}][\p{L}'-]{1,49})*$/u;
	const PHONE_REGEX = /^\+380\d{9}$/;
	const TELEGRAM_REGEX = /^[a-zA-Z0-9_]{5,32}$/;
	const INSTAGRAM_REGEX = /^[a-zA-Z0-9._]{1,30}$/;

	['mousemove', 'scroll', 'keydown', 'touchstart'].forEach(function (evt) {
		document.addEventListener(evt, function () { userInteracted = true; }, { once: true, passive: true });
	});

	// Reset the "too fast" timer whenever the popup is opened / advanced to step 2.
	document.addEventListener('click', function (event) {
		if (event.target.closest('[data-action="togglePopup"][data-target="#repair_popup"]')) formStartTime = Date.now();
		if (event.target.closest('#repair_popup [data-action="nextStep"]')) formStartTime = Date.now();
	});

	if (phoneInput) {
		phoneInput.addEventListener('focus', function () {
			if (!phoneInput.value) phoneInput.value = PHONE_PREFIX;
		});
		phoneInput.addEventListener('input', function () { formatPhoneDisplay(phoneInput.value); });
		phoneInput.addEventListener('blur', function () {
			if (phoneInput.value === PHONE_PREFIX) phoneInput.value = '';
		});
	}

	form.addEventListener('submit', function (event) {
		event.preventDefault();
		if (isSubmitting) return;

		clearErrors();

		const data = collectFormData();
		const errors = validateForm(data);
		if (Object.keys(errors).length) { showErrors(errors); return; }

		const spamError = validateAntiSpam(data);
		if (spamError) { showAlert(spamError); return; }

		isSubmitting = true;
		setSubmitting(true);

		sendToTelegram(data)
			.then(function () {
				localStorage.setItem(LAST_SEND_KEY, String(Date.now()));
				if (config.thankYouUrl) {
					window.location.href = config.thankYouUrl;
				} else {
					showAlert('Дякуємо! Заявку надіслано.');
					form.reset();
				}
			})
			.catch(function () {
				showAlert('Не вдалося надіслати заявку. Спробуйте ще раз або напишіть нам у соцмережах.');
			})
			.finally(function () {
				isSubmitting = false;
				setSubmitting(false);
			});
	});

	/* -------------------- DATA -------------------- */

	function valueOf(name) {
		const el = form.querySelector('[name="' + name + '"]');
		return el ? el.value.trim() : '';
	}

	function collectFormData() {
		return {
			'full-name': valueOf('full-name'),
			'your-phone': normalizePhone(valueOf('your-phone')),
			'your-telegram': valueOf('your-telegram'),
			'your-instagram': valueOf('your-instagram'),
			'your-message': valueOf('your-message'),
			'privacy-policy': form.querySelector('[name="privacy-policy"]') && form.querySelector('[name="privacy-policy"]').checked ? '1' : '',
			website: valueOf('website'),
			'math-check': valueOf('math-check'),
			'page-link': window.location.href,
		};
	}

	/* -------------------- VALIDATION -------------------- */

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

		if (data['your-telegram'] && !TELEGRAM_REGEX.test(cleanUsername(data['your-telegram']))) {
			errors['your-telegram'] = 'Некоректний Telegram username';
		}

		if (data['your-instagram'] && !INSTAGRAM_REGEX.test(cleanUsername(data['your-instagram']))) {
			errors['your-instagram'] = 'Некоректний Instagram username';
		}

		if (data['your-message'].length > 1000) {
			errors['your-message'] = 'Коментар занадто довгий (максимум 1000 символів)';
		}

		if (!data['privacy-policy']) {
			errors['privacy-policy'] = 'Потрібна згода з <a href="' + (config.privacyUrl || '/privacy-policy/') + '" target="_blank" rel="noopener noreferrer">правилами</a>';
		}

		return errors;
	}

	function validateAntiSpam(data) {
		if (data.website) return 'Помилка відправки. Спробуйте ще раз.';
		if (data['math-check']) return 'Помилка відправки. Спробуйте ще раз.';
		if (Date.now() - formStartTime < (Number(config.minFormTime) || 2500)) return 'Зачекайте кілька секунд перед відправкою.';
		if (!userInteracted) return 'Помилка відправки. Спробуйте ще раз.';

		const lastSend = localStorage.getItem(LAST_SEND_KEY);
		if (lastSend && Date.now() - Number(lastSend) < (Number(config.resendDelay) || 10000)) {
			return 'Зачекайте трохи перед повторною відправкою.';
		}
		return '';
	}

	/* -------------------- PHONE / USERNAMES -------------------- */

	function getNationalDigits(value) {
		let digits = String(value || '').replace(/\D/g, '');
		if (digits.startsWith('380')) digits = digits.slice(3);
		else if (digits.startsWith('0')) digits = digits.slice(1);
		return digits.slice(0, 9);
	}

	function formatPhoneDisplay(value) {
		const d = getNationalDigits(value);
		let out = PHONE_PREFIX;
		if (d.length > 0) out += d.slice(0, 2);
		if (d.length > 2) out += ' ' + d.slice(2, 5);
		if (d.length > 5) out += ' ' + d.slice(5, 7);
		if (d.length > 7) out += ' ' + d.slice(7, 9);
		phoneInput.value = out;
		phoneInput.setSelectionRange(out.length, out.length);
	}

	function normalizePhone(value) {
		const national = getNationalDigits(value);
		return national.length === 9 ? '+380' + national : '';
	}

	function cleanUsername(value) {
		if (!value) return '';
		value = value.trim();
		if (value.startsWith('http')) {
			try { return new URL(value).pathname.replace(/\//g, ''); } catch (e) { return ''; }
		}
		return value.replace(/^@+/, '');
	}

	function formatTelegram(value) {
		const u = cleanUsername(value);
		return u ? '@' + u : '';
	}

	function formatInstagramLink(value) {
		const u = cleanUsername(value);
		return u ? 'https://www.instagram.com/' + u : '';
	}

	/* -------------------- SEND -------------------- */

	function sendToTelegram(data) {
		const telegram = formatTelegram(data['your-telegram']);
		const instagramLink = formatInstagramLink(data['your-instagram']);

		const message = [
			'🛠 Нова заявка на реставрацію — KNŌT JEWELRY:',
			'',
			'👤 Ім\'я: ' + data['full-name'],
			'📞 Телефон: ' + data['your-phone'],
			'💬 Telegram: ' + (telegram || '-'),
			'📷 Instagram: ' + (instagramLink || '-'),
			'',
			'📝 Коментар:',
			data['your-message'] || '-',
			'',
			'🔗 Сторінка: ' + data['page-link'],
		].filter(Boolean).join('\n');

		const url = 'https://api.telegram.org/bot' + config.telegramBotToken + '/sendMessage';

		return fetch(url, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ chat_id: config.telegramChatId, text: message }),
		})
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (!res.ok) throw new Error(res.description || 'Telegram error');
				return res;
			});
	}

	/* -------------------- UI -------------------- */

	function clearErrors() {
		form.querySelectorAll('.field-error').forEach(function (el) { el.innerHTML = ''; });
		form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
		if (alertBox) { alertBox.hidden = true; alertBox.textContent = ''; }
	}

	function showErrors(errors) {
		Object.keys(errors).forEach(function (name) {
			const errorEl = form.querySelector('[data-error-for="' + name + '"]');
			const input = form.querySelector('[name="' + name + '"]');
			// Messages are static, developer-controlled strings; privacy carries a link.
			if (errorEl) errorEl.innerHTML = errors[name];
			if (input) input.classList.add('is-invalid');
		});
		const firstInvalid = form.querySelector('.is-invalid');
		if (firstInvalid) firstInvalid.focus();
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
		submitBtn.textContent = state ? 'Надсилаємо…' : 'Надіслати заявку';
	}
});
