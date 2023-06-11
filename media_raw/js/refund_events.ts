window.addEventListener('keydown', function (e) {
	 if (e.key == 'Enter') {
			 e.preventDefault(); return false; 
		 }
}, true);

function removeOptions(selectElement: HTMLElement) {
	if (selectElement.hasChildNodes()) {
		var options = selectElement.children;

		var i: number, L = options.length - 1;
		for (i = L; i >= 0; i--) {
			options[i].remove();
		}
	}
}

function populateEventDiv(_invoice: string) {
	var button = (document.getElementById('refundSubmit') as HTMLInputElement);
	button.disabled = true;

	var amount = (document.getElementById('refundAmount') as HTMLInputElement);
	amount.value = "0";

	fetch('/php/refunds/process.php?action=populate&invoice=' + _invoice + '&time=' + Date.now())
		.then(result => result.text())
		.then(html => {
			const n = document.getElementById("events");

			if (n !== null) {
				n.innerHTML = html;
				updateRefundSelect();
				updateProfileSelect();
			
				var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
				var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
					return new bootstrap.Tooltip(tooltipTriggerEl)
				});
			}	
		});
}

function updateRefundSelect() {
	let selects: { [key: string]: number } = {};

	var refundInfo = document.querySelectorAll("div[id^=refund]");
	refundInfo.forEach(element => {
		var id = element.id.split(':');
		var t = id[1];
		var a = parseFloat(id[2]);
		if (t in selects) {
			selects[t] += a;
		}
		else {
			selects[t] = a;
		}
	});

	var nts = document.getElementById('refundSelect');
	if (nts !== null) {
		removeOptions(nts);
		var opt = document.createElement("option");
		opt.value="";
		opt.innerHTML = "Select Refund Transaction";
		nts.appendChild(opt);

		for (var key in selects) {
			opt = document.createElement("option");
			opt.value = key;
			opt.innerHTML = key + ' ($' + selects[key].toFixed(2) + ')';
			nts.appendChild(opt);
		}
	}
}

function updateProfileSelect() {
	let selects: { [key: string]: number } = {};

	var refundInfo = document.querySelectorAll("span[id^=profile]");
	refundInfo.forEach(element => {
		var id = element.id.split(':');
		var regcode = id[1];
		var profileid = parseInt(id[2]);
		if ( regcode != '' && profileid > 0 ) selects[regcode] = profileid;
	});

	var nts = document.getElementById('profileSelect');
	if (nts !== null) {
		removeOptions(nts);
		var opt = document.createElement("option");
		opt.value="";
		opt.innerHTML = "Select Charge Profile";
		nts.appendChild(opt);

		for (var key in selects) {
			opt = document.createElement("option");
			opt.value = key;
			opt.innerHTML = selects[key].toString();
			nts.appendChild(opt);
		}
	}
}

function getInvoice(): string {
	var n = document.getElementById('invoice') as HTMLInputElement;
	if (n !== null && n.value !== null) return n.value
	return '';
}

function doPopulate() {
	var invoice = getInvoice();
	if (invoice != '') populateEventDiv(invoice);
}

function validateRefundAmount() {
	var button = (document.getElementById('refundSubmit') as HTMLInputElement);
	button.disabled = true;

	var transaction: number = parseInt((document.getElementById('refundSelect') as HTMLSelectElement).value, 10);
	var refund = 0;
	var n = document.getElementById('refundAmount') as HTMLInputElement;
	if (n !== null && n.value !== null) refund = parseFloat(n.value);
	if (refund > 0 && transaction > 0) button.disabled = false;
}

function validateProfileAmount() {
	var button = (document.getElementById('profileSubmit') as HTMLInputElement);
	button.disabled = true;

	var transaction: string = (document.getElementById('profileSelect') as HTMLSelectElement).value;
	var refund = 0;
	var n = document.getElementById('profileAmount') as HTMLInputElement;
	if (n !== null && n.value !== null) refund = parseFloat(n.value);
	if (refund > 0 && transaction != '') button.disabled = false;
}

function processRefund() {
	var refund = (document.getElementById('refundAmount') as HTMLInputElement).value;
	var transaction = (document.getElementById('refundSelect') as HTMLSelectElement).value;
	var cancelall = (document.getElementById('cancelall') as HTMLInputElement).checked;

	fetch('/php/refunds/process.php?action=refund&transaction=' + encodeURIComponent(transaction) +
		 '&amount=' + encodeURIComponent(refund) +
		 '&cancelall=' + encodeURIComponent(cancelall) + 
		 '&time=' + Date.now())
		.then(result => result.text())
		.then(html => {
			var n = document.getElementById('results');
			if (n !== null) n.innerHTML = html;
		});
}

function processProfile() {
	var charge = (document.getElementById('profileAmount') as HTMLInputElement).value;
	var regcode = (document.getElementById('profileSelect') as HTMLSelectElement).value;
	var receipt = (document.getElementById('profileDescription') as HTMLInputElement).value;

	fetch('/php/refunds/process.php?action=charge&regcode=' + encodeURIComponent(regcode) + 
		'&amount=' + encodeURIComponent(charge) + 
		'&receipt=' + encodeURIComponent(receipt) + 
		'&time=' + Date.now())
		.then(result => result.text())
		.then(html => {
			var n = document.getElementById('results');
			if (n !== null) n.innerHTML = html;
		});
	}
