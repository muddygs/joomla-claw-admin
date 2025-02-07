let badgeToken = '';
let page = 'badge-checkin';


window.addEventListener('keydown', function (e) {
  if (e.key == 'Enter') {
      e.preventDefault(); return false; 
    }
}, true);

document.addEventListener("DOMContentLoaded", function () {

  badgeToken = (document.getElementById('token') as HTMLInputElement).value;

  hide('submit');
  hide('submitPrint');
  hide('submitPrintIssue');

  // If batch printing, start auto update on available quantity
  const badgeCount = document.getElementById('badgeCount') as HTMLElement;
  if ( badgeCount !== null ) {
    getBatchCount();
    page = 'badge-print';
  }
});

class checkinSearch {
  name: string;
  id: string;

  constructor(id: string, name: string) {
    this.name = name;
    this.id = id;
  }
}

class checkinRecord {
  badgeId: string;
  issued: string;
  printed: string;
  legalName: string;
  city: string;
  clawPackage: string;
  dinner: string;
  brunch: string;
  buffets: string;
  shifts: string;
  registration_code: string;
  shirtSize: string;
  error: string;
  info: string;

  constructor(badgeId: string, issued: string, printed: string, legalName: string, city: string, clawPackage: string,
    dinner: string, brunch: string, buffets: string, shifts: string, registration_code: string, shirtSize: string, 
    error: string, info: string) {
    this.badgeId = badgeId;
    this.issued = issued;
    this.printed = printed;
    this.legalName = legalName;
    this.city = city;
    this.clawPackage = clawPackage;
    this.dinner = dinner;
    this.brunch = brunch;
    this.buffets = buffets;
    this.shifts = shifts;
    this.registration_code = registration_code;
    this.shirtSize = shirtSize;
    this.error = error;
    this.info = info;
  }
}

function formatSearch(r: any): checkinSearch {
  return { name: r.name, id: r.id };
}

function formatRecord(r: any): checkinRecord {
  return {
    badgeId: r.badgeId,
    issued: r.issued,
    printed: r.printed,
    legalName: r.legalName,
    city: r.city,
    clawPackage: r.clawPackage,
    dinner: r.dinner,
    brunch: r.brunch,
    buffets: r.buffets,
    shifts: r.shifts,
    registration_code: r.registration_code,
    shirtSize: r.shirtSize,
    error: r.error,
    info: r.info
  };
}

function checkinAjaxUrl(task: string) {
	return '/index.php?option=com_claw&task=checkin.' + task + '&format=raw';
}

function checkinOptions(data: object) {
  return {
		method: 'POST',
		body: JSON.stringify(data),
		headers: {
			'Content-Type': 'application/json',
			'X-CSRF-Token': Joomla.getOptions('csrf.token')
		}
	}
}


class SearchService {
  async doSearch(search: string): Promise<checkinSearch[]> {
    const data = {
      search: search,
      token: badgeToken,
      page: page
    };

    const res = await fetch(checkinAjaxUrl('checkin.search'), checkinOptions(data));
    const res_1 = await res.json();
    return res_1.map((s: any) => formatSearch(s));
  }
}

class RecordService {
  async doSearch(registration_code: string): Promise<checkinRecord> {
    const data = {
      registration_code: registration_code,
      token: badgeToken,
      page: page
    };

    const recordResult = await fetch(checkinAjaxUrl('checkin.value'), checkinOptions(data));
    const recordResult_1 = await recordResult.json();
    return formatRecord(recordResult_1);
  }
}

const apiClient = new SearchService();
const apiRecordClient = new RecordService();

function loadRecord() {
  const s = document.getElementById('searchresults') as HTMLSelectElement;
  const v = (s.selectedOptions)[0].value;
  let error:boolean = false;
  hide('submit');
  (document.getElementById("errorMsg") as HTMLElement).innerText = '';
  hide('errorMsg');

  apiRecordClient.doSearch(v).then(results => {
    if ( results.error != '' ) {
      const e = (document.getElementById("errorMsg") as HTMLElement);
      if ( e != null && page == 'badge-checkin') {
        e.innerText = results.error;
        show('errorMsg');
        error = true;
      }
    }

    if ( results.info != '' ) {
      const e = (document.getElementById("infoMsg") as HTMLElement);
      if ( e != null ) {
        e.innerText = results.info;
        show('infoMsg');
      }
    }

    Object.keys(results).forEach(element => {
      if (element != 'registration_code') {
        const e = document.getElementById(element);
        if (e !== null) {
          e.innerText = results[element as keyof checkinRecord];

          if (element == "issued") {
            if (e.innerText == "Issued") {
              e.style.color = "red"
            }
            else {
              e.style.color = "green"
            }
          }
        }
      }
      else {
        const x = document.getElementById('registration_code') as HTMLInputElement;
        x.value = results[element as keyof checkinRecord];

        if ( !error ) {
          if ( page == 'badge-checkin' ) {
            show('submit');
          } else {
            show('submitPrint');
            show('submitPrintIssue');
          }
        }

        hide('status');
      }
    });
  });
}

