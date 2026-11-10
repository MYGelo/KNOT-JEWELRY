/* --------------------------------------------------------------------------
 * Lead source attribution — used by the order / cart / remade Telegram messages.
 * Captures the first-touch traffic source once per session and exposes helpers
 * the forms call at submit time. All storage access is guarded (private mode).
 * -------------------------------------------------------------------------- */
(function () {
	const KEY = 'knot_src';
	function ss(get, val) {
		try { return get ? sessionStorage.getItem(KEY) : sessionStorage.setItem(KEY, val); }
		catch (e) { return null; }
	}

	// First touch: remember the earliest campaign/referrer signal of the session.
	try {
		if (!ss(true)) {
			const fp = new URLSearchParams(window.location.search);
			const sig = {
				source: fp.get('utm_source') || '',
				medium: fp.get('utm_medium') || '',
				campaign: fp.get('utm_campaign') || '',
				content: fp.get('utm_content') || '',
				fbclid: fp.get('fbclid') ? '1' : '',
				referrer: document.referrer || ''
			};
			if (sig.source || sig.fbclid || sig.referrer) { ss(false, JSON.stringify(sig)); }
		}
	} catch (e) {}

	function prettyNet(src) {
		src = (src || '').toLowerCase();
		if (/insta|(^|[^a-z])ig([^a-z]|$)/.test(src)) return 'Instagram';
		if (/face|(^|[^a-z])fb([^a-z]|$)/.test(src)) return 'Facebook';
		if (/google|adwords|gads/.test(src)) return 'Google';
		if (/telegram|t\.me|(^|[^a-z])tg([^a-z]|$)/.test(src)) return 'Telegram';
		if (/tiktok|(^|[^a-z])tt([^a-z]|$)/.test(src)) return 'TikTok';
		if (/youtube|(^|[^a-z])yt([^a-z]|$)/.test(src)) return 'YouTube';
		return src ? src.charAt(0).toUpperCase() + src.slice(1) : '';
	}

	// Human string like "Instagram · social · link_in_bio" or "Прямий захід".
	window.knotLeadSource = function () {
		let s;
		try {
			const p = new URLSearchParams(window.location.search);
			s = {
				source: p.get('utm_source') || '',
				medium: p.get('utm_medium') || '',
				campaign: p.get('utm_campaign') || '',
				content: p.get('utm_content') || '',
				fbclid: p.get('fbclid') ? '1' : '',
				referrer: document.referrer || ''
			};
			if (!s.source && !s.fbclid) {
				const saved = JSON.parse(ss(true) || '{}');
				if (saved && (saved.source || saved.fbclid || saved.referrer)) {
					s.source = saved.source || s.source;
					s.medium = saved.medium || s.medium;
					s.campaign = saved.campaign || s.campaign;
					s.content = saved.content || s.content;
					s.fbclid = saved.fbclid || s.fbclid;
					s.referrer = saved.referrer || s.referrer;
				}
			}
		} catch (e) { s = {}; }

		let name = prettyNet(s.source);
		if (!name && s.fbclid) name = 'Facebook / Instagram';
		if (!name && s.referrer) {
			try {
				const host = new URL(s.referrer).hostname.replace(/^www\./, '');
				const self = location.hostname.replace(/^www\./, '');
				if (host && host.indexOf(self) === -1) {
					if (/instagram/.test(host)) name = 'Instagram';
					else if (/facebook|fb\.com/.test(host)) name = 'Facebook';
					else if (/google/.test(host)) name = 'Google';
					else if (/t\.me|telegram/.test(host)) name = 'Telegram';
					else name = host;
				}
			} catch (e) {}
		}
		if (!name) return 'Прямий захід';

		const extra = [];
		if (s.medium) extra.push(s.medium);
		if (s.campaign) extra.push(s.campaign);
		if (s.content) extra.push(s.content);
		return name + (extra.length ? ' · ' + extra.join(' · ') : '');
	};

	// Splits a long message on line boundaries so each part fits Telegram's limit.
	function splitForTelegram(text, max) {
		if (!text || text.length <= max) { return [text || '']; }
		var out = [];
		var cur = '';
		text.split('\n').forEach(function (line) {
			while (line.length > max) {
				if (cur) { out.push(cur); cur = ''; }
				out.push(line.slice(0, max));
				line = line.slice(max);
			}
			if (cur && cur.length + 1 + line.length > max) { out.push(cur); cur = line; }
			else { cur = cur ? cur + '\n' + line : line; }
		});
		if (cur) { out.push(cur); }
		return out;
	}

	// Sends a Telegram message, splitting it into "Частина N/M" parts when it is
	// longer than Telegram's 4096-char limit. Resolves when every part is sent;
	// rejects (like a single send) if any part fails.
	window.knotSendTelegram = function (token, chatId, text) {
		var url = 'https://api.telegram.org/bot' + token + '/sendMessage';
		var parts = splitForTelegram(text, 3900);
		var total = parts.length;
		if (total > 1) {
			parts = parts.map(function (part, i) { return '📩 Частина ' + (i + 1) + '/' + total + '\n' + part; });
		}
		return parts.reduce(function (chain, part) {
			return chain.then(function () {
				return fetch(url, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ chat_id: chatId, text: part })
				})
					.then(function (r) { return r.json(); })
					.then(function (res) {
						if (!res.ok) { throw new Error(res.description || 'Telegram error'); }
						return res;
					});
			});
		}, Promise.resolve());
	};

	// Strips tracking params so a product link stays clean but clickable.
	window.knotCleanLink = function (url) {
		try {
			const u = new URL(url);
			['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'gclid', 'yclid', 'i', 'aem'].forEach(function (k) { u.searchParams.delete(k); });
			const qs = u.searchParams.toString();
			return u.origin + u.pathname + (qs ? '?' + qs : '');
		} catch (e) { return url || ''; }
	};
})();

