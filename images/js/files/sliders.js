//BildSlider
document.querySelectorAll('[data-swiper]').forEach(slider => {
	slider.classList.add('swiper');
	if (!slider.classList.contains('swiper-bild')) {
		let sliderWrapper = slider.firstElementChild;
		let sliderItems = Array.from(sliderWrapper.children);
		sliderWrapper.classList.add('swiper-wrapper');
		sliderItems.forEach(slide => {
			slide.classList.add('swiper-slide');
		});
		slider.classList.add('swiper-bild');
	}
});

// ========================================================
// ========================================================
// ========================================================

let mainSlider = new Swiper('.main-slider__body', {
	effect: 'fade',
	slidesPerView: 1,
	speed: 800,
	autoplay: {
		delay: 8000,
		pauseOnMouseEnter: true,
		disableOnInteraction: false,
	},
	pagination: {
		el: '.main-slider__pagination',
		bulletClass: 'main-slider-bullet',
		bulletActiveClass: '_active',
		clickable: true,
		renderBullet: index => getMainSliderBullet(index),
	},
});

function getMainSliderBullet(index) {
	const slide = document.querySelectorAll('.main-slider-slide')[index];
	let title = slide.querySelector('.main-slider-slide__title').innerHTML;
	let text = slide.querySelector('.main-slider-slide__pagging-text').innerHTML;
	let bullet = `
		<li class="main-slider__bullet main-slider-bullet">
			<h4 class="main-slider-bullet__title">${title}</h4>
			<p class="main-slider-bullet__text">${text}</p>
		</li>
	`;
	return bullet;
}

// ========================================================


let newsSlider = new Swiper('.news-slider__body', {
	slidesPerView: 4,
	spaceBetween: 30,
	speed: 800,
	autoheight: true,
	navigation: {
		disabledClass: "_disabled",
    prevEl: '.news-slider-arrows__arrow_prev',
    nextEl: '.news-slider-arrows__arrow_next',
  },
	breakpoints: {
		320: { slidesPerView: 1 },
		480: { slidesPerView: 1 },
		768: { slidesPerView: 2 },
		992: { slidesPerView: 3 },
    1201: { slidesPerView: 3 },
    1583: { slidesPerView: 4 }
	},
	scrollbar: {
    draggable: true,
  },
});
