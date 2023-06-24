window.addEventListener('keydown', function (e) {
  if (e.key == 'Enter') {
      e.preventDefault(); return false; 
    }
}, true);

function validateCoupon() {
  const form = new FormData(document.getElementById('registration-survey-coupon') as HTMLFormElement);
  const data = Object.fromEntries(form.entries());

  data["action"] = "couponstatus";

  const options = {
    method: 'POST',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': Joomla.getOptions('csrf.token')
    }
  }

  fetch('/index.php?option=com_claw&task=validatecoupon&format=raw', options )
    .then(result => result.json())
    .then(html => {
      if (html.error != 0) {
        var couponError = document.getElementById('couponerror') as HTMLDivElement;
        couponError.classList.remove('d-none');
        setTimeout(() => {
          clearCouponError();
        }, 10000);
      } else {
        window.location = html.link;
      }
    });
}

function clearCouponError() {
  var couponError = document.getElementById('couponerror') as HTMLDivElement;
  couponError.classList.add('d-none');
}