(function() {
	document.addEventListener('DOMContentLoaded', function(event) {
		const main = document.querySelector('main')
		const body = document.body

		function resetPopupSteps(popup){
			const step1 = popup.querySelector('.step-1')
			const step2 = popup.querySelector('.step-2')
			const indicators = popup.querySelectorAll('.step-indicator')

			if(step2) step2.classList.remove('active')
			if(step1) step1.classList.add('active')

			if(indicators.length){
				indicators[1]?.classList.remove('active')
				indicators[0]?.classList.add('active')
			}
		}

		document.addEventListener('click', function(event) {
			const target = event.target

			if(target.closest('[data-action="toggleMobileMenu"]')) {
				event.preventDefault()

				const button = target.closest('[data-action="toggleMobileMenu"]')
				const header = button.closest('.header')
				const navBox = header ? header.querySelector('.nav_box--mobile') : null

				button.classList.toggle('open')
				if(navBox) navBox.classList.toggle('open')

				body.classList.remove('overflow')
				main.classList.remove('ev-none')

				if(button.classList.contains('open')) {
					body.classList.add('overflow')
					main.classList.add('ev-none')
				}

				document.querySelectorAll('.menu-item-has-children.opened').forEach(el => el.classList.remove('opened'))
			}

			if(target.closest('[data-action="toggleSubmenu"]')) {
				event.preventDefault()

				if(window.innerWidth > 1200) return null
				target.closest('.menu-item-has-children').classList.toggle('opened')
			}

			if(target.closest('[data-action="toggleCartPopup"]')) {
				event.preventDefault()

				document.querySelector('#cart_popup').classList.toggle('open')
			}

			if(target.closest('[data-action="togglePopup"]')) {
				event.preventDefault()

				const popup = target.closest('[data-action="togglePopup"]').getAttribute('data-target')
				if(popup) {
					document.querySelector(popup).classList.toggle('open')
					body.classList.add('overflow')
					main.classList.add('ev-none')
				}
			}

			// close on ✕/overlay, or on a click directly on the scroll area
			// (outside the card) — popup_content captures clicks now.
			if(target.closest('[data-action="closePopup"]') || target.classList.contains('popup_content')) {

				const popup = target.closest('.popup_inner')
				if(!popup) return

				popup.classList.remove('open')

				body.classList.remove('overflow')
				main.classList.remove('ev-none')

				setTimeout(() => {
					resetPopupSteps(popup)
				}, 200)
			}

			if (target.closest('.header_menu a[href*="#"]')) {
				const header = document.querySelector('.header')
				const navBox = header ? header.querySelector('.nav_box--mobile') : null
				const button = header ? header.querySelector('[data-action="toggleMobileMenu"].open') : null

				// закрываем меню
				if (navBox) navBox.classList.remove('open')
				if (button) button.classList.remove('open')
				body.classList.remove('overflow')
				main.classList.remove('ev-none')
			}

			// закрытие мобильного меню при клике вне его
			if (!target.closest('.nav_box--mobile') && !target.closest('[data-action="toggleMobileMenu"]')) {

				const navBox = document.querySelector('.nav_box--mobile.open')
				const button = document.querySelector('[data-action="toggleMobileMenu"].open')

				if (navBox) navBox.classList.remove('open')
				if (button) button.classList.remove('open')

				if (navBox || button) {
					body.classList.remove('overflow')
					main.classList.remove('ev-none')
				}
			}

			// ===== MULTI STEP POPUP =====

			// NEXT STEP
			if(target.closest('[data-action="nextStep"]')) {

				const popup = target.closest('.popup_inner')
				if(!popup) return

				const step1 = popup.querySelector('.step-1')
				const step2 = popup.querySelector('.step-2')
				const indicators = popup.querySelectorAll('.step-indicator')

				if(step1) step1.classList.remove('active')
				if(step2) step2.classList.add('active')

				if(indicators.length){
					indicators[0]?.classList.remove('active')
					indicators[1]?.classList.add('active')
				}

				const firstInput = step2?.querySelector('input, textarea')
				if(firstInput) firstInput.focus()
			}


			// PREV STEP
			if(target.closest('[data-action="prevStep"]')) {

				const popup = target.closest('.popup_inner')
				if(!popup) return

				resetPopupSteps(popup)
			}
		})

		document.addEventListener('keydown', function(event) {
			if (event.key === 'Escape' || event.key === 'Esc') {
				document.querySelectorAll('.popup_inner.open').forEach(popup => {
					popup.classList.remove('open')
				})

				const cartPopup = document.querySelector('#cart_popup.open')
				if (cartPopup) {
					cartPopup.classList.remove('open')
				}

				body.classList.remove('overflow')
				main.classList.remove('ev-none')
			}
		})


		// global scripts

		// arrows for header menu
		document.querySelectorAll('.header .menu-item-has-children').forEach(item => {
			const arrow = document.createElement('span')
			arrow.classList.add('arrow')
			arrow.setAttribute('data-action', 'toggleSubmenu')

			item.append(arrow)
		})

		// add target="_blank" to all external links
		const links = document.querySelectorAll('a[href]')
		const currentHost = window.location.hostname

		links.forEach(link => {
			const url = new URL(link.href, window.location.href)

			if(url.hostname !== currentHost) {
				link.setAttribute('target', '_blank')
				link.setAttribute('rel', 'noopener noreferrer')
			}
		})

		// ===== BTN TO TOP =====
		const btn = document.querySelector('.scroll-top-btn');
		if (btn) {
			let ticking = false;
			let visible = false;

			const updateTopBtn = () => {
				ticking = false;
				const shouldShow = window.scrollY > 300;
				if (shouldShow !== visible) {
					visible = shouldShow;
					btn.classList.toggle('show', shouldShow);
				}
			};

			window.addEventListener('scroll', () => {
				if (!ticking) {
					ticking = true;
					requestAnimationFrame(updateTopBtn);
				}
			}, {passive: true});

			updateTopBtn();

			btn.addEventListener('click', () => {
				const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
				window.scrollTo({
					top: 0,
					behavior: reduce ? 'auto' : 'smooth'
				});
			});
		}

		// ===== BTN BACK =====
		// Real link (fallback URL) for no-JS / direct visits; go back in history when the user came from this site.
		const backBtn = document.querySelector('.back-btn');
		if (backBtn) {
			backBtn.addEventListener('click', (e) => {
				// let modified clicks (new tab / window) open the fallback link as usual
				if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
				if (backBtn.hasAttribute('data-no-history')) return;

				let sameSite = false;
				try {
					sameSite = !!document.referrer && new URL(document.referrer).origin === location.origin;
				} catch (err) {}

				if (sameSite && history.length > 1) {
					e.preventDefault();
					history.back();
				}
			});
		}

		// SCROLL ANIMATE
		const animatedElements = document.querySelectorAll(".scroll-animate");

		const observer = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				const el = entry.target;

				if (entry.isIntersecting) {
					el.classList.add("animated");
				}
			});
		}, {
			threshold: 0.35
		});

		animatedElements.forEach(el => observer.observe(el));
	})
})()