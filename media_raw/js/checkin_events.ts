window.addEventListener('keydown', function (e) {
  if (e.key == 'Enter') {
    e.preventDefault(); return false;
  }
}, true);

function badgeprintControllerTask(task: string) {
  return '/index.php?option=com_claw&task=badgeprint.' + task + '&format=raw';
}

function doPrint(mode: boolean = false) {
  document.getElementById('registration_code').innerHTML = "";
  document.getElementById('searchresults').innerHTML = "";
  (document.getElementById('search') as HTMLInputElement).value = "";

  const registration_code = (document.getElementById('registration_code') as HTMLInputElement).value;
  if (registration_code == null || registration_code === '') return;

  const action = mode ? 'printissue' : 'print'

  const printUrl = badgeprintControllerTask('print');
  const ts = Date.now();
  var badgeToken = (document.getElementById('token') as HTMLInputElement).value;
  window.open(`${printUrl}&action=${action}&registration_code=${registration_code}&token=${badgeToken}&&ts=${ts}`, '_blank');
}

function doBatchPrint(type: number = 0) {
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

  const printUrl = badgeprintControllerTask('print');
  const ts = Date.now();
  var badgeToken = (document.getElementById('token') as HTMLInputElement).value;
  window.open(`${printUrl}&action=printbatch&quantity=${batch_quantity}&token=${badgeToken}&type=${type}&ts=${ts}`, '_blank');
}

function clearDisplay() {
  document.getElementsByName('info').forEach(td => {
    td.innerText = "";
  });
  (document.getElementById('registration_code') as HTMLInputElement).value = "";
  (document.getElementById('status') as HTMLInputElement).value = "";
  (document.getElementById('errorMsg') as HTMLInputElement).value = "";
  (document.getElementById('infoMsg') as HTMLInputElement).value = "";
  (document.getElementById('search') as HTMLInputElement).value = "";
  (document.getElementById('searchresults') as HTMLSelectElement).innerHTML = "";
}
