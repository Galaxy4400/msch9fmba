window.addEventListener('load', 
   function () {
      if (!document.querySelector('.smart-room-table')) return;
      
      const smartRoomTable = document.querySelector('.smart-room-table');
      const initialTableContent = smartRoomTable.innerHTML;
      
      const displayMobileTable = function () {
         smartRoomTable.innerHTML = initialTableContent;
         const rows = Array.from(smartRoomTable.querySelectorAll('tr'));
         
         rows.map((row) => {
            if (row.querySelector('th')) {
               row.style.display = 'none';
               return row;
            }
            if (row.querySelector('td').colSpan === 2) return row;
         
            const cells = Array.from(row.querySelectorAll('td'));
            const service = cells[0].innerHTML;
            const price = cells[1].innerHTML;
      
            cells[0].innerHTML = "Услуга: ";
            cells[1].innerHTML = service;
         
            const priceRow = document.createElement('tr');
            priceRow.classList.add('smart-room-table__price');
            priceRow.innerHTML = `
               <td>
                  <p>&nbsp;Цена: </p>
               </td>
               <td>
                  <span>&nbsp;${price}</span>
               </td>
            `;
            row.insertAdjacentElement('afterend', priceRow);
         })
      
         const stripedRows = rows.filter(
            row => !row.querySelector('th') && row.querySelector('td').colSpan !== 2
         );
         stripedRows.forEach((row, i) => {
            if (i % 2 === 0) {
            row.classList.add('grey-row');
            row.nextElementSibling.classList.add('grey-row');
            }
         });
      }
      
      const displayDesktopTable = function () {
         smartRoomTable.innerHTML = initialTableContent; 
         
         const rows = Array.from(smartRoomTable.querySelectorAll('tr'));
         const stripedRows = rows.filter(
            row => !row.querySelector('th') && row.querySelector('td').colSpan !== 2
         );
         stripedRows.forEach((row, i) => {
            if (i % 2 === 0) {
               row.classList.add('grey-row');
            }
         });
      };
      
      const changeView = function() {
         if (document.querySelector('html').clientWidth <= 1400) {
            displayMobileTable();
         } else {
            displayDesktopTable();
         }
      }
      changeView();
      
      window.addEventListener('resize', function() {
         changeView();
      })
   }
)