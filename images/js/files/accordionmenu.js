if (document.querySelector('.menu-accordion')) {
   // Работа с ссылками верхнего уровня
   const menuAccordion = document.querySelector('.menu-accordion');
   const menuItems = menuAccordion.querySelectorAll('.menu__item');
   menuItems.forEach((item) => {
      if (item.querySelector('ul')) {
         item.querySelector('.menu__link').classList.add('_icon-arrow-menu')
      }
   })
   
   const menuControlBtns = menuAccordion.querySelectorAll('[data-control]');
   const asideMenuContents = Array.from(menuAccordion.querySelectorAll('[data-content]'));
   
   menuControlBtns.forEach((btn) => btn.addEventListener('click', function(e) {
      e.preventDefault();
      if (e.target.classList.contains('_anchor')) {
         window.location.href = e.target.href;
      } else {
         this.classList.toggle('active');
         const menuContent = e.target.closest('.menu__item').querySelector('.menu__sub-list');
      
         if (menuContent.classList.contains('active')) {
            menuContent.classList.remove('active');
         } else {
            menuContent.classList.add('active');
         }
      }
   }))
   
   // Работа с вложенными ссылками
   const menuSubItems = document.querySelector('.menu-accordion').querySelectorAll('.menu__sub-item');
   menuSubItems.forEach((subItem) => {
      if (subItem.querySelector('ul')) {
         subItem.classList.add('expandable');
         subItem.querySelector('.menu__sub-link').classList.add('_icon-arrow-menu');
         subItem.querySelector('.menu__sub-link').setAttribute('data-control-submenu', '');
      }
   })
   
   const submenuControlBtns = document.querySelector('.menu-accordion').querySelectorAll('[data-control-submenu]');
   
   submenuControlBtns.forEach((btn) => btn.addEventListener('click', function(e) {
      e.preventDefault();
      if (e.target.classList.contains('_anchor-sublink')) {
         window.location.href = e.target.href;
      } else {
         this.classList.toggle('active');
         e.target.closest('.menu__sub-item').classList.toggle('active');
         
         const menuContent = e.target.closest('.menu__sub-item').querySelector('.menu__sub-list');
      
         if (menuContent.classList.contains('active')) {
            menuContent.classList.remove('active');
         } else {
            menuContent.classList.add('active');
         }
      }
   }))
}