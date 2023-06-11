jQuery(function () {
  // Code copied from elsewhere in eventbooking to support registration separate from
  // their registration pages
  var EBBaseAjaxUrl = "$baseAjaxUrl";
  var getUrlParameter = function getUrlParameter(sParam: string) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
  };

  var registerButtons = [].slice.call(document.querySelectorAll('.eb-register-button:not(.eb-colorbox-addcart):not(.eb-join-waiting-list-individual-button')) as HTMLAnchorElement[];
  registerButtons.map(function (thing) {
    thing.addEventListener('click', function (event) {
      event.preventDefault();
      fetch(this.href)
        .then(() => cartToast());
    })
  });
});

function cartToast() {
  var toastElList = [].slice.call(document.querySelectorAll('.toast'))

  var toastList = toastElList.map(function (toastEl) {
    return new bootstrap.Toast(toastEl)
  });
  toastList.forEach(toast => toast.show());
}
  
// function cartAdd(eventId: number) {

// }

// function updateCart() {
//   const cartDiv = document.getElementById('cart_result');
//   if (cartDiv == null) return;

//   var url = '/index.php?option=com_eventbooking&view=cart&layout=module&format=raw';
//   fetch(url )
//   .then(result => result.text())
//     .then(html => {
//       const cartDiv = document.getElementById('cart_result');
//       if ( cartDiv !== null ) cartDiv.innerHTML = html; 
//   });

// }
