(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		const config = window.knotCart || {};

		const STORAGE_KEY = 'knot_cart';
		const LAST_SEND_KEY = 'knot_last_order_send';
		const CURRENCY = config.currency || '₴';
		const MAX_ITEMS = Number(config.maxItems) || 50;
		const MAX_QTY = Number(config.maxQty) || 99;

		const drawer = document.getElementById('cart-drawer');
		if (!drawer) return;

		const RING_SIZES = Array.isArray(config.ringSizes) ? config.ringSizes : [];

		const body = document.body;
		const main = document.querySelector('main');
		const itemsEl = drawer.querySelector('[data-cart-items]');
		const emptyEl = drawer.querySelector('[data-cart-empty]');
		const footEl = drawer.querySelector('[data-cart-foot]');
		const totalEl = drawer.querySelector('[data-cart-total]');
		const checkoutBtn = drawer.querySelector('[data-cart-goto="intro"]');
		const hintEl = drawer.querySelector('[data-cart-hint]');
		const steps = drawer.querySelectorAll('[data-step]');
		const stepForm = drawer.querySelector('[data-step="form"]');
		const countEls = document.querySelectorAll('[data-cart-count]');

		const form = document.getElementById('cart-order-form');
		const alertBox = document.getElementById('cart-order-form-alert');
		const submitBtn = document.getElementById('cart-order-form-submit');
		const phoneInput = document.getElementById('cart-phone');

		const PHONE_PREFIX = '+380 ';
		const PHONE_PREFIX_LEN = PHONE_PREFIX.length;
		const NAME_REGEX = /^[\p{L}][\p{L}'-]{1,49}(?:\s+[\p{L}][\p{L}'-]{1,49})*$/u;
		const PHONE_REGEX = /^\+380\d{9}$/;
		const TELEGRAM_REGEX = /^[a-zA-Z0-9_]{5,32}$/;
		const INSTAGRAM_REGEX = /^[a-zA-Z0-9._]{1,30}$/;

		let state = readState();
		let formStartTime = Date.now();
		let userInteracted = false;
		let isSubmitting = false;

		['mousemove', 'scroll', 'keydown', 'touchstart'].forEach(function (evt) {
			document.addEventListener(evt, function () {
				userInteracted = true;
			}, { once: true });
		});

		render();

		/* ---------------- STATE ---------------- */

		function readState() {
			let raw;
			try {
				raw = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
			} catch (e) {
				raw = [];
			}

			if (!Array.isArray(raw)) return [];

			return raw
				.map(normalizeItem)
				.filter(Boolean)
				.slice(0, MAX_ITEMS);
		}

		function normalizeItem(item) {
			if (!item || typeof item !== 'object') return null;

			const id = String(item.id || '').replace(/[^\d]/g, '');
			if (!id) return null;

			const qty = clampQty(parseInt(item.qty, 10) || 1);
			const price = toInt(item.price);
			const needsSize = item.needsSize === true || item.needsSize === '1' || item.needsSize === 1;

			return {
				uid: String(item.uid || '') || makeUid(),
				id: id,
				title: String(item.title || '').slice(0, 200),
				price: price,
				link: safeUrl(item.link),
				image: safeUrl(item.image),
				material: String(item.material || '').slice(0, 120),
				stone: String(item.stone || '').slice(0, 120),
				type: String(item.type || '').slice(0, 120),
				needsSize: needsSize,
				size: String(item.size || '').slice(0, 20),
				qty: qty
			};
		}

		function makeUid() {
			return Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
		}

		function persist() {
			try {
				localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
			} catch (e) {
				/* storage full or blocked — ignore */
			}
		}

		function findByUid(uid) {
			return state.find(function (i) {
				return i.uid === uid;
			});
		}

		function addItem(data) {
			const item = normalizeItem(data);
			if (!item) return false;

			if (item.needsSize) {
				// If a line for this ring is still waiting for a size, don't stack
				// another empty line — let the user pick the size there first.
				const pending = state.find(function (i) {
					return i.needsSize && i.id === item.id && !i.size;
				});
				if (pending) {
					render();
					return true;
				}
			} else {
				// Products without size merge by id.
				const existing = state.find(function (i) {
					return !i.needsSize && i.id === item.id;
				});
				if (existing) {
					existing.qty = clampQty(existing.qty + item.qty);
					persist();
					render();
					return true;
				}
			}

			if (state.length >= MAX_ITEMS) return false;
			state.push(item);
			persist();
			render();
			return true;
		}

		function removeItem(uid) {
			state = state.filter(function (i) {
				return i.uid !== uid;
			});
			persist();
			render();
		}

		function changeQty(uid, delta) {
			const item = findByUid(uid);
			if (!item) return;
			item.qty = clampQty(item.qty + delta);
			persist();
			render();
		}

		function setSize(uid, size) {
			const item = findByUid(uid);
			if (!item) return;

			const newSize = String(size || '').slice(0, 20);

			// Same ring + same size already in cart → merge into it.
			const twin = newSize && state.find(function (i) {
				return i.uid !== uid && i.needsSize && i.id === item.id && i.size === newSize;
			});

			if (twin) {
				twin.qty = clampQty(twin.qty + item.qty);
				state = state.filter(function (i) {
					return i.uid !== uid;
				});
			} else {
				item.size = newSize;
			}

			persist();
			render();
		}

		function hasUnsizedRings() {
			return state.some(function (i) {
				return i.needsSize && !i.size;
			});
		}

		function clearCart() {
			state = [];
			persist();
			render();
		}

		function totalSum() {
			return state.reduce(function (sum, i) {
				return sum + i.price * i.qty;
			}, 0);
		}

		function totalCount() {
			return state.reduce(function (sum, i) {
				return sum + i.qty;
			}, 0);
		}

		/* ---------------- RENDER ---------------- */

		function render() {
			renderItems();
			renderCount();
		}

		function renderCount() {
			const count = totalCount();
			countEls.forEach(function (el) {
				el.textContent = String(count);
				el.hidden = count === 0;
			});
		}

		function renderItems() {
			if (!itemsEl) return;

			itemsEl.textContent = '';

			const isEmpty = state.length === 0;
			if (emptyEl) emptyEl.hidden = !isEmpty;
			if (footEl) footEl.hidden = isEmpty;

			if (isEmpty) return;

			const fragment = document.createDocumentFragment();
			state.forEach(function (item) {
				fragment.appendChild(buildItem(item));
			});
			itemsEl.appendChild(fragment);

			if (totalEl) totalEl.textContent = formatMoney(totalSum());

			const blocked = hasUnsizedRings();
			if (checkoutBtn) {
				checkoutBtn.disabled = blocked;
				checkoutBtn.classList.toggle('is-disabled', blocked);
			}
			if (hintEl) hintEl.hidden = !blocked;
		}

		function buildItem(item) {
			const li = document.createElement('li');
			li.className = 'cart-item';
			li.dataset.key = item.uid;

			// media
			const media = document.createElement('div');
			media.className = 'cart-item__media';
			if (item.image) {
				const img = document.createElement('img');
				img.src = item.image;
				img.alt = item.title;
				img.loading = 'lazy';
				media.appendChild(img);
			}
			li.appendChild(media);

			// body
			const bodyEl = document.createElement('div');
			bodyEl.className = 'cart-item__body';

			const title = document.createElement('a');
			title.className = 'cart-item__title';
			title.href = item.link || '#';
			title.textContent = item.title;
			bodyEl.appendChild(title);

			if (item.needsSize) {
				bodyEl.appendChild(buildSize(item));
			} else if (item.size) {
				const meta = document.createElement('span');
				meta.className = 'cart-item__meta';
				meta.textContent = 'Розмір: ' + item.size;
				bodyEl.appendChild(meta);
			}

			bodyEl.appendChild(buildQty(item));
			li.appendChild(bodyEl);

			// side
			const side = document.createElement('div');
			side.className = 'cart-item__side';

			const sum = document.createElement('span');
			sum.className = 'cart-item__sum';
			sum.textContent = formatMoney(item.price * item.qty);
			side.appendChild(sum);

			// Per-unit price only makes sense when there is more than one piece.
			if (item.qty > 1) {
				const price = document.createElement('span');
				price.className = 'cart-item__price';
				price.textContent = formatMoney(item.price) + ' / шт';
				side.appendChild(price);
			}

			const remove = document.createElement('button');
			remove.type = 'button';
			remove.className = 'cart-item__remove';
			remove.dataset.cartRemove = '';
			remove.textContent = 'Видалити';
			side.appendChild(remove);

			li.appendChild(side);
			return li;
		}

		function buildQty(item) {
			const wrap = document.createElement('div');
			wrap.className = 'cart-item__qty';

			const dec = document.createElement('button');
			dec.type = 'button';
			dec.dataset.cartDec = '';
			dec.setAttribute('aria-label', 'Зменшити');
			dec.textContent = '−';

			const val = document.createElement('span');
			val.textContent = String(item.qty);

			const inc = document.createElement('button');
			inc.type = 'button';
			inc.dataset.cartInc = '';
			inc.setAttribute('aria-label', item.needsSize ? 'Додати інший розмір' : 'Збільшити');
			if (item.needsSize) inc.title = 'Додати інший розмір';
			inc.textContent = '+';

			wrap.appendChild(dec);
			wrap.appendChild(val);
			wrap.appendChild(inc);
			return wrap;
		}

		function buildSize(item) {
			const wrap = document.createElement('div');
			wrap.className = 'cart-item__size';

			const select = document.createElement('select');
			select.className = 'cart-item__size-select';
			select.dataset.cartSizeSelect = '';
			if (!item.size) select.classList.add('is-invalid');

			const placeholder = document.createElement('option');
			placeholder.value = '';
			placeholder.textContent = 'Оберіть розмір';
			select.appendChild(placeholder);

			RING_SIZES.forEach(function (size) {
				const opt = document.createElement('option');
				opt.value = size;
				opt.textContent = size;
				if (String(size) === item.size) opt.selected = true;
				select.appendChild(opt);
			});

			wrap.appendChild(select);

			if (item.size && parseFloat(item.size) > 19) {
				const note = document.createElement('span');
				note.className = 'cart-item__note';
				note.textContent = '⚠️ Розмір понад 19 — ціна може змінитися.';
				wrap.appendChild(note);
			}

			return wrap;
		}

		/* ---------------- DRAWER ---------------- */

		function openDrawer() {
			showStep('cart');
			drawer.classList.add('is-open');
			drawer.setAttribute('aria-hidden', 'false');
			body.classList.add('overflow');
			if (main) main.classList.add('ev-none');

			// iOS Safari caches hit-testing for controls built while the drawer
			// was hidden (pointer-events: none) — rebuild them now that it's
			// visible and interactive so selects are tappable on first try.
			renderItems();
		}

		function closeDrawer() {
			drawer.classList.remove('is-open');
			drawer.setAttribute('aria-hidden', 'true');
			body.classList.remove('overflow');
			if (main) main.classList.remove('ev-none');
		}

		function showStep(name) {
			// Leaving the cart requires a non-empty cart with every ring sized.
			if (name !== 'cart' && (state.length === 0 || hasUnsizedRings())) {
				name = 'cart';
			}

			steps.forEach(function (el) {
				el.classList.toggle('is-active', el.dataset.step === name);
			});

			if (name === 'intro') {
				formStartTime = Date.now();
			}

			if (name === 'form' && stepForm) {
				const first = stepForm.querySelector('input, textarea');
				if (first) first.focus();
			}
		}

		/* ---------------- EVENTS ---------------- */

		document.addEventListener('click', function (event) {
			const target = event.target;

			if (target.closest('[data-cart-toggle]')) {
				event.preventDefault();
				openDrawer();
				return;
			}

			if (target.closest('[data-cart-close]')) {
				closeDrawer();
				return;
			}

			const addBtn = target.closest('[data-cart-add]');
			if (addBtn) {
				event.preventDefault();
				handleAdd(addBtn);
				return;
			}

			const gotoBtn = target.closest('[data-cart-goto]');
			if (gotoBtn) {
				showStep(gotoBtn.dataset.cartGoto);
				return;
			}

			const line = target.closest('.cart-item');
			if (!line) return;

			const key = line.dataset.key;
			if (target.closest('[data-cart-remove]')) {
				removeItem(key);
			} else if (target.closest('[data-cart-inc]')) {
				const item = findByUid(key);
				// For rings, "+" spawns a new line with its own size choice
				// instead of increasing the quantity of this exact size.
				if (item && item.needsSize) {
					addSizeVariant(item);
				} else {
					changeQty(key, 1);
				}
			} else if (target.closest('[data-cart-dec]')) {
				changeQty(key, -1);
			}
		});

		document.addEventListener('change', function (event) {
			const select = event.target.closest('[data-cart-size-select]');
			if (!select) return;

			const line = select.closest('.cart-item');
			if (line) setSize(line.dataset.key, select.value);
		});

		document.addEventListener('keydown', function (event) {
			if ((event.key === 'Escape' || event.key === 'Esc') && drawer.classList.contains('is-open')) {
				closeDrawer();
			}
		});

		function handleAdd(btn) {
			const added = addItem({
				id: btn.dataset.id,
				title: btn.dataset.title,
				price: btn.dataset.price,
				link: btn.dataset.link,
				image: btn.dataset.image,
				material: btn.dataset.material,
				stone: btn.dataset.stone,
				type: btn.dataset.type,
				needsSize: btn.dataset.needsSize === '1',
				size: '',
				qty: 1
			});

			if (!added) return;

			openDrawer();
			showStep('cart');
			focusPendingSize();
		}

		function addSizeVariant(item) {
			const added = addItem({
				id: item.id,
				title: item.title,
				price: item.price,
				link: item.link,
				image: item.image,
				material: item.material,
				stone: item.stone,
				type: item.type,
				needsSize: true,
				size: '',
				qty: 1
			});

			if (added) focusPendingSize();
		}

		function focusPendingSize() {
			// Just scroll the pending ring into view — no programmatic focus(),
			// which on iOS Safari leaves the <select> needing an extra tap.
			const pending = itemsEl && itemsEl.querySelector('.cart-item__size-select.is-invalid');
			if (pending) pending.scrollIntoView({ block: 'nearest' });
		}

		/* ---------------- CHECKOUT ---------------- */

		if (phoneInput) attachPhoneMask(phoneInput);

		if (form) {
			form.addEventListener('submit', function (event) {
				event.preventDefault();
				if (isSubmitting) return;

				clearErrors();

				if (state.length === 0) {
					showAlert('Ваш кошик порожній.');
					showStep('cart');
					return;
				}

				if (hasUnsizedRings()) {
					showStep('cart');
					return;
				}

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
						clearCart();
						window.location.href = config.thankYouUrl || '/';
					})
					.catch(function () {
						showAlert('Не вдалося надіслати замовлення. Спробуйте ще раз або напишіть нам у соцмережах.');
					})
					.finally(function () {
						isSubmitting = false;
						setSubmitting(false);
					});
			});
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
				'math-check': valueOf('math-check')
			};
		}

		function valueOf(name) {
			const el = form.querySelector('[name="' + name + '"]');
			return el ? el.value.trim() : '';
		}

		function validateForm(data) {
			const errors = {};

			if (!data['full-name']) {
				errors['full-name'] = 'Вкажіть ім\'я';
			} else if (data['full-name'].length > 100 || !NAME_REGEX.test(data['full-name'])) {
				errors['full-name'] = 'Вкажіть коректне ім\'я (мінімум 2 символи, лише літери)';
			}

			if (!data['your-phone']) {
				errors['your-phone'] = 'Вкажіть номер телефону';
			} else if (!PHONE_REGEX.test(data['your-phone'])) {
				errors['your-phone'] = 'Використовуйте формат +380 XX XXX XX XX';
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
			if (data.website || data['math-check']) {
				return 'Помилка відправки. Спробуйте ще раз.';
			}

			if (Date.now() - formStartTime < (Number(config.minFormTime) || 2500)) {
				return 'Зачекайте кілька секунд перед відправкою.';
			}

			if (!userInteracted) {
				return 'Помилка відправки. Спробуйте ще раз.';
			}

			const lastSend = localStorage.getItem(LAST_SEND_KEY);
			if (lastSend && Date.now() - Number(lastSend) < (Number(config.resendDelay) || 10000)) {
				return 'Зачекайте трохи перед повторною відправкою.';
			}

			return '';
		}

		/* ---------------- TELEGRAM ---------------- */

		function sendToTelegram(data) {
			const token = config.telegramBotToken;
			const chatId = config.telegramChatId;
			if (!token || !chatId) {
				return Promise.reject(new Error('No telegram config'));
			}

			const telegram = formatTelegram(data['your-telegram']);
			const instagram = formatInstagramLink(data['your-instagram']);

			const lines = [
				'🛒 Нове замовлення з КОШИКА — KNŌT JEWELRY:',
				'👤 Ім\'я: ' + data['full-name'],
				'📞 Телефон: ' + data['your-phone'],
				'💬 Telegram: ' + (telegram || '-'),
				'📷 Instagram: ' + (instagram || '-'),
				'📝 Коментар:',
				data['your-message'] || '-',
				'',
				'Позиції:'
			];

			state.forEach(function (item, index) {
				lines.push((index + 1) + ') 💍 Товар: ' + item.title);
				lines.push('💰 Ціна: ' + formatMoney(item.price));

				if (item.qty > 1) {
					lines.push('🔢 Кількість: ' + item.qty + ' шт');
					lines.push('🧮 Сума: ' + formatMoney(item.price * item.qty));
				}

				if (item.size) {
					lines.push('📏 Розмір: ' + item.size);
					if (parseFloat(item.size) > 19) {
						lines.push('⚠️ Розмір > 19 — ціна може змінитися');
					}
				}

				lines.push('⚙️ Матеріал: ' + (item.material || '-'));
				lines.push('💎 Камінь: ' + (item.stone || '-'));
				lines.push('📦 Тип: ' + (item.type || '-'));
				if (item.link) lines.push('🔗 Посилання: ' + item.link);
				lines.push('');
			});

			lines.push('💰 Разом: ' + formatMoney(totalSum()));

			return fetch('https://api.telegram.org/bot' + token + '/sendMessage', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ chat_id: chatId, text: lines.join('\n') })
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (result) {
					if (!result.ok) throw new Error(result.description || 'Telegram error');
					return result;
				});
		}

		/* ---------------- PHONE MASK ---------------- */

		function attachPhoneMask(input) {
			input.addEventListener('focus', function () {
				if (!getNationalDigits(input.value)) {
					input.value = PHONE_PREFIX;
					input.setSelectionRange(PHONE_PREFIX_LEN, PHONE_PREFIX_LEN);
				}
			});

			input.addEventListener('keydown', function (event) {
				const start = input.selectionStart;
				const end = input.selectionEnd;
				if (
					(event.key === 'Backspace' && start <= PHONE_PREFIX_LEN && end <= PHONE_PREFIX_LEN) ||
					(event.key === 'Delete' && start < PHONE_PREFIX_LEN)
				) {
					event.preventDefault();
				}
			});

			input.addEventListener('paste', function (event) {
				event.preventDefault();
				const pasted = (event.clipboardData || window.clipboardData).getData('text') || '';
				applyPhoneValue(input, pasted);
			});

			input.addEventListener('input', function () {
				applyPhoneValue(input, input.value);
			});

			input.addEventListener('blur', function () {
				if (!getNationalDigits(input.value)) input.value = '';
			});
		}

		function applyPhoneValue(input, value) {
			const formatted = formatPhoneDisplay(value);
			input.value = formatted;
			input.setSelectionRange(formatted.length, formatted.length);
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

		function formatPhoneDisplay(value) {
			const d = getNationalDigits(value);
			let out = PHONE_PREFIX;
			if (d.length > 0) out += d.slice(0, 2);
			if (d.length > 2) out += ' ' + d.slice(2, 5);
			if (d.length > 5) out += ' ' + d.slice(5, 7);
			if (d.length > 7) out += ' ' + d.slice(7, 9);
			return out;
		}

		function normalizePhone(value) {
			const national = getNationalDigits(value);
			return national.length === 9 ? '+380' + national : '';
		}

		/* ---------------- HELPERS ---------------- */

		function cleanUsername(value) {
			if (!value) return '';
			value = value.trim();
			if (value.startsWith('http')) {
				try {
					return new URL(value).pathname.replace(/\//g, '');
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
			return username ? 'https://www.instagram.com/' + username : '';
		}

		function formatMoney(n) {
			const value = Math.round(Number(n) || 0);
			return value.toLocaleString('uk-UA') + ' ' + CURRENCY;
		}

		function toInt(value) {
			const n = parseInt(String(value == null ? '' : value).replace(/[^\d]/g, ''), 10);
			return Number.isFinite(n) ? n : 0;
		}

		function clampQty(qty) {
			if (!Number.isFinite(qty) || qty < 1) return 1;
			return Math.min(qty, MAX_QTY);
		}

		function safeUrl(value) {
			const str = String(value || '').trim();
			return /^https?:\/\//i.test(str) ? str : '';
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
			Object.keys(errors).forEach(function (name) {
				const errorEl = form.querySelector('[data-error-for="' + name + '"]');
				const input = form.querySelector('[name="' + name + '"]');
				if (errorEl) errorEl.textContent = errors[name];
				if (input) input.classList.add('is-invalid');
			});
			const firstInvalid = form.querySelector('.is-invalid');
			if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}

		function showAlert(message) {
			if (!alertBox) return;
			alertBox.textContent = message;
			alertBox.hidden = false;
			alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}

		function setSubmitting(stateFlag) {
			if (!submitBtn) return;
			submitBtn.disabled = stateFlag;
			submitBtn.textContent = stateFlag ? 'Надсилаємо…' : 'Надіслати замовлення';
		}
	});
})();
