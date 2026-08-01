document.addEventListener('DOMContentLoaded', function () {

	/* ---------------- BEFORE / AFTER DRAG ---------------- */
	document.querySelectorAll('[data-repair-cmp]').forEach(function (cmp) {

		const before = cmp.querySelector('[data-repair-before]');
		const inner = cmp.querySelector('[data-repair-inner]');
		const handle = cmp.querySelector('[data-repair-handle]');
		if (!before || !inner || !handle) return;

		function setX(px) {
			const w = cmp.clientWidth;
			if (px < 0) px = 0;
			if (px > w) px = w;
			before.style.width = px + 'px';
			inner.style.width = w + 'px'; // keep the clipped image at full width so it aligns
			handle.style.left = px + 'px';
		}

		function fromEvent(e) {
			const rect = cmp.getBoundingClientRect();
			const cx = (e.touches && e.touches[0]) ? e.touches[0].clientX : e.clientX;
			setX(cx - rect.left);
		}

		let dragging = false;

		cmp.addEventListener('pointerdown', function (e) {
			dragging = true;
			try { cmp.setPointerCapture(e.pointerId); } catch (err) { /* ignore */ }
			fromEvent(e);
		});
		cmp.addEventListener('pointermove', function (e) { if (dragging) fromEvent(e); });
		cmp.addEventListener('pointerup', function () { dragging = false; });
		cmp.addEventListener('pointercancel', function () { dragging = false; });

		window.addEventListener('resize', function () { setX(cmp.clientWidth * 0.52); });
		setX(cmp.clientWidth * 0.52);
	});

	/* ---------------- STEP LIGHTBOX ---------------- */
	document.querySelectorAll('.repair').forEach(function (section) {

		const steps = Array.prototype.slice.call(section.querySelectorAll('[data-repair-step]'));
		const modal = section.querySelector('[data-repair-modal]');
		if (!steps.length || !modal) return;

		const imgEl = modal.querySelector('[data-repair-modal-img]');
		const capEl = modal.querySelector('[data-repair-modal-cap]');
		const prev = modal.querySelector('[data-repair-prev]');
		const next = modal.querySelector('[data-repair-next]');

		const items = steps.map(function (b) {
			return { full: b.getAttribute('data-full'), title: b.getAttribute('data-title') || '' };
		});

		let idx = 0;
		let closeTimer = null;

		function render() {
			const it = items[idx];
			if (imgEl) { imgEl.src = it.full; imgEl.alt = it.title; }
			if (capEl) capEl.textContent = it.title;
		}

		function open(i) {
			idx = i;
			render();
			clearTimeout(closeTimer);
			modal.hidden = false;
			requestAnimationFrame(function () { modal.classList.add('is-open'); });
			document.body.classList.add('repair-modal-open');
		}

		function close() {
			modal.classList.remove('is-open');
			document.body.classList.remove('repair-modal-open');
			closeTimer = setTimeout(function () {
				modal.hidden = true;
				if (imgEl) imgEl.src = '';
			}, 250);
		}

		function go(delta) {
			idx = (idx + delta + items.length) % items.length;
			render();
		}

		steps.forEach(function (b, i) {
			b.addEventListener('click', function () { open(i); });
		});

		modal.querySelectorAll('[data-repair-close]').forEach(function (el) {
			el.addEventListener('click', close);
		});

		if (items.length < 2) {
			if (prev) prev.style.display = 'none';
			if (next) next.style.display = 'none';
		} else {
			if (prev) prev.addEventListener('click', function () { go(-1); });
			if (next) next.addEventListener('click', function () { go(1); });
		}

		document.addEventListener('keydown', function (e) {
			if (modal.hidden) return;
			if (e.key === 'Escape') close();
			else if (e.key === 'ArrowLeft') go(-1);
			else if (e.key === 'ArrowRight') go(1);
		});
	});

});
