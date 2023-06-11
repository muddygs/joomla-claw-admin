const jwtstatus:string = 'jwtstatus';
var jwttoken:string = null;

function tokenmon() {
  var data = {
    action: 'validate',
    token: jwttoken
  };

  const options = {
    method: 'POST',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/json'
    }
  };

  fetch('/php/jwt/jwt_link_request_process.php', options)
    .then( result => result.json())
    .then(state => {
      var timer = state.time_remaining as number;
      var tokenState = state.state as string;

      if ( timer > 0 && tokenState == 'issued' ) {
        updateMonitorStatus(timer);
        setTimeout(() => {
            tokenmon();
        }, 60000);
      } else {
        updateMonitorStatus(0);
      }

    }).catch( error => {
      console.log("Fetch error in tokenmon");
      setTimeout(() => {
        tokenmon();
      }, 5000);
    });
}

function updateMonitorStatus(timer: number) {
  var n = document.getElementById(jwtstatus);
  if ( n === null ) return;

  var minutes = Math.round(timer/60);

  var html = 'Authentication expires in ' + secondsToHms(timer) + '.';
  //var now = Math.round(new Date().getTime() / 1000);
  if ( minutes <= 1 ) {
      html = 'Authentication has expired. Click <a href="/getlink">HERE</a> to re-authenticate.';
  }

  n.innerHTML = html;
}

function secondsToHms(d: number):string {
  var h = Math.floor(d / 3600);
  var m = Math.floor(d % 3600 / 60);
  //var s = Math.floor(d % 3600 % 60);

  var hDisplay = h > 0 ? h + (h == 1 ? " hour, " : " hours, ") : "";
  var mDisplay = m > 0 ? m + (m == 1 ? " minute" : " minutes") : "";
  //var sDisplay = s > 0 ? s + (s == 1 ? " second" : " seconds") : "";
  return hDisplay + mDisplay; 
}


jQuery(function() {
  var s = document.getElementById(jwtstatus);

  jwttoken = (document.getElementById('token') as HTMLInputElement).value;

  if ( s !== null && jwttoken !== null ) {
    tokenmon();
  }
});