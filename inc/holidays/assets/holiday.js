/**
 * Shared holiday decoration:
 *   - a full-viewport particle field (bats / snow / petals by holiday);
 *   - a small floating button that expands to a panel with the greeting and an
 *     on/off switch for the animation (so a visitor who finds it distracting
 *     can turn it off; the choice is remembered across visits);
 *   - themed motifs on the up/back buttons and by the logo.
 *
 * Reads window.knotHoliday ({slug, banner}) set by the module. Respects
 * prefers-reduced-motion and pauses while the tab is hidden; pointer-events
 * never block the page.
 */
(function () {
	"use strict";

	var cfg = window.knotHoliday || {};
	var slug = cfg.slug || '';
	if (!slug) return;

	var TYPE = { halloween: 'bat', new_year: 'snow', march8: 'petal' }[slug] || 'snow';
	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var ANIM_OFF = 'kh-anim-off'; // visitor turned the animation off (all holidays)

	function store(get, key, val) {
		try { return get ? window.localStorage.getItem(key) : window.localStorage.setItem(key, val); } catch (e) { return null; }
	}
	// data-saver users start with the animation off (they can still switch it on)
	var LITE = !!(navigator.connection && navigator.connection.saveData);
	function animOff() { var v = store(true, ANIM_OFF); return v === null ? LITE : v === '1'; }
	var uid = 0;
	// Each inserted copy of an icon needs its own gradient ids: duplicates resolve to the
	// first copy in the page, and a gradient inside a hidden <svg> paints nothing.
	function uniq(html) {
		uid++;
		return html.replace(/id="(kh[A-Za-z]+)"/g, 'id="$1' + uid + '"').replace(/url\(#(kh[A-Za-z]+)\)/g, 'url(#$1' + uid + ')');
	}

	// Small themed motifs. Mostly currentColor (the holiday accent); a few fixed
	// tones for realism.
	var FPETAL = '<path d="M16 10 C13.5 9 12.5 5 14.4 3.2 C15 2.6 15.5 3.5 16 4.1 C16.5 3.5 17 2.6 17.6 3.2 C19.5 5 18.5 9 16 10 Z"/>';
	var FARM = 'M16 16 L16 4.5 M16 8.4 L12.8 5.8 M16 8.4 L19.2 5.8 M16 11.4 L13.7 9.6 M16 11.4 L18.3 9.6';
	var FARMS = (function () { var s = ''; for (var i = 0; i < 6; i++) s += '<g transform="rotate(' + (i * 60) + ' 16 16)"><path d="' + FARM + '"/></g>'; return s; })();
	var MOTIF = {
		halloween: '<svg viewBox="0 0 32 32" aria-hidden="true">' +
			'<defs><radialGradient id="khP" cx="40%" cy="34%" r="72%"><stop offset="0" stop-color="#F6A83F"/><stop offset=".6" stop-color="#E0761C"/><stop offset="1" stop-color="#B9550F"/></radialGradient></defs>' +
			'<path d="M16 9.6 C15.1 6 17.7 3.5 20.1 4.4 C18.4 5.4 17.9 7.3 18.3 9" fill="none" stroke="#5f7d35" stroke-width="2" stroke-linecap="round"/>' +
			'<g fill="url(#khP)"><ellipse cx="10.4" cy="19" rx="6" ry="8"/><ellipse cx="21.6" cy="19" rx="6" ry="8"/><ellipse cx="16" cy="19" rx="7.7" ry="8.8"/></g>' +
			'<g fill="none" stroke="#C9661A" stroke-width="1" opacity=".5" stroke-linecap="round"><path d="M12.8 12.8 C11 16 11 23 12.8 26"/><path d="M19.2 12.8 C21 16 21 23 19.2 26"/></g>' +
			'<g fill="#F7D774"><path d="M11.3 16.6 L14 16.6 L12.65 19.4 Z"/><path d="M20.7 16.6 L18 16.6 L19.35 19.4 Z"/><path d="M11.1 21.3 L20.9 21.3 L19.3 23.9 L17.6 22 L16 24.1 L14.4 22 L12.7 23.9 Z"/></g></svg>',
		new_year: '<svg viewBox="0 0 32 32" aria-hidden="true">' +
			'<g stroke="#9cc0db" stroke-width="3" opacity=".4" fill="none" stroke-linecap="round">' + FARMS + '</g>' +
			'<g stroke="#5f94bd" stroke-width="1.6" fill="none" stroke-linecap="round">' + FARMS + '</g>' +
			'<circle cx="16" cy="16" r="1.8" fill="#C19A4B"/></svg>',
		march8: '<svg viewBox="0 0 32 32" aria-hidden="true">' +
			'<defs><radialGradient id="khF" cx="50%" cy="44%" r="62%"><stop offset="0" stop-color="#FCD6E3"/><stop offset="1" stop-color="#E487AC"/></radialGradient></defs>' +
			'<path d="M16 30 C16 24 15.2 19.5 14.2 16.5" stroke="#6fae57" stroke-width="1.8" fill="none" stroke-linecap="round"/>' +
			'<path d="M14.6 21.5 C10.2 20.5 9.2 23 11.6 24 C9.4 25.1 11.8 27.4 14.2 25.2" fill="#79b85f"/>' +
			'<g fill="url(#khF)" transform="translate(0,0.5)">' + FPETAL +
			'<g transform="rotate(72 16 10)">' + FPETAL + '</g>' +
			'<g transform="rotate(144 16 10)">' + FPETAL + '</g>' +
			'<g transform="rotate(216 16 10)">' + FPETAL + '</g>' +
			'<g transform="rotate(288 16 10)">' + FPETAL + '</g></g>' +
			'<circle cx="16" cy="10.5" r="2.5" fill="#F5C84B"/>' +
			'<g fill="#E8A72E"><circle cx="16" cy="8.6" r=".5"/><circle cx="17.7" cy="10" r=".5"/><circle cx="17" cy="12" r=".5"/><circle cx="15" cy="12" r=".5"/><circle cx="14.3" cy="10" r=".5"/></g></svg>'
	};


	/* corner ornament that hangs off the top-left of the header */
	var ORN = {
		halloween:
			'<svg viewBox="0 0 150 160" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M1.5 1.5 L141.5 1.5M1.5 1.5 L134.6 44.8M1.5 1.5 L114.8 83.8M1.5 1.5 L83.8 114.8M1.5 1.5 L44.8 134.6M1.5 1.5 L1.5 141.5"/><path d="M27.5 1.5 Q23.6 5.0 26.2 9.5M26.2 9.5 Q21.4 11.7 22.5 16.8M22.5 16.8 Q17.3 17.3 16.8 22.5M16.8 22.5 Q11.7 21.4 9.5 26.2M9.5 26.2 Q5.0 23.6 1.5 27.5M51.5 1.5 Q44.0 8.2 49.1 17.0M49.1 17.0 Q39.8 21.0 42.0 30.9M42.0 30.9 Q31.9 31.9 30.9 42.0M30.9 42.0 Q21.0 39.8 17.0 49.1M17.0 49.1 Q8.2 44.0 1.5 51.5M75.5 1.5 Q64.4 11.5 71.9 24.4M71.9 24.4 Q58.2 30.4 61.4 45.0M61.4 45.0 Q46.5 46.5 45.0 61.4M45.0 61.4 Q30.4 58.2 24.4 71.9M24.4 71.9 Q11.5 64.4 1.5 75.5M99.5 1.5 Q84.7 14.7 94.7 31.8M94.7 31.8 Q76.6 39.8 80.8 59.1M80.8 59.1 Q61.1 61.1 59.1 80.8M59.1 80.8 Q39.8 76.6 31.8 94.7M31.8 94.7 Q14.7 84.7 1.5 99.5M123.5 1.5 Q105.1 17.9 117.5 39.2M117.5 39.2 Q95.0 49.1 100.2 73.2M100.2 73.2 Q75.7 75.7 73.2 100.2M73.2 100.2 Q49.1 95.0 39.2 117.5M39.2 117.5 Q17.9 105.1 1.5 123.5" stroke-width=".8"/><path d="M46.5 46.5 V75.5" stroke-width=".8"/><g stroke-width="1.1"><path d="M46.5 81.5 L41.1 75.2 L34.2 72.4M46.5 81.5 L39.9 79.7 L31.1 78.7M46.5 81.5 L39.9 84.2 L31.5 88.5M46.5 81.5 L41.7 87.8 L35.7 92.7M46.5 81.5 L51.9 75.2 L58.9 72.4M46.5 81.5 L53.1 79.7 L62.0 78.7M46.5 81.5 L53.1 84.2 L61.6 88.5M46.5 81.5 L51.3 87.8 L57.3 92.7"/></g><g fill="currentColor" stroke="none"><ellipse cx="46.5" cy="84.0" rx="5" ry="6"/><circle cx="46.5" cy="78.5" r="3.2"/></g></svg>',
		march8:
			'<svg viewBox="0 0 176 130" fill="none" stroke-linecap="round"><defs><g id="kh-bl"><path d="M0 0 C-11.2 -5.2 -12 -15.8 -2.4 -16.2 L0 -13.5 L2.4 -16.2 C12 -15.8 11.2 -5.2 0 0Z" transform="rotate(0)" fill="#f7c3d2" stroke="#e08aa8" stroke-width=".7"/><path d="M0 0 C-11.2 -5.2 -12 -15.8 -2.4 -16.2 L0 -13.5 L2.4 -16.2 C12 -15.8 11.2 -5.2 0 0Z" transform="rotate(72)" fill="#f7c3d2" stroke="#e08aa8" stroke-width=".7"/><path d="M0 0 C-11.2 -5.2 -12 -15.8 -2.4 -16.2 L0 -13.5 L2.4 -16.2 C12 -15.8 11.2 -5.2 0 0Z" transform="rotate(144)" fill="#f7c3d2" stroke="#e08aa8" stroke-width=".7"/><path d="M0 0 C-11.2 -5.2 -12 -15.8 -2.4 -16.2 L0 -13.5 L2.4 -16.2 C12 -15.8 11.2 -5.2 0 0Z" transform="rotate(216)" fill="#f7c3d2" stroke="#e08aa8" stroke-width=".7"/><path d="M0 0 C-11.2 -5.2 -12 -15.8 -2.4 -16.2 L0 -13.5 L2.4 -16.2 C12 -15.8 11.2 -5.2 0 0Z" transform="rotate(288)" fill="#f7c3d2" stroke="#e08aa8" stroke-width=".7"/><circle r="3" fill="#c24e78"/><circle cx="6.1" cy="1.1" r="1.3" fill="#8a2f55"/><circle cx="3.0" cy="5.4" r="1.3" fill="#8a2f55"/><circle cx="-2.3" cy="5.7" r="1.3" fill="#8a2f55"/><circle cx="-5.9" cy="1.8" r="1.3" fill="#8a2f55"/><circle cx="-5.1" cy="-3.5" r="1.3" fill="#8a2f55"/><circle cx="-0.5" cy="-6.2" r="1.3" fill="#8a2f55"/><circle cx="4.5" cy="-4.3" r="1.3" fill="#8a2f55"/></g></defs><path d="M2 3 C30 10 52 24 70 50 C84 70 96 82 120 92" stroke="#7a5640" stroke-width="3.2"/><path d="M54 30 C60 44 58 58 66 72" stroke="#7a5640" stroke-width="2"/><path d="M96 76 C112 74 128 80 148 72" stroke="#7a5640" stroke-width="1.8"/><path d="M20 12 C22 24 20 34 28 44" stroke="#7a5640" stroke-width="1.6"/><use href="#kh-bl" transform="translate(66 76) rotate(10) scale(1)"/><use href="#kh-bl" transform="translate(122 94) rotate(-20) scale(0.87)"/><use href="#kh-bl" transform="translate(28 47) rotate(25) scale(0.73)"/><use href="#kh-bl" transform="translate(150 72) rotate(40) scale(0.6)"/><use href="#kh-bl" transform="translate(84 38) rotate(-15) scale(0.6)"/><g fill="#e48aa9"><circle cx="40" cy="22" r="3.6"/><circle cx="106" cy="66" r="3.4"/><circle cx="14" cy="26" r="3"/></g><g fill="#f7c3d2" stroke="#e08aa8" stroke-width=".6"><path d="M104 112 q5-4 7 2 q-3 5-7-2z"/><path d="M46 100 q5-4 7 2 q-3 5-7-2z"/><path d="M132 50 q5-4 7 2 q-3 5-7-2z"/></g><g transform="translate(146 106)"><circle r="14" fill="#d9487a"/><circle r="14" fill="none" stroke="#fff" stroke-opacity=".7" stroke-width="1.2" transform="scale(.82)"/><g stroke="#fff" stroke-width="2.2" fill="none"><circle cx="0" cy="-4.6" r="3.6"/><circle cx="0" cy="4.2" r="4.4"/></g></g></svg>'
	};
	function buildOrnament() {
		if (!ORN[slug]) return;
		var hdr = document.getElementById('header') || document.querySelector('.header');
		var host = hdr && (hdr.querySelector(':scope > .container') || hdr);
		if (!host || host.querySelector('.kh-orn')) return;
		var el = document.createElement('span');
		el.className = 'kh-orn kh-orn--' + slug;
		el.setAttribute('aria-hidden', 'true');
		el.innerHTML = ORN[slug];
		host.appendChild(el);
		if (slug === 'march8') {
			var bq = document.createElement('span');
			bq.className = 'kh-orn-bq';
			bq.setAttribute('aria-hidden', 'true');
			bq.innerHTML = '<svg viewBox="0 -8 140 138" fill="none"><defs><g id="kh-th"><path d="M-9 0 C-12 -13 -7 -23 -2 -28 C-1 -17 0 -8 0 0Z" fill="currentColor"/><path d="M9 0 C12 -13 7 -23 2 -28 C1 -17 0 -8 0 0Z" fill="currentColor"/><path d="M-9 0 C-12 -13 -7 -23 -2 -28 C-1 -17 0 -8 0 0Z M9 0 C12 -13 7 -23 2 -28 C1 -17 0 -8 0 0Z" fill="#000" opacity=".16"/><path d="M-6.5 1 C-10 -12 -4 -22 0 -30 C4 -22 10 -12 6.5 1 C3 4 -3 4 -6.5 1Z" fill="currentColor"/><path d="M-3 -4 C-5 -12 -2 -20 0 -25 C-1 -17 -1 -10 -3 -4Z" fill="#fff" opacity=".35"/></g></defs><path d="M50 98 C38 86 34 66 40 52 C46 68 50 84 50 98Z" fill="#6fb25a"/><path d="M50 98 C66 92 78 78 78 62 C68 72 56 84 50 98Z" fill="#5f9a4a"/><path d="M50 102 L40.3 26.7" stroke="#5f9a4a" stroke-width="2.4" stroke-linecap="round"/><path d="M50 102 L59.7 26.7" stroke="#5f9a4a" stroke-width="2.4" stroke-linecap="round"/><path d="M50 102 L78.5 32.1" stroke="#5f9a4a" stroke-width="2.4" stroke-linecap="round"/><path d="M50 102 L95.0 42.4" stroke="#5f9a4a" stroke-width="2.4" stroke-linecap="round"/><path d="M50 102 L108.0 56.9" stroke="#5f9a4a" stroke-width="2.4" stroke-linecap="round"/><use href="#kh-th" transform="translate(40.3 26.7) rotate(-8)" style="color:#e8456b"/><use href="#kh-th" transform="translate(59.7 26.7) rotate(8)" style="color:#f27aa6"/><use href="#kh-th" transform="translate(78.5 32.1) rotate(24)" style="color:#f6c33b"/><use href="#kh-th" transform="translate(95.0 42.4) rotate(40)" style="color:#e8456b"/><use href="#kh-th" transform="translate(108.0 56.9) rotate(56)" style="color:#f27aa6"/></svg>';
			host.appendChild(bq);
		}
	}

	/* New Year: a string of lights across the whole header, tucked under its content */
	function garlandSvg(W) {
		var small = W < 552;
		var S = small ? 70 : 100, sag = small ? 22 : 30, sc = small ? .8 : 1;
		var n = Math.ceil(W / S), cols = ['#d8b45c', '#cf8f93', '#93b2c2', '#9bb49d', '#b79ac2', '#e0c48a'];
		var wire = '', glow = '', bulbs = '', k = 0, H = Math.round(sag / 2 + 2 + 18 * sc + 6);
		for (var i = 0; i < n; i++) {
			var x0 = i * S, x2 = (i + 1) * S, y0 = 2, cx = (x0 + x2) / 2, cy = 2 + sag;
			wire += 'M' + x0 + ' ' + y0 + ' Q' + cx + ' ' + cy + ' ' + x2 + ' ' + y0;
			[0.2, 0.5, 0.8].forEach(function (t) {
				var u = 1 - t, bx = u * u * x0 + 2 * u * t * cx + t * t * x2, by = u * u * y0 + 2 * u * t * cy + t * t * y0;
				var dx = 2 * u * (cx - x0) + 2 * t * (x2 - cx), dy = 2 * u * (cy - y0) + 2 * t * (y0 - cy);
				var nn = Math.sqrt(dx * dx + dy * dy), nx = -dy / nn, ny = dx / nn;
				if (ny < 0) { nx = -nx; ny = -ny; }
				var ang = Math.atan2(ny, nx) * 180 / Math.PI - 90, c = cols[k++ % cols.length];
				glow += '<circle class="kh-glow" style="animation-delay:-' + (k * 0.41 % 3.4).toFixed(2) + 's" cx="' + (bx + nx * 9 * sc).toFixed(1) + '" cy="' + (by + ny * 9 * sc).toFixed(1) + '" r="' + (8 * sc).toFixed(1) + '" fill="' + c + '"/>';
				bulbs += '<g transform="translate(' + bx.toFixed(1) + ' ' + by.toFixed(1) + ') rotate(' + ang.toFixed(1) + ') scale(' + sc + ')" style="color:' + c + '"><use href="#kh-bulb"/></g>';
			});
		}
		return '<svg width="' + W + '" height="' + H + '" viewBox="0 0 ' + W + ' ' + H + '" fill="none" aria-hidden="true">' +
			'<defs><g id="kh-bulb"><rect x="-2.3" y="-1" width="4.6" height="4.6" rx="1.3" fill="#b7a173"/><rect x="-2.3" y="-1" width="4.6" height="2" rx="1" fill="#cdbb90"/><path d="M-4 4 C-7.8 9.5 -5.6 16.8 0 18 C5.6 16.8 7.8 9.5 4 4Z" fill="currentColor"/><path d="M-4 4 C-7.8 9.5 -5.6 16.8 0 18 C5.6 16.8 7.8 9.5 4 4Z" fill="#000" opacity=".1" transform="translate(1 1.4)"/><ellipse cx="-1.6" cy="8.6" rx="2.1" ry="3.4" fill="#fff" opacity=".5"/><circle cx="-1.9" cy="6.6" r="1" fill="#fff" opacity=".85"/></g></defs>' +
			'<path d="' + wire + '" stroke="#566553" stroke-width="1.3" stroke-linecap="round"/>' + glow + bulbs + '</svg>';
	}
	function buildGarland() {
		if (slug !== 'new_year') return;
		var hdr = document.getElementById('header') || document.querySelector('.header');
		if (!hdr) return;
		var old = document.getElementById('kh-garland');
		if (old) old.remove();
		// Lives on <body>, pinned just under the fixed header, BEHIND the page
		// content (breadcrumbs etc.) so its bulbs tuck under the header edge
		// without covering anything below.
		var el = document.createElement('div');
		el.id = 'kh-garland';
		el.className = 'kh-garland';
		el.setAttribute('aria-hidden', 'true');
		el.innerHTML = garlandSvg(window.innerWidth);
		el.style.top = hdr.offsetHeight + 'px';
		document.body.appendChild(el);
	}
	var garTimer;
	window.addEventListener('resize', function () {
		clearTimeout(garTimer);
		garTimer = setTimeout(buildGarland, 200);
	});

	function start() {
		var field = createField();
		if (!animOff()) field.start();
		buildFab(field);
		buildMotifs();
		buildOrnament();
		buildGarland();
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}

	/* -------------------------------------------- motifs on nav + logo ------ */
	function addMotif(target, variant) {
		if (!target || target.querySelector('.kh-motif')) return;
		var el = document.createElement('span');
		el.className = 'kh-motif kh-motif--' + variant;
		el.innerHTML = uniq(MOTIF[slug] || '');
		target.appendChild(el);
	}
	function buildMotifs() {
		if (!MOTIF[slug]) return;
		addMotif(document.querySelector('.scroll-top-btn'), 'btn');
		addMotif(document.querySelector('.back-btn'), 'btn');
		addMotif(document.querySelector('.logo_wrapper'), 'logo');
	}

	/* -------------------------------------------- floating greeting fab ----- */
	function buildFab(field) {
		var text = (cfg.banner || '').trim();

		var fab = document.createElement('button');
		fab.id = 'kh-fab';
		fab.type = 'button';
		fab.setAttribute('aria-label', 'Святкове оформлення');
		fab.setAttribute('aria-expanded', 'false');
		var icon = document.createElement('span');
		icon.className = 'kh-fab-icon';
		icon.setAttribute('aria-hidden', 'true');
		icon.innerHTML = uniq(MOTIF[slug] || '');
		fab.appendChild(icon);

		var panel = document.createElement('div');
		panel.id = 'kh-panel';
		panel.setAttribute('role', 'dialog');
		panel.setAttribute('aria-label', 'Святкове оформлення');

		if (text) {
			var p = document.createElement('p');
			p.className = 'kh-panel-text';
			p.textContent = text;
			panel.appendChild(p);
		}

		var toggle = document.createElement('button');
		toggle.className = 'kh-toggle';
		toggle.type = 'button';
		function sync() {
			var off = animOff();
			toggle.textContent = off ? 'Увімкнути анімацію' : 'Вимкнути анімацію';
			fab.classList.toggle('kh-off', off);
			document.body.classList.toggle('kh-anim-paused', off);
		}
		sync();
		toggle.addEventListener('click', function () {
			if (animOff()) { store(false, ANIM_OFF, '0'); field.start(); }
			else { store(false, ANIM_OFF, '1'); field.stop(); }
			sync();
		});
		panel.appendChild(toggle);

		function setOpen(open) {
			panel.classList.toggle('open', open);
			fab.setAttribute('aria-expanded', open ? 'true' : 'false');
		}
		fab.addEventListener('click', function (e) {
			e.stopPropagation();
			setOpen(!panel.classList.contains('open'));
		});
		panel.addEventListener('click', function (e) { e.stopPropagation(); });
		document.addEventListener('click', function () { setOpen(false); });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });

		document.body.appendChild(fab);
		document.body.appendChild(panel);
	}

	/* -------------------------------------------- particle field ------------ */
	function createField() {
		var canvas = null, ctx = null, raf = 0, running = false;
		var dpr = Math.min(window.devicePixelRatio || 1, 1.5);
		var W = 0, H = 0, parts = [], color = '#000';

		function count() {
			var area = window.innerWidth * window.innerHeight;
			var dens = TYPE === 'snow' ? 26000 : (TYPE === 'petal' ? 34000 : 60000);
			var n = Math.round(area / dens);
			return Math.max(6, Math.min(TYPE === 'bat' ? 26 : 90, n));
		}
		function resize() {
			W = window.innerWidth; H = window.innerHeight;
			canvas.width = Math.round(W * dpr);
			canvas.height = Math.round(H * dpr);
			ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
		}
		function rand(a, b) { return a + Math.random() * (b - a); }
		function spawn(initial) {
			var p = { x: rand(0, W), y: initial ? rand(0, H) : -12, rot: rand(0, 6.28), vr: rand(-0.02, 0.02), ph: rand(0, 6.28), pp: rand(0, 6.28), ps: rand(0.01, 0.024) };
			if (TYPE === 'snow') { p.size = rand(1.6, 3.8); p.vy = rand(0.3, 0.8); p.vx = rand(-0.3, 0.3); p.sway = rand(0.4, 1.1); p.a = rand(0.45, 0.9); p.flap = 0.03; }
			else if (TYPE === 'petal') { p.size = rand(3.4, 6.4); p.vy = rand(0.4, 0.95); p.vx = rand(-0.5, 0.5); p.sway = rand(0.6, 1.4); p.a = rand(0.5, 0.85); p.flap = 0.03; }
			else { p.size = rand(6, 12); p.vy = rand(0.3, 0.7); p.vx = rand(0.25, 0.8) * (Math.random() < 0.5 ? -1 : 1); p.a = rand(0.45, 0.8); p.flap = rand(0.12, 0.22); }
			return p;
		}
		function build() {
			color = getComputedStyle(document.body).getPropertyValue('--kh-ptl').trim() || '#000';
			var n = count();
			parts = [];
			for (var i = 0; i < n; i++) parts.push(spawn(true));
		}
		function drawSnow(p) { ctx.globalAlpha = p.a; ctx.fillStyle = color; ctx.beginPath(); ctx.arc(p.x, p.y, (p.ds || p.size) * 0.5, 0, 6.28); ctx.fill(); }
		function drawPetal(p) {
			var s = p.ds || p.size;
			ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.rot); ctx.globalAlpha = p.a; ctx.fillStyle = color;
			ctx.beginPath(); ctx.ellipse(0, 0, s * 0.9, s * 0.45, 0, 0, 6.28); ctx.fill(); ctx.restore();
		}
		function drawBat(p) {
			var s = p.ds || p.size, f = Math.sin(p.ph) * 0.5 + 0.5;
			ctx.save(); ctx.translate(p.x, p.y); ctx.globalAlpha = p.a;
			ctx.beginPath(); ctx.moveTo(0, 0);
			ctx.quadraticCurveTo(-s * 0.6, -s * (0.2 + 0.5 * f), -s, 0);
			ctx.quadraticCurveTo(-s * 0.55, s * 0.12, -s * 0.28, s * 0.05);
			ctx.quadraticCurveTo(0, s * 0.28, s * 0.28, s * 0.05);
			ctx.quadraticCurveTo(s * 0.55, s * 0.12, s, 0);
			ctx.quadraticCurveTo(s * 0.6, -s * (0.2 + 0.5 * f), 0, 0);
			ctx.fillStyle = color;
			ctx.fill();
			ctx.beginPath(); ctx.arc(0, -s * 0.05, s * 0.16, 0, 6.28); ctx.fillStyle = color; ctx.fill();
			ctx.restore();
		}
		var draw = TYPE === 'snow' ? drawSnow : (TYPE === 'petal' ? drawPetal : drawBat);

		function step() {
			ctx.clearRect(0, 0, W, H);
			for (var i = 0; i < parts.length; i++) {
				var p = parts[i];
				p.pp += p.ps;
				p.ds = p.size * (1 + (Math.sin(p.pp) * 0.5 + 0.5)); // breathe: base size … 2× and back
				p.ph += p.flap;
				p.y += p.vy;
				p.x += p.vx + (TYPE === 'bat' ? 0 : Math.sin(p.ph) * p.sway * 0.3);
				if (TYPE === 'petal') p.rot += p.vr + 0.01;
				draw(p);
				if (p.y > H + 16 || p.x < -24 || p.x > W + 24) parts[i] = spawn(false);
			}
			raf = requestAnimationFrame(step);
		}
		function staticSprinkle() {
			ctx.clearRect(0, 0, W, H);
			var n = Math.round(count() * 0.6);
			for (var i = 0; i < n; i++) draw(spawn(true));
		}

		var rt, onResize, onVis;
		function startField() {
			if (running) return;
			running = true;
			canvas = document.createElement('canvas');
			canvas.id = 'kh-fx';
			canvas.setAttribute('aria-hidden', 'true');
			document.body.appendChild(canvas);
			ctx = canvas.getContext('2d');
			resize();
			build();
			if (reduce) { staticSprinkle(); }
			else { raf = requestAnimationFrame(step); }

			onResize = function () { clearTimeout(rt); rt = setTimeout(function () { if (!running) return; var sameW = window.innerWidth === W; resize(); if (sameW && !reduce) return; build(); if (reduce) staticSprinkle(); }, 180); };
			onVis = function () { if (reduce || !running) return; if (document.hidden) { cancelAnimationFrame(raf); raf = 0; } else if (!raf) { raf = requestAnimationFrame(step); } };
			window.addEventListener('resize', onResize);
			document.addEventListener('visibilitychange', onVis);
		}
		function stopField() {
			if (!running) return;
			running = false;
			cancelAnimationFrame(raf); raf = 0;
			window.removeEventListener('resize', onResize);
			document.removeEventListener('visibilitychange', onVis);
			if (canvas && canvas.parentNode) canvas.parentNode.removeChild(canvas);
			canvas = null; ctx = null; parts = [];
		}

		return { start: startField, stop: stopField };
	}
})();
