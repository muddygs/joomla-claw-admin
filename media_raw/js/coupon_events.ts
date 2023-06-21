function getQuantity(): number {
	var n = document.getElementById('jform_quantity') as HTMLInputElement;
	if ( n !== null && n.value !== null ) return parseInt(n.value);
	return 0; 
}

function getFormData() {
	const data = new FormData(document.getElementById('claw-coupon-generator') as HTMLFormElement);
	const value = Object.fromEntries(data.entries());
	return value;
}

function loadEvent() {
	var data = getFormData();

	if (data.eventSelection == "0") return;

	data["action"] = "load";

	const options = {
		method: 'POST',
		body: JSON.stringify(data),
		headers: {
			'Content-Type': 'application/json'
		}
	}

	fetch('/php/coupons/process.php', options)
		.then(result => result.json())
		.then(html => {
			var p = document.getElementById('packagetype');
			var a = document.getElementById('addons');

			if ( p == null || a == null ) return;
			if ( html.length == 2 ) {
				p.innerHTML = html[0];
				a.innerHTML = html[1];

				var n = document.getElementById('value') as HTMLInputElement;
				if (n !== null) n.value = "0";

				jQuery('#package').on('change', function () {
					updateTotalValue();
				});

				// Any checkboxes
				jQuery('input[type=checkbox][id^=addon').on('change', function () {
					updateTotalValue();
				});

			}
		});

}

function updateTotalValue() {
	var data = getFormData();

	data["action"] = "value";

	const options = {
		method: 'POST',
		body: JSON.stringify(data),
		headers: {
			'Content-Type': 'application/json'
		}
	}

	fetch('/php/coupons/process.php', options )
		.then(result => result.json())
		.then(html => {
			var n = document.getElementById('value') as HTMLInputElement;
			if ( n !== null ) n.value = html.value;
		});
}

function getEmailStatus() {
	var data = getFormData();

	data["action"] = "emailstatus";

	const options = {
		method: 'POST',
		body: JSON.stringify(data),
		headers: {
			'Content-Type': 'application/json'
		}
	}

	fetch('/php/coupons/process.php', options )
		.then(result => result.text())
		.then(html => {
			var n = document.getElementById('emailstatus');
			if ( n !== null ) n.innerHTML = html;
		});
}

function copyCoupons() {
	var coupons = document.querySelectorAll("[data-coupon]");

	var text: string = '';

	[...coupons].forEach((e) => {
		text += (e as HTMLElement).dataset.coupon + "\n";
	});

	navigator.clipboard.writeText(text)
		.then(() => {
			alert('Coupons copied to clipboard');
		})
		.catch(err => {
			alert('Error in copying text: '+ err);
		});
}

function generateCoupons() {
	var data = getFormData();

	data["action"] = "generate";

	const options = {
		method: 'POST',
		body: JSON.stringify(data),
		headers: {
			'Content-Type': 'application/json'
		}
	}

	fetch('/php/coupons/process.php', options )
		.then(result => result.text())
		.then(html => {
			var n = document.getElementById('results');
			if ( n !== null ) n.innerHTML = html;
		});
}

function submitCoupons() {
	var result = document.getElementById('results');
	result.textContent = '';

	var error = '';
	var name = document.getElementById('name') as HTMLInputElement;
	var regExp = /[a-zA-Z]/g;
	if ( ! regExp.test(name.value)) error = 'Please set the name.';

	var quantity = parseInt((document.getElementById('quantity') as HTMLInputElement).value);
	if ( ! Number.isInteger(quantity)) error = 'Please set an integer quantity.';

	if ( error != '' )
	{
		var node = document.createElement("p");
		var note = document.createTextNode(error);
		node.appendChild(note);
		result.appendChild(node);
	}
	else
	{
		generateCoupons();
	}
}
