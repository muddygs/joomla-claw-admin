const jwtstatus:string = 'jwtstatus';
let jwttoken:string = '';

function jwtmonAjaxUrl(task: string) {
  return `/index.php?option=com_claw&view=checkin&task=${task}&format=raw`;
}

function tokencheck() {
  const data = {
    action: 'validate',
    token: jwttoken
  };

  const options = {
    method: 'POST',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': Joomla.getOptions('csrf.token')
    }
  };

  fetch(jwtmonAjaxUrl('jwtTokenCheck'), options)
    .then( result => result.json())
    .then(state => {
      const timer = state.time_remaining as number;
      const tokenState = state.state as string;

      if ( timer > 0 && tokenState == 'issued' ) {
        updateMonitorStatus(timer);
        setTimeout(() => {
            tokencheck();
        }, 60000);
      } else {
        updateMonitorStatus(0);
      }

    }).catch( error => {
      console.log("Fetch error in tokenmon");
      setTimeout(() => {
        tokencheck();
      }, 5000);
    });
}

function updateMonitorStatus(timer: number) {
  const n = document.getElementById(jwtstatus);
  if ( n === null ) return;

  const minutes = Math.round(timer/60);
  if ( minutes <= 1 ) {
    window.location.href = '/index.php?option=com_claw&view=checkin';
  }

  n.innerHTML = `Authentication expires in ${secondsToHms(timer)}.`;
}

function secondsToHms(d: number): string {
  if ( d <= 0 ) return 'N/A';
  const h = Math.floor(d / 3600);
  const m = Math.floor(d % 3600 / 60);
  const hDisplay = h > 0 ? `${h} ${h === 1 ? 'hour' : 'hours'}, ` : '';
  const mDisplay = m > 0 ? `${m} ${m === 1 ? 'minute' : 'minutes'}` : '';
  return hDisplay + mDisplay;
}

document.addEventListener('DOMContentLoaded', function() {
  const s = document.getElementById(jwtstatus);

  jwttoken = (document.getElementById('token') as HTMLInputElement).value;

  if ( s !== null && jwttoken !== null ) {
    tokencheck();
  }
});