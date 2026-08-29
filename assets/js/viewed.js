(function () {
	'use strict';

	const config = window.knotViewed || {};
	const KEY = 'knot_viewed';
	const STORE_MAX = 24;   // keep a bit of history
	const SHOW_MAX = 12;    // endpoint also caps at 12

	document.addEventListener('DOMContentLoaded', function () {
		recordCurrent();
		initServerRenderedSliders();
		renderSection();
	});

	/**
	 * Sections rendered by PHP (e.g. "Схожі вироби") already have their cards in
	 * the markup — they only need the slider and the flip behaviour, which is
	 * exactly what the history section uses once its cards arrive.
	 */
	function initServerRenderedSliders() {
		document.querySelectorAll('[data-cards-section]').forEach(initSlider);
	}

	/* ---------------- STORAGE ---------------- */

	function read() {
		let raw;
		try {
			raw = JSON.parse(localStorage.getItem(KEY) || '[]');
		} catch (e) {
			raw = [];
		}
		if (!Array.isArray(raw)) return [];
		return raw
			.map(function (n) { return parseInt(n, 10); })
			.filter(function (n) { return n > 0; });
	}

	function write(ids) {
		try {
			localStorage.setItem(KEY, JSON.stringify(ids.slice(0, STORE_MAX)));
		} catch (e) {
			/* ignore */
		}
	}

	function recordCurrent() {
		const id = parseInt(config.currentId, 10);
		if (!id) return;

		const ids = read().filter(function (x) { return x !== id; });
		ids.unshift(id);
		write(ids);
	}

	/* ---------------- RENDER ---------------- */

	function renderSection() {
		// Keep only the first section if the block was also placed manually.
		const sections = document.querySelectorAll('[data-viewed]');
		for (let i = 1; i < sections.length; i++) sections[i].remove();

		const section = sections[0];
		if (!section || !config.restUrl) return;

		const list = section.querySelector('[data-viewed-list]');
		if (!list) return;

		const exclude = parseInt(section.dataset.exclude, 10) || 0;
		const tap = section.dataset.tap || '';

		let ids = read();
		if (exclude) ids = ids.filter(function (x) { return x !== exclude; });
		ids = ids.slice(0, SHOW_MAX);
		if (!ids.length) return;

		const url = config.restUrl
			+ '?ids=' + encodeURIComponent(ids.join(','))
			+ '&tap=' + encodeURIComponent(tap);

		// Reveal now and reserve the card-row height before the fetch resolves,
		// so the content below never jumps when cards arrive (avoids CLS). The
		// heading shows immediately; cards fade in once loaded.
		section.hidden = false;
		section.classList.add('is-loading');

		fetch(url, { headers: { Accept: 'application/json' } })
			.then(function (response) { return response.json(); })
			.then(function (res) {
				const html = res && res.html ? res.html : '';
				if (!html.trim()) {
					// Nothing to show after all (e.g. items unpublished) — collapse back.
					section.hidden = true;
					section.classList.remove('is-loading');
					return;
				}

				// Trusted: markup rendered server-side with esc_* applied.
				list.innerHTML = html;
				initSlider(section);

				// Next frame: drop the loading state so cards fade into the
				// already-reserved space.
				requestAnimationFrame(function () {
					section.classList.remove('is-loading');
				});
			})
			.catch(function () {
				section.hidden = true;
				section.classList.remove('is-loading');
			});
	}

	/* ---------------- SLIDER + FLIP (mirrors in-stock) ---------------- */

	function initSlider(section) {
		// Both strips share the card markup, so they share this code: the
		// in-stock block keeps its own class for styling only.
		const slider = section.querySelector('.viewed-slider, .in-stock-slider');
		let swiper = null;
		let inView = false;

		if (slider && typeof Swiper !== 'undefined') {
			if (slider.swiper) slider.swiper.destroy(true, true);
			swiper = new Swiper(slider, {
				slidesPerView: 'auto',
				spaceBetween: 24,
				autoplay: { delay: 4000 },
				freeMode: false,
				speed: 500
			});
			// Hold autoplay until the strip is actually on screen (observer below),
			// so it doesn't advance several slides before the user scrolls to it.
			if (swiper.autoplay) swiper.autoplay.stop();
		}

		function syncAutoplay() {
			if (!swiper || !swiper.autoplay) return;
			const anyFlipped = section.querySelector('.stock-card.flipped');
			if (inView && !anyFlipped) {
				swiper.autoplay.start();
			} else {
				swiper.autoplay.stop();
			}
		}

		if (swiper && 'IntersectionObserver' in window) {
			const io = new IntersectionObserver(function (entries) {
				inView = entries[0].isIntersecting;
				syncAutoplay();
			}, { threshold: 0.25 });
			io.observe(slider);
		} else {
			inView = true; // no observer support: fall back to always-on
			syncAutoplay();
		}

		bindFlip(section, swiper, syncAutoplay);
	}

	/**
	 * Tap flips the card open, tap again flips it back — including the ✕, which
	 * needs no handler of its own. The only way to the product is the link on
	 * the back face; the card itself never navigates, because people tapping a
	 * second time mean "close", not "take me away".
	 */
	function bindFlip(section, swiper, syncAutoplay) {
		const cards = section.querySelectorAll('.stock-card');

		cards.forEach(function (card) {
			const back = card.querySelector('.stock-card-back');

			// While the card is face down its link is off-screen; keep it out of
			// the tab order and the accessibility tree too.
			if (back) back.inert = !card.classList.contains('flipped');

			card.addEventListener('click', function (event) {
				// Let the browser follow the one real link.
				if (event.target.closest('.stock-card-cta')) return;

				const flipped = card.classList.toggle('flipped');
				if (back) back.inert = !flipped;

				syncAutoplay();
			});
		});
	}
})();
