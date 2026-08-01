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

});
