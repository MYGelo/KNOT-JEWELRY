(function () {
	'use strict';

	const conn = navigator.connection;
	// Respect data-saver / very slow connections — no speculative prefetch.
	const allowPrefetch = !(conn && (conn.saveData || /(^|-)2g$/.test(conn.effectiveType || '')));

	const origin = location.origin;
	const prefetched = new Set();
	let progressFail = null;

	document.addEventListener('DOMContentLoaded', function () {
		buildProgressBar();
		buildLoader();
	});

	/* ---------------- SPECULATIVE PREFETCH ---------------- */

	function prefetchableUrl(link) {
		if (!link || link.tagName !== 'A') return '';

		const href = link.href || '';
		if (!href || href.indexOf(origin) !== 0) return '';           // same-origin only
		if (link.hasAttribute('download')) return '';
		if (link.target && link.target !== '' && link.target !== '_self') return '';
		if (/^(mailto:|tel:)/i.test(href)) return '';

		// Skip functional / cart / admin / anchor-only links.
		if (link.matches('[data-action], [data-cart-toggle], [data-cart-close]')) return '';
		if (/\/(wp-admin|wp-login|cart|checkout|my-account)(\/|$)/i.test(href)) return '';
		if (/([?&])add-to-cart=/i.test(href)) return '';

		// Same page (ignoring hash) — nothing to prefetch.
		const clean = href.split('#')[0];
		if (clean === location.href.split('#')[0]) return '';

		if (prefetched.has(clean)) return '';
		return clean;
	}

	function prefetch(url) {
		if (!url || prefetched.has(url)) return;
		prefetched.add(url);

		const link = document.createElement('link');
		link.rel = 'prefetch';
		link.href = url;
		document.head.appendChild(link);
	}

	if (allowPrefetch) {
		// Desktop: prefetch on hover intent (small delay avoids over-fetching).
		// Prefetch on press intent (mousedown / touchstart) rather than hover —
		// this still gives the server a head start before the click completes,
		// without firing dozens of requests while the cursor sweeps a listing.
		document.addEventListener('mousedown', function (event) {
			if (event.button !== 0) return;
			const link = event.target.closest && event.target.closest('a');
			const url = prefetchableUrl(link);
			if (url) prefetch(url);
		});

		document.addEventListener('touchstart', function (event) {
			const link = event.target.closest && event.target.closest('a');
			const url = prefetchableUrl(link);
			if (url) prefetch(url);
		}, { passive: true });
	}

	/* ---------------- NAVIGATION PROGRESS BAR ---------------- */

	function buildProgressBar() {
		if (document.getElementById('nav-progress')) return;
		const bar = document.createElement('div');
		bar.id = 'nav-progress';
		document.body.appendChild(bar);
	}

	// Center iOS-style HUD spinner: 12 fading spokes inside a rounded dark box.
	function buildLoader() {
		if (document.getElementById('nav-loader')) return;

		const overlay = document.createElement('div');
		overlay.id = 'nav-loader';
		overlay.setAttribute('aria-hidden', 'true');

		const box = document.createElement('div');
		box.className = 'nav-loader__box';

		const spinner = document.createElement('div');
		spinner.className = 'nav-spinner';

		for (let i = 0; i < 12; i++) {
			const spoke = document.createElement('i');
			spoke.style.transform = 'rotate(' + (i * 30) + 'deg)';
			spoke.style.animationDelay = (-(12 - i) / 12) + 's';
			spinner.appendChild(spoke);
		}

		box.appendChild(spinner);
		overlay.appendChild(box);
		document.body.appendChild(overlay);
	}

	function startProgress() {
		const bar = document.getElementById('nav-progress');
		if (bar) bar.classList.add('is-loading');
		const loader = document.getElementById('nav-loader');
		if (loader) loader.classList.add('is-loading');
		// Busy cursor everywhere — an unmistakable "something is happening" cue.
		document.documentElement.classList.add('is-navigating');
	}

	function stopProgress() {
		const bar = document.getElementById('nav-progress');
		if (bar) bar.classList.remove('is-loading');
		const loader = document.getElementById('nav-loader');
		if (loader) loader.classList.remove('is-loading');
		document.documentElement.classList.remove('is-navigating');
	}

	// Show feedback on a real in-tab navigation click. We deliberately avoid
	// `beforeunload`/`unload` here — those listeners disable the back/forward
	// cache (bfcache) in some browsers, which would slow down Back/Forward.
	document.addEventListener('click', function (event) {
		if (event.defaultPrevented) return;
		if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

		const link = event.target.closest && event.target.closest('a');
		if (!link) return;
		if (link.target && link.target !== '' && link.target !== '_self') return;
		if (link.hasAttribute('download')) return;

		const href = link.href || '';
		if (!/^https?:\/\//i.test(href)) return;              // skip mailto:, tel:, etc.
		if (href.split('#')[0] === location.href.split('#')[0]) return; // same page / anchor

		startProgress();

		// Safety: if the navigation was cancelled by another handler, release it.
		clearTimeout(progressFail);
		progressFail = setTimeout(stopProgress, 10000);
	});

	// Reset on back/forward (bfcache) so the bar never stays stuck.
	window.addEventListener('pageshow', stopProgress);
})();
