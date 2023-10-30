document.addEventListener("DOMContentLoaded", function () {
  updateClassList();
});

class classRecord {
  stime: string;
  title: string;
  gid: string;
  day: string;

  constructor(stime: string, title:string,gid:string,day:string) {
    this.stime = stime;
    this.title = title;
    this.gid = gid;
    this.day = day;
  }
}

function updateClassList() {
  const stimeSelect = document.getElementById('class_start_time') as HTMLSelectElement;
  const stime = stimeSelect.selectedOptions[0].value;

  const classes: classRecord[] = JSON.parse((document.getElementById('classListJson') as HTMLInputElement).value);

  let selects: classRecord[] = [];

  if (stime == 'any') {
    selects = classes
  } else {
    classes.forEach((c) => {
      const t: number = Number(c.stime);
      const d: number = Number(c.day);

      // Any, FriAM, ..., Sun

      switch (stime) {
        case 'friam':
          if (d == 5 && t < 1200) {
            selects.push(c);
          }
          break;
        case 'fripm':
          if (d == 5 && t > 1159) {
            selects.push(c);
          }
          break;
        case 'satam':
          if (d == 6 && t < 1200) {
            selects.push(c);
          }
          break;
        case 'satpm':
          if (d == 6 && t > 1159) {
            selects.push(c);
          }
          break;
        case 'sun':
          if (d == 0) {
            selects.push(c);
          }
          break;
      }
    });
  }

  const classTitleParam = document.getElementById('classTitleParam') as HTMLInputElement;
  let preselect = '';
  if (classTitleParam != null) preselect = classTitleParam.value;
  const classTitle = document.getElementById('class_title') as HTMLSelectElement;

  classTitle.innerHTML = "";

  selects.forEach(s => {
    const o = new Option(s.title, s.gid, s.gid == preselect, s.gid == preselect);
    o.innerHTML = s.title;
    classTitle.append(o);
  });
}
