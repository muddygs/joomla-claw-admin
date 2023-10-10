const colTitle = "col-4 col-lg-6";
const colCheckin = "col-4 col-lg-3";
const colCheckout = "col-4 col-lg-3";
const url = new URL(window.location.href);

var rollcallToken = "";
var shiftId = "";

document.addEventListener("DOMContentLoaded", function () {
  rollcallToken = (document.getElementById('token') as HTMLInputElement).value;
});

function volunteerAjaxUrl(task: string) {
  return `/index.php?option=com_claw&view=checkin&task=${task}&format=raw`;
}

function volunteerOptions(data: object) {
  return {
    method: 'POST',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': Joomla.getOptions('csrf.token')
    }
  }
}

class volunteerSearch {
  name: string;
  uid: number;
  regid: number;
  title: string;
  checkin: boolean;
  checkout: boolean;

  constructor(name: string, uid: number, regid: number, title: string, checkin: boolean, checkout: boolean) {
    this.name = name;
    this.uid = uid;
    this.regid = regid;
    this.title = title;
    this.checkin = checkin;
    this.checkout = checkout;
  }
}

function formatVolunteerSearch(r: any): volunteerSearch {
  return { name: r.name, uid: r.uid, regid: r.regid, title: r.title, checkin: r.checkin, checkout: r.checkout }
}

class volunteerSearchService {
  async doSearch(regid: string): Promise<volunteerSearch[]> {
    const data = {
      token: rollcallToken,
      regid: regid
    };

    const res = await fetch(volunteerAjaxUrl('volunteerSearch'), volunteerOptions(data));
    const res_1 = await res.json();
    return res_1.map((s: any) => formatVolunteerSearch(s));
  }
}

const apiVolunteerSearchService = new volunteerSearchService();


function getVolunteerStatusRow(record: volunteerSearch): string {
  const buttonInClass = record.checkin == false ? 'btn-danger' : 'btn-success';
  const buttonOutClass = record.checkout == false ? 'btn-danger' : 'btn-success';

  const buttonIn = `<button type="button" id="e${record.regid}" class="btn btn-lg mt-2 mb-2 ${buttonInClass}" onClick="${getVolunteerButton(record.regid, 'checkin', record.checkin)}">IN</button>`;
  const buttonOut = `<button type="button" id="f${record.regid}" class="btn btn-lg mt-2 mb-2 ${buttonOutClass}" onClick="${getVolunteerButton(record.regid, 'checkout', record.checkout)}">OUT</button>`;

  return `
  <div class="row row-striped align-items-center">
    <div class="${colTitle}">${record.title}</div>
    <div class="${colCheckin}">${buttonIn}</div>
    <div class="${colCheckout}">${buttonOut}</div>
  </div>
`;
}

function getVolunteerButton(regid: number, action: string, value: boolean) {
  return "updateVolunteerStatus('" + regid + "','" + action + "', " + value + ");"
}

function updateVolunteerStatus(regid: number, action: string, undo: boolean) {
  if (undo == true && !confirm("Press OK to undo the check in/out for this volunteer.")) {
    return;
  }

  var data = {
    token: rollcallToken,
    regid: regid,
    action: action,
    currentValue: undo
  };

  fetch(volunteerAjaxUrl('volunteerUpdate'), volunteerOptions(data))
    .then(result => result.text())
    .then(result => {
      if (result != 'ok') {
        alert('Status change failed. Are you still authenticated?')
        return;
      }
      updateButton(regid, action, undo);
    });
}

function updateButton(regid: number, button: string, undo: boolean) {
  var buttonCheckin = 'e' + regid;
  var buttonCheckout = 'f' + regid;

  if (button == 'checkin') {
    if (undo == false) {
      var b = document.getElementById(buttonCheckin) as HTMLElement;
      b.classList.remove('btn-danger');
      b.classList.add('btn-success')
      b.setAttribute('onclick', getVolunteerButton(regid, 'checkin', true))
    } else {
      var b = document.getElementById(buttonCheckin) as HTMLElement;
      b.classList.remove('btn-success');
      b.classList.add('btn-danger')
      b.setAttribute('onclick', getVolunteerButton(regid, 'checkin', false))

      b = document.getElementById(buttonCheckout) as HTMLElement;
      b.classList.remove('btn-success');
      b.classList.add('btn-danger')
      b.setAttribute('onclick', getVolunteerButton(regid, 'checkout', false))
    }
  }
  else if (button == 'checkout') {
    if (undo == false) {
      var b = document.getElementById(buttonCheckout) as HTMLElement;
      b.classList.remove('btn-danger');
      b.classList.add('btn-success')
      b.setAttribute('onclick', getVolunteerButton(regid, 'checkout', true))
    } else {
      var b = document.getElementById(buttonCheckout) as HTMLElement;
      b.classList.remove('btn-success');
      b.classList.add('btn-danger')
      b.setAttribute('onclick', getVolunteerButton(regid, 'checkout', false))
    }
  } else {
    alert("An error has occurred. Please reload this page to continue. No changes will be lost.");
  }
}

function fetchVolunteerData() {
  const regid = (document.getElementById('regid') as HTMLInputElement).value;

  apiVolunteerSearchService.doSearch(regid).then(results => {
    const v = document.getElementById('shifts');
    v.innerHTML = `
      <div class="row row-striped">
        <div class="${colTitle}">Shift Title</div>
        <div class="${colCheckin}">Checkin</div>
        <div class="${colCheckout}">Checkout</div>
      </div>`;

    if ( results.length == 0 ) {
      v.innerHTML += '<div class="row row-striped"><div class="col-12">No shifts found.</div></div>';
    } else {
      const nameId = document.getElementById('name');
      nameId.innerHTML = results[0].name;
      const uidId = document.getElementById('uid') as HTMLInputElement;
      uidId.value = results[0].uid.toString();

      results.forEach(s => {
        v.innerHTML += getVolunteerStatusRow(s);
      });
    }
  })
}

function clearVolunteerData() {
  const nameId = document.getElementById('name');
  nameId.innerHTML = '';
  const uidId = document.getElementById('uid') as HTMLInputElement;
  uidId.value = '0';
  const v = document.getElementById('shifts');
  v.innerHTML = '';
  const regid = document.getElementById('regid') as HTMLInputElement;
  regid.value = '';
}

function addShift() {
  const uid = (document.getElementById('uid') as HTMLInputElement).value;

  const e = document.getElementById('shift-items') as HTMLSelectElement;
  const sel = e.selectedIndex;
  const opt = e.options[sel];
  const shift = opt.value;

  const regid = document.getElementById('regid') as HTMLInputElement;
  const regidValue = regid.value;

  const data = {
    token: rollcallToken,
    uid: uid,
    shift: shift
  };

  fetch(volunteerAjaxUrl('volunteerAddShift'), volunteerOptions(data))
    .then(result => result.text())
    .then(result => {
      if (result != 'ok') {
        alert('Shift add failed. Are you still authenticated?')
        return;
      }
      alert('Shift added.');
      regid.value = regidValue;
      fetchVolunteerData();
    });
}
