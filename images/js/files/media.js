/**
 * media.js
 * 
 * Copyright (c) 2023 Moiseev Evgeny
 * Organization: WebisGroup
 * 
 * Скрипты инициализации различных медиа-объектов
 * 
 * Используется библиотека lightgallery.js: assets/js/libs/lightgallery.min.js
 *
 * Документация: https://www.lightgalleryjs.com/docs/getting-started/
 * Примеры: https://www.lightgalleryjs.com/demos/
 *
 * Требуется подключение стилей: assets/scss/_lightgallery.scss
 */


/**
 * Инициализация галлерей изображений по атрибуту data-gallery
 */
document.querySelectorAll('[data-gallery]').forEach(gallery => {
	new lightGallery(gallery, {
		plugins: [
			lgZoom, 
			lgThumbnail, 
			lgRotate,
			// lgAutoplay, 
			// lgFullscreen, 
			// lgHash,
			// lgPager,
		],
		selector: 'a',
		speed: 800,
		mode: 'lg-fade',
		mobileSettings: {
			controls: true,
			showCloseIcon: true,
			download: false,
			rotate: true,
		},
	});
});


/**
 * Инициализация видео-галлерей по атрибуту data-video-gallery
 */
document.querySelectorAll('[data-video-gallery]').forEach(videoGallery => {
	new lightGallery(videoGallery, {
		plugins: [
			lgThumbnail, 
			lgVideo,
		],
		selector: 'a',
		speed: 800,
		videojs: true,
		videojsOptions: {
			muted: true,
		},
		mobileSettings: {
			controls: true,
			showCloseIcon: true,
			download: false,
			rotate: true,
		},
	});
});


/**
 * Инициализация механизма увеличения изображений
 */
document.querySelectorAll('figure[data-src]').forEach(zoomFigure => {
	new lightGallery(zoomFigure, {
		plugins: [
			lgMediumZoom,
		],
		selector: 'this',
		mobileSettings: {
			controls: true,
			showCloseIcon: true,
			download: false,
			rotate: true,
		},
	});
});


/**
 * Инициализация открытия различных медия в фрейме
 */
document.querySelectorAll('[data-iframe][data-src]').forEach(frameMedia => {
	new lightGallery(frameMedia, {
		selector: 'this',
		mobileSettings: {
			controls: true,
			showCloseIcon: true,
			download: false,
			rotate: true,
		},
	});
});

