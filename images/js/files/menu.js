let iconMenu = document.querySelector(".icon-menu");
if (iconMenu != null) {
	iconMenu.innerHTML = "<span></span>".repeat(3);
	iconMenu.addEventListener("click", function (e) {
		iconMenu.classList.contains('_active') ? menu_close() : menu_open();
	});
};
function menu_open() {
	document.querySelector(".icon-menu").classList.add("_active");
	document.querySelector(".menu-line").classList.add("_active");
	document.querySelector("body").classList.add("_lock");
}
function menu_close() {
	document.querySelector(".icon-menu").classList.remove("_active");
	document.querySelector(".menu-line").classList.remove("_active");
	document.querySelector("body").classList.remove("_lock");
}