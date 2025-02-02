function clearVolunteerData() {
  const nameId = document.getElementById('name');
  nameId.innerHTML = '';
  const uidId = document.getElementById('uid') as HTMLInputElement;
  uidId.value = '0';
  const v = document.getElementById('shifts');
  v.innerHTML = '';
  const regid = document.getElementById('regid') as HTMLInputElement;
  regid.value = '';
  regid.readOnly = false;
  regid.focus();
}

