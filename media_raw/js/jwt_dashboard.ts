window.addEventListener('keydown', function (e) {
  if (e.key == 'Enter') {
      e.preventDefault(); return false; 
    }
}, true);

function jwtdashboardAjaxUrl(task: string) {
	return '/administrator/index.php?option=com_claw&task=' + task + '&format=raw';
}

function jwtdashboardOptions(action: string, id: number) {
  var data = {
    action: action,
    id: id
  };

  return {
		method: 'POST',
		body: JSON.stringify(data),
		headers: {
			'Content-Type': 'application/json',
			'X-CSRF-Token': Joomla.getOptions('csrf.token')
		}
	}
}

function doConfirm(id: number) {
  fetch(jwtdashboardAjaxUrl('jwtdashboardConfirm'), jwtdashboardOptions('confirm', id))
    .then(result => result.json())
    .then(html => {
      var b = document.getElementById('dbrdc' + html.id) as HTMLInputElement;
      if ( b != null ) b.remove();
    });
}

function doRevoke(id: number) {
  fetch(jwtdashboardAjaxUrl('jwtdashboardRevoke'), jwtdashboardOptions('revoke', id))
    .then(result => result.json())
    .then(html => {
      var b = document.getElementById('dbrdc' + html.id) as HTMLInputElement;
      if (b != null) b.remove();
      b = document.getElementById('dbrdr' + html.id) as HTMLInputElement;
      if (b != null) b.remove();
    });

}
