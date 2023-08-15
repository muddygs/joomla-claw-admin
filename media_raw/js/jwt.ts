let timeout: number = 0;
let jwttokenState: string = 'error';
let email: string = '';
let urlInput: string = '';

function jwtstateAjaxUrl(task: string) {
  return `/index.php?option=com_claw&view=checkin&task=${task}&format=raw`;
}

function jwtstateOptions(action: string, email: string, urlInput: string) {
  const data = {
    action: action,
    email: email,
    urlInput: urlInput
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

/**
 * Start the token process using page's #email and #nonce inputs
 */
function initToken(): void {
  const e = document.getElementById('email') as HTMLInputElement;
  if (e === null || e.value === null) return;

  const u = document.getElementById('url') as HTMLInputElement;
  if (u === null || u.value === null) return;

  //nonce = n.value;
  email = e.value;
  urlInput = u.value;
  jwttokenState = 'new';

  // Default 5 minutes
  timeout = time() + 300;

  // Request token sent to email
  fetch(jwtstateAjaxUrl('jwtstateInit'), jwtstateOptions('init', email, urlInput))
    .then(result => result.text())
    .then(state => {
      jwttokenState = state;
    })

  updateStatus();

  setTimeout(() => {
    getNonceStatus();
  }, 5000);
}

/**
 * Displays a simple timeout message on the page in #msg
 */
function updateStatus(): void {
  var n = document.getElementById('msg');
  if (n === null) return;

  var html = `Request expires in ${Math.round(timeout - time())} seconds.`;

  if (timeout <= time()) {
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
  if (u === null || u.value === null) return;

  fetch(jwtstateAjaxUrl('jwtstateState'), jwtstateOptions('state', '', urlInput))
    .then(result => result.json())
    .then(state => {
      var tokenState = state.state;
      var token = state.token;

      if (time() < timeout) {
        if (tokenState == 'issued') {
          var redirect = `/index.php?option=com_claw&view=checkin&token=${token}`;
          window.location.href = redirect;
        } else if (tokenState != "revoked") {
          updateStatus();
          setTimeout(() => {
            getNonceStatus();
          }, 5000);
        }
      }
    }).catch(error => {
      console.log('Fetch error in getNonceStatus');
      updateStatus();
      setTimeout(() => {
        getNonceStatus();
      }, 10000);
    });

  updateStatus();
}

window.addEventListener('keydown', function (e) {
  if (e.key == 'Enter') {
    e.preventDefault(); return false;
  }
}, true);

function submitjwtEmail() {
  const submitButton = document.getElementById("submit") as HTMLInputElement;
  submitButton.disabled = true;
  initToken();
}
