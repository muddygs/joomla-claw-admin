<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use ClawCorpLib\Helpers\Helpers;

// No direct access to this file
\defined('_JEXEC') or die('Restricted Access');

\ClawCorpLib\Helpers\Bootstrap::rawHeader([], ['/media/com_claw/css/print_letter.css']);

$master_assigned = 0;
$master_needed = 0;

?>
<h1 class="text-center">Volunteer Overview Report</h1>
<div class="row">
  <div class="col-2" style="color:white; background-color:red">Empty</div>
  <div class="col-2" style="color:white; background-color:navy">&lt;30%</div>
  <div class="col-2" style="color:white; background-color:royalblue">&lt;50%</div>
  <div class="col-2" style="color:white; background-color:maroon">&lt;80%</div>
  <div class="col-2" style="color:white; background-color:green">Fulfilled</div>
  <div class="col-2" style="color:white; background-color:orange">Overbooked</div>
</div>
<?php
foreach ($this->items['shifts'] as $this->sid => $events) {
  $grid = [];

  // Determine ordering by event_date
  foreach ($events as $e) {
    // Time only
    $stime = date('H:i', strtotime($e->event_date));
    $etime = date('H:i', strtotime($e->event_end_date));

    // Will sort on this key
    $k = $stime . '-' . $etime;

    if (!array_key_exists($k, $grid)) {
      $grid[$k] = (object)[];
      foreach (Helpers::getDays() as $day) {
        $grid[$k]->$day = null;
      }
    }

    $day = strtolower(date('D', strtotime($e->event_date)));

    $grid[$k]->$day = (object) [
      'event_capacity' => $e->event_capacity,
      'memberCount' => $e->memberCount,
      'published' => $e->published,
      'id' => $e->id
    ];
  }

  ksort($grid);

  if (!array_key_exists($this->sid, $this->items['coordinators'])) {
    echo "<h1 class=\"text-danger\">Cannot find coordinator for: $this->sid</h1>";
    var_dump($e);
    continue;
  }
?>
  <h1><?= $this->items['coordinators'][$this->sid]['title'] ?></h1>
  <h2><?= $this->items['coordinators'][$this->sid]['name'] ?> (<?= $this->items['coordinators'][$this->sid]['email'] ?>)</h2>
  <table class="table table-striped table-hover table-sm table-bordered">
    <thead class="thead-dark">
      <tr>
        <th scope="col" class="col-1">Grid ID</th>
        <th scope="col" class="col-2">Start</th>
        <th scope="col" class="col-2">End</th>
        <th scope="col" class="col-1">TUE</th>
        <th scope="col" class="col-1">WED</th>
        <th scope="col" class="col-1">THU</th>
        <th scope="col" class="col-1">FRI</th>
        <th scope="col" class="col-1">SAT</th>
        <th scope="col" class="col-1">SUN</th>
        <th scope="col" class="col-1">MON</th>
      </tr>
    </thead>
    <tbody>
      <?php

      $this->needed = 0;
      $this->assigned = 0;

      foreach ($grid as $k => $this->row) {
        list($stime, $etime) = explode('-', $k);
        $this->stime = Helpers::formatTime($stime);
        $this->etime = Helpers::formatTime($etime);
        echo $this->loadTemplate('row');
      }
      ?>
    </tbody>
  </table>

  <?php
  if ($this->needed > 0) {
    $master_needed += $this->needed;
    $master_assigned += $this->assigned;

    $priPercentage = floor(100.0 - ($this->needed - $this->assigned) / $this->needed * 100.0);

    if ($priPercentage < 0) $priPercentage = 0;
    if ($priPercentage > 100) $priPercentage = 100;

    $label = $priPercentage == 100 ? 'FULFILLED' : "$this->assigned / $this->needed ($priPercentage %)";

    switch (true) {
      case ($priPercentage > 25 && $priPercentage <= 50):
        $color = 'bg-warning';
        break;
      case ($priPercentage > 50 && $priPercentage <= 75):
        $color = 'bg-info';
        break;
      case ($priPercentage > 75):
        $color = 'bg-success';
        break;
      default:
        $color = 'bg-danger';
        break;
    }
  ?>
    <div class="progress mb-4" style="height:30px;">
      <div class="progress-bar <?= $color ?>" role="progressbar" style="width: <?= $priPercentage ?>%" aria-valuenow="<?= $priPercentage ?>" aria-valuemin="0" aria-valuemax="100">
        <b><?= $label ?></b>
      </div>
    </div>
  <?php
  }
}

if ($master_needed > 0) {
  echo "<h1>OVERALL SHIFT STATUS</h1>";
  $priPercentage = floor(100.0 - ($master_needed - $master_assigned) / $master_needed * 100.0);

  if ($priPercentage < 0) $priPercentage = 0;
  if ($priPercentage > 100) $priPercentage = 100;

  switch (true) {
    case ($priPercentage > 25 && $priPercentage <= 50):
      $color = 'bg-warning';
      break;
    case ($priPercentage > 50 && $priPercentage <= 75):
      $color = 'bg-info';
      break;
    case ($priPercentage > 75):
      $color = 'bg-success';
      break;
    default:
      $color = 'bg-danger';
      break;
  }

  $color .= ' text-dark';

  $label = "Overall Shift Status: $master_assigned / $master_needed ($priPercentage %)";
  ?>
  <div class="progress mb-4" style="height:30px;">
    <div class="progress-bar <?= $color ?>" role="progressbar" style="width: <?= $priPercentage ?>%" aria-valuenow="<?= $priPercentage ?>" aria-valuemin="0" aria-valuemax="100">
      <b><?= $label ?></b>
    </div>
  </div>

<?php
}
\ClawCorpLib\Helpers\Bootstrap::rawFooter();
