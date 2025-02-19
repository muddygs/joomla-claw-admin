window.addEventListener('keydown', function (e) {
  if (e.key == 'Enter') {
    e.preventDefault(); return false;
  }
}, true);

function clearcode() {
  const badgecode = document.getElementById('badgecode') as HTMLInputElement;
  badgecode.value = '';
  badgecode.focus();
}
