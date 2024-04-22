let mainMap;

// Дождёмся загрузки API и готовности DOM.
ymaps.ready(init);

function init() {
	if (!document.getElementById('map')) return;

	//Создаём карту
	mainMap = new ymaps.Map('map', {
		center: [56.749500, 37.199065],
		zoom: 15,
		controls: ['zoomControl'],
	});

	//Устанавливаем маркер
	mainMap.geoObjects.add(new ymaps.Placemark([56.749500, 37.199065], {
		balloonContent: '<strong>ФБУЗ МСЧ № 9 ФМБА РОССИИ</strong>',
	}, {
			preset: 'islands#dotIcon',
			iconColor: '#E35946',
	}));

	//Убираем возможность скролить и перетаскивать
	mainMap.behaviors.disable('scrollZoom');
	mainMap.behaviors.disable('multiTouch');
	mainMap.behaviors.disable('drag');
}