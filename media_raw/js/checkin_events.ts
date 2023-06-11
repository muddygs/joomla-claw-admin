var badgeToken = "";
const checkinUrl = '/php/checkin/badgeStationCheckinProcess.php'
const printUrl = '/php/checkin/badgePrintingProcess.php'

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
  var badgeCount = document.getElementById('badgeCount') as HTMLElement;
  if ( badgeCount !== null ) {
    setTimeout(() => {
      getBatchCount();
    }, 60000);
  }

  (document.getElementById("search") as HTMLInputElement)?.addEventListener("change",searchChange);
  (document.getElementById('submitBatch') as HTMLInputElement)?.addEventListener('click', doBatchPrint );
  (document.getElementById("submit") as HTMLInputElement)?.addEventListener('click',doCheckin);
  (document.getElementById("submitPrint") as HTMLInputElement)?.addEventListener('click',function(){doPrint()});
  (document.getElementById("submitPrintIssue") as HTMLInputElement)?.addEventListener('click',function(){doPrint(true)});
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

class SearchService {
  async doSearch(search: string): Promise<checkinSearch[]> {
    var scope = 'single';
    if (document.getElementById('submitPrint') != null) scope = 'any';

    var data = {
      action: "search",
      search: search,
      token: badgeToken,
      scope: scope
    };

    const options = {
      method: 'POST',
      body: JSON.stringify(data),
      headers: {
        'Content-Type': 'application/json'
      }
    };

    const res = await fetch(checkinUrl, options);
    const res_1 = await res.json();
    return res_1.map((s: any) => formatSearch(s));
  }
}

class RecordService {
  async doSearch(registration_code: string): Promise<checkinRecord> {
    var data = {
      action: "value",
      registration_code: registration_code,
      token: badgeToken
    };

    const options = {
      method: 'POST',
      body: JSON.stringify(data),
      headers: {
        'Content-Type': 'application/json'
      }
    };

    const recordResult = await fetch(checkinUrl, options);
    const recordResult_1 = await recordResult.json();
    return formatRecord(recordResult_1);
  }
}

const apiClient = new SearchService();
const apiRecordClient = new RecordService();

function loadRecord() {
  var s = document.getElementById('searchresults') as HTMLSelectElement;
  var v = (s.selectedOptions)[0].value;
  var error:boolean = false;
  hide('submit');

  apiRecordClient.doSearch(v).then(results => {
    if ( results.error != '' ) {
      var e = (document.getElementById("errorMsg") as HTMLElement);
      if ( e != null ) {
        e.innerText = results.error;
        show('errorMsg');
      }
      error = true;
    }

    if ( results.info != '' ) {
      var e = (document.getElementById("infoMsg") as HTMLElement);
      if ( e != null ) {
        e.innerText = results.info;
        show('infoMsg');
      }
    }

    Object.keys(results).forEach(element => {
      if (element != 'registration_code') {
        var e = document.getElementById(element);
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
        var x = document.getElementById('registration_code') as HTMLInputElement;
        x.value = results[element as keyof checkinRecord];

        if ( !error ) {
          (document.getElementById("errorMsg") as HTMLElement).innerText = '';
          show('submit');
        }
        show('submitPrint');
        show('submitPrintIssue');
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

  var registration_code = (document.getElementById('registration_code') as HTMLInputElement).value;
  if (registration_code == null || registration_code === '') return;

  var data = {
    action: "checkin",
    registration_code: registration_code,
    token: badgeToken
  };

  const options = {
    method: 'POST',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/json'
    }
  };

  fetch(checkinUrl, options)
    .then(result => result.json())
    .then(html => {
      show('status');
      document.getElementById('status').innerHTML = "<h2>Badge Issued</h2>";
    });
}

function doPrint(mode:boolean = false) {
  (document.getElementById('searchresults') as HTMLInputElement)?.removeEventListener('change', loadRecord )

  hide('submitPrint');
  hide('submitPrintIssue');

  document.getElementById('registration_code').innerHTML = "";
  document.getElementById('searchresults').innerHTML = "";
  (document.getElementById('search') as HTMLInputElement).value = "";

  var registration_code = (document.getElementById('registration_code') as HTMLInputElement).value;
  if (registration_code == null || registration_code === '') return;

  var codes = [registration_code];
  var action = mode ? 'printissue' : 'print'

  window.open(printUrl + '?action=' + action + '&registration_code=' + JSON.stringify(codes) + '&token=' + badgeToken, '_blank');
}

function doBatchPrint() {
  var batch_quantity = (document.getElementById('batchcount') as HTMLInputElement).value;
  window.open(printUrl + '?action=printbatch&quantity=' + batch_quantity + '&token=' + badgeToken, '_blank');
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
    action: "getcount",
    token: badgeToken
  };

  const options = {
    method: 'POST',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/json'
    }
  };

  fetch(printUrl, options)
    .then(result => result.text())
    .then(html => {
      document.getElementById('badgeCount').innerHTML = html;
    });

  setTimeout(() => {
    getBatchCount();
  }, 30000);
}



function searchChange() {
  var searchField = document.getElementById('search') as HTMLInputElement;
  if (searchField === null) return;

  clearDisplay();
  hide('submit');
  hide('submitPrint');
  hide('submitPrintIssue');

  var searchValue = searchField.value;
  apiClient.doSearch(searchValue).then(results => {
    var resultField = document.getElementById('searchresults') as HTMLSelectElement;
    if (resultField === null) return;

    resultField.removeEventListener('change', loadRecord )
    resultField.addEventListener('change', loadRecord )

    resultField.innerHTML = "";

    results.forEach(s => {
      var k = s.id;
      var t = s.name;

      var o = new Option(t, k);
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
