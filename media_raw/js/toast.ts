document.addEventListener("DOMContentLoaded", function () {
  var registerButtons = [].slice.call(
    document.querySelectorAll(
      ".eb-register-button:not(.eb-colorbox-addcart):not(.eb-join-waiting-list-individual-button"
    )
  ) as HTMLAnchorElement[];
  registerButtons.map(function (thing) {
    thing.addEventListener("click", function (event) {
      event.preventDefault();
      fetch(this.href).then(() => cartToast());
    });
  });
});

function cartToast() {
  var toastElList = [].slice.call(document.querySelectorAll('.toast'))

  var toastList = toastElList.map(function (toastEl) {
    return new bootstrap.Toast(toastEl)
  });
  toastList.forEach(toast => toast.show());
}
