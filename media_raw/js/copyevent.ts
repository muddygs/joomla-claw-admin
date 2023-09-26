function copyeventAjaxUrl(task: string) {
  return `/administrator/index.php?option=com_claw&view=eventcopy&task=${task}&format=raw`;
}

function getCopyEventFormData(): { [k: string]: FormDataEntryValue; } {
	const data = new FormData(document.getElementById('claw-copy-event') as HTMLFormElement);
	const value = Object.fromEntries(data.entries());
	return value;
}

function copyeventOptions(): { method: string; body: string; headers: { 'Content-Type': string; 'X-CSRF-Token': string; }; } {
	return {
		method: 'POST',
		body: JSON.stringify(getCopyEventFormData()),
		headers: {
			'Content-Type': 'application/json',
			'X-CSRF-Token': Joomla.getOptions('csrf.token')
		}
	}
}

function copyEvent() {
	const blaa = copyeventOptions();
	console.log(blaa);

  fetch(copyeventAjaxUrl('doCopyEvent'), copyeventOptions())
    .then(result => result.text())
    .then(html => {
      document.getElementById('results').innerHTML = html;
  });
}