let token = "";

window.addEventListener('keydown', function (e) {
  if (e.key == 'Enter') {
    e.preventDefault(); return false;
  }
}, true);

document.addEventListener("DOMContentLoaded", function () {
  token = (document.getElementById('token') as HTMLInputElement).value;
});

function mealsAjaxUrl(task: string) {
  return `/index.php?option=com_claw&task=${task}&format=raw`;
}

function mealsOptions(data: object) {
  return {
    method: 'POST',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': Joomla.getOptions('csrf.token')
    }
  }
}

function doMealCheckin() {
  const registration_code = (document.getElementById('badgecode') as HTMLInputElement).value;
  if (registration_code == null || registration_code === '') return;

  const mealEvent = (document.getElementById('mealEvent') as HTMLSelectElement).value;
  if (mealEvent == null || mealEvent === '') return;

  const data = {
    registration_code: registration_code,
    mealEvent: mealEvent,
    token: token
  };

  fetch(mealsAjaxUrl('mealCheckin'), mealsOptions(data))
    .then(result => result.json())
    .then(response => {
      document.getElementById('status').innerHTML = response.msg;
      const badgeInput = document.getElementById('badgecode') as HTMLInputElement;
      if (badgeInput) {
        badgeInput.value = response.badge;
      }
    }).catch(error => {
      console.log("Fetch error in doMealCheckin");
    })
}

function clearcode() {
  (document.getElementById('badgecode') as HTMLInputElement).value = '';
}
