jQuery(function () {
  jQuery(window).on('keydown', function (event) {
    if (event.key == 'Enter') {
      event.preventDefault();
      return false;
    }
  });
});

function doConfirm(id: number) {
  var data = {
    action: "confirm",
    id: id
  };

  const options = {
    method: 'POST',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/json'
    }
  };

  fetch('/php/jwt/jwt_dashboard_process.php', options)
    .then(result => result.json())
    .then(html => {
      var b = document.getElementById('dbrdc' + html.id) as HTMLInputElement;
      if ( b != null ) b.remove();
    });
}

function doRevoke(id: number) {
  var data = {
    action: "revoke",
    id: id
  };

  const options = {
    method: 'POST',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/json'
    }
  };

  fetch('/php/jwt/jwt_dashboard_process.php', options)
    .then(result => result.json())
    .then(html => {
      var b = document.getElementById('dbrdc' + html.id) as HTMLInputElement;
      if (b != null) b.remove();
      b = document.getElementById('dbrdr' + html.id) as HTMLInputElement;
      if (b != null) b.remove();

    });

}
