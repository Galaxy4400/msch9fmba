let isBlind = false;

window.addEventListener('load', function() {
	document.addEventListener('click', documentActions);

		function documentActions(e) {
			const target = e.target;

			// Search form actions
			if (target.closest('.menu-line__search-btn')) {
				document.querySelector('.search').classList.toggle('_active');
				setTimeout(() => {
					document.querySelector('.search__input').focus();
				}, 500);
			} else if (!target.closest('.menu-line__search') && document.querySelector('.search._active')) {
				document.querySelector('.search').classList.remove('_active');
				document.querySelector('.search__input').blur();
			}

			// Menu actions
			if (isMobile.any()) {
				if (target.classList.contains('menu__arrow')) {
					target.closest('.menu__item').classList.toggle('_hover');
				}
				if (target.classList.contains('menu__sub-arrow')) {
					target.closest('.menu__sub-item').classList.toggle('_hover');
				}
				if (!target.closest('.menu__item') && document.querySelectorAll('.menu__item._hover').length > 0) {
					_removeClasses(document.querySelectorAll('.menu__item._hover'), "_hover");
				}
				if (!target.closest('.menu__sub-item') && document.querySelectorAll('.menu__sub-item._hover').length > 0) {
					_removeClasses(document.querySelectorAll('.menu__sub-item._hover'), "_hover");
				}
				if (!target.closest('.menu-line') && !target.closest('.icon-menu') && document.querySelectorAll('.menu-line._active').length > 0) {
					_removeClasses(document.querySelectorAll('.menu-line._active'), "_active");
					_removeClasses(document.querySelectorAll('.icon-menu._active'), "_active");
					menu_close();
				}
				if (target.closest('.menu__back')) {
					target.closest('._hover').classList.remove('_hover');
				}
			} else {
				if (window.innerWidth > 767) return;

				if (target.closest('.menu__arrow')) {
					target.closest('.menu__item').classList.toggle('_hover');
				}
				if (target.closest('.menu__sub-arrow')) {
					target.closest('.menu__sub-item').classList.toggle('_hover');
				}
				if (target.closest('.menu__back')) {
					target.closest('._hover').classList.remove('_hover');
				}
			}
		}
	}
)
// Версия для слабовидящих
function blindVersion() {
	let btn = document.getElementById('blind-btn');
	if (getCookie('blind')) {
		deleteCookie('blind');
		document.documentElement.classList.remove('_blind');
		btn.innerHTML = 'Версия для слабовидящих';
		isBlind = false;
	} else {
		setCookie('blind', true, {'max-age': 3600 * 24 * 30});
		document.documentElement.classList.add('_blind');
		btn.innerHTML = 'Обычная версия';
		isBlind = true;
	}
}