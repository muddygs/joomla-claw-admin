const colName = "col-6 col-lg-4";
const colBadge = "col-6 col-lg-2";
const colCheckin = "col-6 col-lg-3";
const colCheckout = "col-6 col-lg-3";
const url = new URL(window.location.href);

var rollcallToken = "";
var shiftId = "";

class volunteerSearch {
	name: string;
	regid: number;
	badgeid: string;
	checkin: number;
	checkout: number;

	constructor(name: string, regid: number, badgeid: string, checkin: number, checkout: number) {
		this.name = name;
		this.regid = regid;
		this.badgeid = badgeid;
		this.checkin = checkin;
		this.checkout = checkout;
	}
}

function formatVolunteerSearch(r: any): volunteerSearch {
	return { name: r.name, regid: r.regid, badgeid: r.badgeid, checkin: r.checkin, checkout: r.checkout }
}

class volunteerSearchService {
	async doSearch(search: string): Promise<volunteerSearch[]> {
		var data = {
			action: 'search',
			token: rollcallToken,
			eventid: search
		};

		const options = {
			method: 'POST',
			body: JSON.stringify(data),
			headers: {
				'Content-Type': 'application/json'
			}
		};

		const res = await fetch('/php/volunteers/process.php', options);
		const res_1 = await res.json();
		return res_1.map((s: any) => formatVolunteerSearch(s));
	}
}

const apiVolunteerSearchService = new volunteerSearchService();

jQuery(function() {
	rollcallToken = (document.getElementById('token') as HTMLInputElement).value;
});

function getVolunteerStatusRow(j: volunteerSearch): string
{
	var button;
	var bout;
	
	if ( 0 == j.checkin )
	{
		button = '<button id="e'+j.regid+'" class="btn btn-danger btn-lg mt-2 mb-2" onClick="'+getVolunteerButton(j.regid,'checkin',0)+'">IN</button>';
	}
	else
	{
		button = '<button id="e' + j.regid + '" class="btn btn-success btn-lg mt-2 mb-2" onClick="'+getVolunteerButton(j.regid, 'checkin', 0)+'">IN</button>';
	}
	
	if ( 0 == j.checkout )
	{
		bout = '<button id="f' + j.regid + '" class="btn btn-danger btn-lg mt-2 mb-2" onClick="' + getVolunteerButton(j.regid, 'checkout', 0)+'">OUT</button>';
	}
	else
	{
		bout = '<button id="f' + j.regid + '" class="btn btn-success btn-lg mt-2 mb-2" onClick="' + getVolunteerButton(j.regid, 'checkout', 1)+'">OUT</button>';
	}
	
	return '<div class="row row-striped align-items-center"><div class="'+colName+'">'+
	j.name+'</div><div class="'+colBadge+'">'+j.badgeid+'</div><div class="'+colCheckin+'">'+
	button+'</div><div class="'+colCheckout+'">'+bout+'</div></div>';
}

function getVolunteerButton(regid: number, action:string, value: number) {
	return "updateVolunteerStatus('" + regid + "','"+action+"', "+value+");"
}

function updateVolunteerStatus(regid: number,button:string,undo:number)
{
	if (undo == 1 && !confirm("Press OK to undo the check in/out for this volunteer.")) {
		return;
	}

	var data = {
		action: 'value',
		token: rollcallToken,
		regid: regid,
		button: button,
		eventid: shiftId,
		currentValue: undo
	};

	const options = {
		method: 'POST',
		body: JSON.stringify(data),
		headers: {
			'Content-Type': 'application/json'
		}
	};

	fetch('/php/volunteers/process.php', options)
		.then(result => result.text())
		.then(result => {
			if (result != 'ok') {
				alert('Status change failed. Are you still authenticated?')
				return;
			}
			updateButton(regid, button, undo);
		});

  // var url='/php/volunteers/checkin_status.php';
  // var button;
  
  // if ( action == 'checkout' && jQuery('#e'+sid).hasClass('btn-danger'))
  // {
	//   alert("Check out cannot be changed when not checked in.");
	//   return;
  // }
  
  // if ( action == 'checkin' ) { button = '#e'+sid; } else { button = '#f'+sid };
  // if ( override == 0 && jQuery(button).hasClass('btn-success') ) {
	// action = action + 'confirm';
  // }

	// var postData =
	// {
	// 	token: t,
	// 	sid: sid,
	// 	action: action
	// };

	// jQuery.post( url, postData)
	// .done(function( data ) {
	// 	jsonData = JSON.parse(data);
	// 	finalizeStatus(jsonData);
	// });
}

function updateButton(regid: number, button: string, undo: number)
{
	var buttonCheckin  = 'e'+regid;
	var buttonCheckout = 'f'+regid;

	if ( button == 'checkin') {
		if ( undo == 0 ) {
			var b = document.getElementById(buttonCheckin) as HTMLElement;
			b.classList.remove('btn-danger');
			b.classList.add('btn-success')
			b.setAttribute('onclick',getVolunteerButton(regid, 'checkin', 1))
		} else {
			var b = document.getElementById(buttonCheckin) as HTMLElement;
			b.classList.remove('btn-success');
			b.classList.add('btn-danger')
			b.setAttribute('onclick', getVolunteerButton(regid, 'checkin', 0))

			b = document.getElementById(buttonCheckout) as HTMLElement;
			b.classList.remove('btn-success');
			b.classList.add('btn-danger')
			b.setAttribute('onclick', getVolunteerButton(regid, 'checkout', 0))
		}
	}
	else if (button == 'checkout') {
		if (undo == 0) {
			var b = document.getElementById(buttonCheckout) as HTMLElement;
			b.classList.remove('btn-danger');
			b.classList.add('btn-success')
			b.setAttribute('onclick', getVolunteerButton(regid, 'checkout', 1))
		} else {
			var b = document.getElementById(buttonCheckout) as HTMLElement;
			b.classList.remove('btn-success');
			b.classList.add('btn-danger')
			b.setAttribute('onclick', getVolunteerButton(regid, 'checkout', 0))
		}
	} else {
		alert("An error has occurred. Please reload this page to continue. No changes will be lost.");
	}
}



function fetchVolunteerData()
{
	var shifts = document.getElementById('shifts') as HTMLSelectElement;
	shiftId = shifts.selectedOptions[0].value;

	apiVolunteerSearchService.doSearch(shiftId).then(results => {
		var v = document.getElementById('volunteers');
		v.innerHTML = "";
		v.innerHTML += '<div class="row row-striped"><div class="' + colName + '">Name</div><div class="' + colBadge + '">Badge #</div><div class="' + colCheckin + '">Checkin #</div><div class="' + colCheckout + '">Checkout #</div></div>';

		results.forEach(s => {
			v.innerHTML += getVolunteerStatusRow(s);
		});
	})
}
