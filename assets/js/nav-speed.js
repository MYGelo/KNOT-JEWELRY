(function () {
	'use strict';

	const conn = navigator.connection;
	// Respect data-saver / very slow connections — no speculative prefetch.
	const allowPrefetch = !(conn && (conn.saveData || /(^|-)2g$/.test(conn.effectiveType || '')));

	const origin = location.origin;
	const prefetched = new Set();
	let hoverTimer = null;

	document.addEventListener('DOMContentLoaded', function () {
		buildProgressBar();
		if (allowPrefetch && 'requestIdleCallback' in window) {
			// nothing eager; prefetch happens on intent below
		}
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
		document.addEventListener('mouseover', function (event) {
			const link = event.target.closest && event.target.closest('a');
			const url = prefetchableUrl(link);
			if (!url) return;

			clearTimeout(hoverTimer);
			hoverTimer = setTimeout(function () {
				prefetch(url);
			}, 65);
		});

		document.addEventListener('mouseout', function () {
			clearTimeout(hoverTimer);
		});

		// Mobile: touchstart fires well before the click.
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

	function startProgress() {
		const bar = document.getElementById('nav-progress');
		if (bar) bar.classList.add('is-loading');
	}

	function stopProgress() {
		const bar = document.getElementById('nav-progress');
		if (bar) bar.classList.remove('is-loading');
	}

	// A real navigation away from the page → show feedback immediately.
	window.addEventListener('beforeunload', startProgress);

	// Restore on back/forward (bfcache) so the bar never stays stuck.
	window.addEventListener('pageshow', stopProgress);
})();
