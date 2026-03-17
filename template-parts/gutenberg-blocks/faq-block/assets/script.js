(function () {
	document.addEventListener('DOMContentLoaded', function () {
		const section = document.querySelector('.advantages-block');
		if (!section) return;

		const cards = Array.from(section.querySelectorAll('.advantages-card'));
		const gap = 60;
		const headerGap = 40;
		const titleGap = 32;

		const header = document.querySelector('header');
		const title = section.querySelector('.advantages-block-title');

		function refreshSticky() {
			const isMobile = window.innerWidth <= 1023;

			if (title) {
				title.style.position = '';
				title.style.top = '';
			}

			cards.forEach(card => {
				card.style.position = '';
				card.style.top = '';
				card.style.zIndex = '';
			});

			if (!isMobile) return;

			const headerHeight = header ? header.offsetHeight : 0;
			const titleHeight = title ? title.offsetHeight : 0;
			const titleTop = headerHeight + headerGap;
			const firstTop = headerHeight + titleHeight + headerGap + titleGap;

			if (title) {
				title.style.position = 'sticky';
				title.style.top = titleTop + 'px';
			}

			let prevTop = firstTop;

			cards.forEach((card, index) => {
				card.style.position = 'sticky';
				card.style.zIndex = (index + 1).toString();

				if (index !== 0) {
					prevTop += gap;
				}

				card.style.top = prevTop + 'px';
			});

		}

		window.addEventListener('resize', () => requestAnimationFrame(refreshSticky));
		window.addEventListener('load', () => requestAnimationFrame(refreshSticky));
		refreshSticky();
	})
})()