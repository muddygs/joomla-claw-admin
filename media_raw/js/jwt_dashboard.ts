window.addEventListener('keydown', function (e) {
  if (e.key == 'Enter') {
      e.preventDefault(); return false; 
    }
}, true);

function jwtdashboardAjaxUrl(task: string) {
  return `/index.php?option=com_claw&view=jwt&task=jwt.${task}&format=raw`;
}

function jwtdashboardOptions(id: number) {
  const data = {
    tokenid: id
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
  fetch(jwtdashboardAjaxUrl('jwtdashboardConfirm'), jwtdashboardOptions(id))
    .then(result => result.json())
    .then(html => {
      if ( html.id == id ) {
        const b = document.getElementById('dbrdc' + id) as HTMLInputElement;
        if ( b != null ) b.remove();
      }
    });
}

function doRevoke(id: number) {
  fetch(jwtdashboardAjaxUrl('jwtdashboardRevoke'), jwtdashboardOptions(id))
    .then(result => result.json())
    .then(html => {
      let b = document.getElementById('dbrdc' + html.id) as HTMLInputElement;
      if (b != null) b.remove();
      b = document.getElementById('dbrdr' + html.id) as HTMLInputElement;
      if (b != null) b.remove();
    });

}
