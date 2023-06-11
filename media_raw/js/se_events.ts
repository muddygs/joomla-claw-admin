jQuery(function () {
  // jQuery('#class_start_time').on('change', function() {
  //   updateClassList();
  // })

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
  var stimeSelect = document.getElementById('class_start_time') as HTMLSelectElement;
  var stime = stimeSelect.selectedOptions[0].value;

  var classes: classRecord[] = JSON.parse((document.getElementById('classListJson') as HTMLInputElement).value);

  var selects: classRecord[] = [];

  if (stime == 'any') {
    selects = classes
  } else {
    classes.forEach((c) => {
      var t: number = Number(c.stime);
      var d: number = Number(c.day);

      // Any, FriAM, ..., Sun

      if (stime == 'friam' && d == 1 && t < 1200) {
        selects.push(c);
      } else
      if (stime == 'fripm' && d == 1 && t > 1159) {
        selects.push(c);
      } else
      if (stime == 'satam' && d == 2 && t < 1200) {
        selects.push(c);
      } else
      if (stime == 'satpm' && d == 2 && t > 1159) {
        selects.push(c);
      } else
      if (stime == 'sun' && d == 3) {
        selects.push(c);
      }
    });
  }

  var classTitleParam = document.getElementById('classTitleParam') as HTMLInputElement;
  var preselect = '';
  if (classTitleParam != null) preselect = classTitleParam.value;
  var classTitle = document.getElementById('class_title') as HTMLSelectElement;

  classTitle.innerHTML = "";

  selects.forEach(s => {
    var selected = false;
    if (s.gid == preselect) {
      selected = true
    }
    var o = new Option(s.title, s.gid, selected, selected);
    classTitle.append(o);
  });
}
