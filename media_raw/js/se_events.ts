/* eslint-disable @typescript-eslint/no-unused-vars */
/** RSFORM SCRIPT
<script>

var script = document.createElement('script');
script.src = "/media/com_claw/js/se_events.js";

// Append the script to the head
document.head.appendChild(script);

// Function to check if `class_start_time` is ready and then call updateClassList
function checkAndUpdateClassList() {
    var startTimeElement = document.getElementById('class_start_time');

    if (startTimeElement && typeof updateClassList === 'function') {
        // `class_start_time` is ready, call updateClassList
        updateClassList();
    } else {
        // If `class_start_time` is not ready, call this function again after 500ms
        setTimeout(checkAndUpdateClassList, 500);
    }
}

window.addEventListener('DOMContentLoaded', function () {
    setTimeout(checkAndUpdateClassList, 500);
});

</script>

 */

class classRecord {
  stime: string;
  title: string;
  gid: string;
  day: string;

  constructor(stime: string, title: string, gid: string, day: string) {
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
      default:
        selects.push(c);
        break;
    }
  });

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
