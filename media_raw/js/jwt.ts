var timeout:number = 0;
var tokenState:string = 'error';
var email: string = '';
var urlInput: string = '';

/**
 * Start the token process using page's #email and #nonce inputs
 */
function initToken(): void {
    var e = document.getElementById('email') as HTMLInputElement;
    if ( e === null || e.value === null ) return;

    var u = document.getElementById('url') as HTMLInputElement;
    if ( u === null || u.value === null ) return;

    //nonce = n.value;
    email = e.value;
    urlInput = u.value;
    tokenState = 'new';

    // Default 5 minutes
    timeout = time() + 300;

    var data = {
        action: 'init',
        email: email,
        urlInput: urlInput
    };

    const options = {
        method: 'POST',
        body: JSON.stringify(data),
        headers: {
            'Content-Type': 'application/json'
        }
    };

    // Request token sent to email
    fetch('/php/jwt/jwt_link_request_process.php', options)
    .then(result => result.text())
    .then(state => {
        tokenState = state;
    })

    updateStatus();

    setTimeout(() => {
        getNonceStatus();
    }, 5000);
}

/**
 * Displays a simple timeout message on the page in #msg
 */
function updateStatus():void  {
    var n = document.getElementById('msg');
    if ( n === null ) return;

    var html = 'Request expires in ' + Math.round(timeout - time()) + ' seconds.';

    if ( timeout <= time() ) {
        html = 'Authentication request has expired.';
    }

    n.innerHTML = html;
}

/**
 * Get time in seconds
 * @returns number Epoch time
 */
function time(): number {
    return Math.round(new Date().getTime() / 1000);
}

/**
 * Check the jwt state every 5 seconds
 * until timeout occurs (default 5 minutes) or token is issued or revoked.
 * If "issued," redirect page to #url input
 */
function getNonceStatus(): void {
    var u = document.getElementById('url') as HTMLInputElement;
    if ( u === null || u.value === null ) return;

    var data = {
        action: 'state',
        urlInput: u.value
    };

    const options = {
        method: 'POST',
        body: JSON.stringify(data),
        headers: {
            'Content-Type': 'application/json'
        }
    };

    fetch('/php/jwt/jwt_link_request_process.php', options)
        .then(result => result.json())
        .then(state => {
            var tokenState = state.state;
            var token = state.token;

            if ( time() < timeout ) {
                if ( tokenState == 'issued' ) {
                    (document.getElementById('token') as HTMLInputElement).value = token;
                    (document.getElementById('redirect') as HTMLFormElement).submit();
                    // var url = document.getElementById('url') as HTMLInputElement;
                    // if ( url !== null && url.value !== null ) {
                    //     var redirect = '/' + url.value + '?token=' + token;
                    //     window.location.href = redirect;
                    // }
                } else if (tokenState != "revoked") {
                    updateStatus();
                    setTimeout(() => {
                        getNonceStatus();
                    }, 5000);
                }
            }
        }).catch( error => {
            console.log('Fetch error in getNonceStatus');
            updateStatus();
            setTimeout(() => {
                getNonceStatus();
            }, 1000);
        });

    updateStatus();
}

jQuery(function() {
    jQuery(window).on('keydown', function (event) {
		if (event.key == 'Enter') {
			event.preventDefault();
			return false;
		}
	});

    jQuery('#submit').on('click', function () {
        jQuery('#submit').prop("disabled", true);
		initToken();
	});
});