function doCheckin() {
  //(document.getElementById("submit") as HTMLInputElement)?.removeEventListener('click',doCheckin);
  (document.getElementById('searchresults') as HTMLInputElement)?.removeEventListener('change', loadRecord )

  hide('submit');
  document.getElementById('registration_code').innerHTML = "";
  document.getElementById('searchresults').innerHTML = "";
  (document.getElementById('search') as HTMLInputElement).value = "";

  const registration_code = (document.getElementById('registration_code') as HTMLInputElement).value;
  if (registration_code == null || registration_code === '') return;

  const data = {
    registration_code: registration_code,
    token: badgeToken,
    page: page
  };

  fetch(checkinAjaxUrl('checkin.issue'), checkinOptions(data))
    .then(result => result.json())
    .then(html => {
      show('status');
      document.getElementById('status').innerHTML = '<h2>Badge Issued</h2>';
    });
}

function doPrint(mode:boolean = false) {
  (document.getElementById('searchresults') as HTMLInputElement)?.removeEventListener('change', loadRecord )

  hide('submitPrint');
  hide('submitPrintIssue');

  document.getElementById('registration_code').innerHTML = "";
  document.getElementById('searchresults').innerHTML = "";
  (document.getElementById('search') as HTMLInputElement).value = "";

  const registration_code = (document.getElementById('registration_code') as HTMLInputElement).value;
  if (registration_code == null || registration_code === '') return;

  const action = mode ? 'printissue' : 'print'

  const printUrl = checkinAjaxUrl('checkin.print');
  const ts = Date.now();
  window.open(`${printUrl}&action=${action}&registration_code=${registration_code}&token=${badgeToken}&page=${page}&ts=${ts}`, '_blank');
}

function doBatchPrint(type:number = 0) {
  let batch_quantity = -1;

  switch (type) {
  case 0:
    batch_quantity = parseInt((document.getElementById('batchcount0') as HTMLInputElement).value);  
    break;
  case 1:
    batch_quantity = parseInt((document.getElementById('batchcount1') as HTMLInputElement).value);  
    break;
  case 2:
    batch_quantity = parseInt((document.getElementById('batchcount2') as HTMLInputElement).value);  
    break;
  default:
    return;
  }

  const printUrl = checkinAjaxUrl('checkin.print');
  const ts = Date.now();
  window.open(`${printUrl}&action=printbatch&quantity=${batch_quantity}&token=${badgeToken}&page=${page}&type=${type}&ts=${ts}`, '_blank');
}

function clearDisplay() {
  document.getElementsByName('info').forEach(td => {
    td.innerText = "";
  });
  (document.getElementById('registration_code') as HTMLInputElement).value = "";
  hide('status');
  hide('errorMsg');
  hide('infoMsg');
}

function getBatchCount() {
  var data = {
    token: badgeToken
  };

  fetch(checkinAjaxUrl('checkin.count'), checkinOptions(data))
    .then(result => result.json())
    .then(json => {
      document.getElementById('attendeeCount').innerHTML = json.attendee;
      document.getElementById('volunteerCount').innerHTML = json.volunteer;
      document.getElementById('remainderCount').innerHTML = json.remainder;
      document.getElementById('badgeCount').innerHTML = json.all;
    });

  setTimeout(() => {
    getBatchCount();
  }, 30000);
}



function searchChange() {
  const searchField = document.getElementById('search') as HTMLInputElement;
  if (searchField === null) return;

  clearDisplay();
  hide('submit');
  hide('submitPrint');
  hide('submitPrintIssue');

  const searchValue = searchField.value;
  apiClient.doSearch(searchValue).then(results => {
    const resultField = document.getElementById('searchresults') as HTMLSelectElement;
    if (resultField === null) return;

    resultField.removeEventListener('change', loadRecord )
    resultField.addEventListener('change', loadRecord )

    resultField.innerHTML = "";

    results.forEach(s => {
      const k = s.id;
      const t = s.name;

      const o = new Option(t, k);
      resultField.append(o);
    });
  })
}
function show(e: string):void {
  (document.getElementById(e) as HTMLElement)?.setAttribute("style","");
}
function hide(e: string): void {
  (document.getElementById(e) as HTMLElement)?.setAttribute("style","display:none;");
}
