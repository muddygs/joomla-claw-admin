function getQuantity(): number {
	const n = document.getElementById('jform_quantity') as HTMLInputElement;
	if ( n !== null && n.value !== null ) return parseInt(n.value);
	return 0; 
}

function getCouponFormData(): { [k: string]: FormDataEntryValue; } {
	const data = new FormData(document.getElementById('claw-coupon-generator') as HTMLFormElement);
	const value = Object.fromEntries(data.entries());
	return value;
}

function couponAjaxUrl(task: string): string {
	return '/administrator/index.php?option=com_claw&task=' + task + '&format=raw';
}

function couponOptions(): { method: string; body: string; headers: { 'Content-Type': string; 'X-CSRF-Token': string; }; } {
	return {
		method: 'POST',
		body: JSON.stringify(getCouponFormData()),
		headers: {
			'Content-Type': 'application/json',
			'X-CSRF-Token': Joomla.getOptions('csrf.token')
		}
	}
}

function loadEvent() {
	const data = getCouponFormData();
	const task = "couponLoadEvent";

	if (!data.hasOwnProperty("jform[event]") || data["jform[event]"] == "0") return;

	fetch(couponAjaxUrl(task), couponOptions() )
		.then(result => result.json())
		.then(html => {
			var p = document.getElementById('jform_packagetype');
			var a = document.getElementById('jform_addons');

			if ( p == null || a == null ) return;
			if ( html.length == 2 ) {
				// TODO: Lazy -- should create elements and append
				p.innerHTML = html[0];
				a.innerHTML = html[1];

				var n = document.getElementById('jform_value') as HTMLInputElement;
				if (n !== null) n.value = "0";
			}
		});

}

function updateTotalValue() {
	const task = "couponValue";
	const n = document.getElementById('jform_value') as HTMLInputElement;
	n.value = '0';

	fetch(couponAjaxUrl(task), couponOptions() )
		.then(result => result.text())
		.then(html => {
			if ( n !== null ) n.value = html;
		});
}

function getEmailStatus() {
	const task = "emailStatus";
	const n = document.getElementById('emailstatus');
	n.innerHTML = '';

	fetch(couponAjaxUrl(task), couponOptions() )
		.then(result => result.text())
		.then(msg => {
			if ( n !== null ) n.innerHTML = msg;
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

function createCoupons() {
	var task = "createCoupons";

	fetch(couponAjaxUrl(task), couponOptions() )
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
	var name = document.getElementById('jform_name') as HTMLInputElement;
	var regExp = /[a-zA-Z]/g;
	if ( ! regExp.test(name.value)) error = 'Please set the name.';

	var quantity = parseInt((document.getElementById('jform_quantity') as HTMLInputElement).value);
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
		createCoupons();
	}
}

document.addEventListener("DOMContentLoaded", function () {
	loadEvent();
	document.getElementById('jform_packagetype').addEventListener("change", updateTotalValue);
});