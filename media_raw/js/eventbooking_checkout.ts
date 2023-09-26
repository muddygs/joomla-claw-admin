declare const eb_current_page: any;
declare function calculateIndividualRegistrationFee(): void;
declare function calculateCartRegistrationFee(): void;

document.addEventListener('DOMContentLoaded', function () {
	if ( eb_current_page !== null && typeof(eb_current_page) == 'string' )
	{
		switch (eb_current_page) {
			case 'default':
				calculateIndividualRegistrationFee();
				break;
			case 'cart':
				calculateCartRegistrationFee();
			default:
				break;
		}
	}
});
