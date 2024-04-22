var isMobile = { 
	Android: function () { return navigator.userAgent.match(/Android/i); }, 
	BlackBerry: function () { return navigator.userAgent.match(/BlackBerry/i); }, 
	iOS: function () { return navigator.userAgent.match(/iPhone|iPad|iPod/i); }, 
	Opera: function () { return navigator.userAgent.match(/Opera Mini/i); }, 
	Windows: function () { return navigator.userAgent.match(/IEMobile/i); }, 
	any: function () { return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows()); } 
};

window.addEventListener("load", function () {
	if (document.querySelector('.wrapper')) {
		setTimeout(function () {
			document.querySelector('.wrapper').classList.add('_loaded');
		}, 0);
	}
});

//========================================
//ActionsOnHash
if (location.hash) {
	const hsh = location.hash.replace('#', '');
	if (document.querySelector('.popup_' + hsh)) {
		popup_open(hsh);
	} else if (document.querySelector('div.' + hsh)) {
		_goto(document.querySelector('.' + hsh), 500, '');
	}
}

//========================================
//renameClasses
function _renameClasses(el, class_name) {
	el.forEach(element => {
		element.className = class_name;
	});
}

//========================================
//AddClasses
function _addClasses(el, class_name) {
	el.forEach(element => {
		element.classList.add(class_name);
	});
}

//========================================
//RemoveClasses
function _removeClasses(el, class_name) {
	el.forEach(element => {
		element.classList.remove(class_name);
	});
}

//========================================
//IsHidden
function _is_hidden(el) {
	return (el.offsetParent === null)
}

//========================================
//get cookie
function getCookie(name) {
	var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
	return matches ? decodeURIComponent(matches[1]) : undefined;
}