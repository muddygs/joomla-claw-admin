var mealToken = "";
const mealCheckinUrl = "/php/checkin/mealsProcess.php"

function doMealCheckin() {
  var registration_code = (document.getElementById('badgecode') as HTMLInputElement).value;
  if ( registration_code == null || registration_code === '' ) return;

  var mealEvent = (document.getElementById('mealEvent') as HTMLSelectElement).value;
  if ( mealEvent == null || mealEvent === '' ) return;

  var data = {
    action: "checkin",
    registration_code: registration_code,
    mealEvent: mealEvent,
    token: mealToken
  };

  const options = {
    method: 'POST',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/json'
    }
  };

  fetch(mealCheckinUrl, options)
    .then(result => result.json())
    .then(html => {
      document.getElementById('status').innerHTML = html.msg;
      (document.getElementById('badgecode') as HTMLInputElement).value = html.badge;
    }).catch( error => {
      console.log("Fetch error in doMealCheckin");
      setTimeout(() => {
        doMealCheckin();
      }, 500);
    })
}

jQuery(function () {
  jQuery(window).on('keydown', function (event) {
    if (event.key == 'Enter') {
      event.preventDefault();
      return false;
    }
  });

  mealToken = (document.getElementById('token') as HTMLInputElement).value;

  jQuery('#badgecode').on('change', function() {
    doMealCheckin();
  });

  jQuery('#badgecode').on('click', function() {
    (document.getElementById('badgecode') as HTMLInputElement).value = '';
  });
});

