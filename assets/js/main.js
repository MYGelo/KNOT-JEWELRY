(function() {
	document.addEventListener('DOMContentLoaded', function(event) {

		document.addEventListener('click', function(event) {
			const target = event.target

			if(target.closest('[data-action="toggleMobileMenu"]')) {
				event.preventDefault()

				const button = target.closest('[data-action="toggleMobileMenu"]')
				const header = button.closest('.header')
				const navBox = header ? header.querySelector('.nav_box--mobile') : null
				const body = document.body

				button.classList.toggle('open')
				if(navBox) navBox.classList.toggle('open')

				body.classList.remove('overflow')
				if(button.classList.contains('open')) {
					body.classList.add('overflow')
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
				if(popup) document.querySelector(popup).classList.toggle('open')
				if (popup) {
					document.body.classList.add('overflow')
				}
			}

			if(target.closest('[data-action="closePopup"]')) {
				target.closest('.popup_inner').classList.remove('open')
				document.body.classList.remove('overflow')
			}

			if (target.closest('.header_menu a[href*="#"]')) {
				const url = new URL(link.href);
				const isSamePage =
					url.pathname === window.location.pathname;

				// ВСЕГДА закрываем меню
				const header = document.querySelector('.header');
				const navBox = header?.querySelector('.nav_box--mobile');
				const button = header?.querySelector('[data-action="toggleMobileMenu"].open');

				if (navBox) navBox.classList.remove('open');
				if (button) button.classList.remove('open');
				document.body.classList.remove('overflow');

				// если это не текущая страница — просто даём перейти
				if (!isSamePage) return;

				// если это текущая страница — блокируем дефолт и скроллим
				event.preventDefault();

				const id = url.hash.replace('#', '');
				const el = document.getElementById(id);

				if (el) {
					el.scrollIntoView({ behavior: 'smooth' });
				}
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

				document.body.classList.remove('overflow')
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
			window.addEventListener('scroll', () => {
				if (window.scrollY > 300) {
					btn.classList.add('show');
				} else {
					btn.classList.remove('show');
				}
			});

			btn.addEventListener('click', () => {
				window.scrollTo({
					top: 0,
					behavior: 'smooth'
				});
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
