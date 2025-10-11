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

$days = Helpers::days;

?>
<h1 class="text-center">Volunteer Detail Report</h1>
<?php
foreach ($this->items['shifts'] as $this->sid => $shift) {
  $grid = [];

  // Determine ordering by event_date
  foreach ($shift as $s) {
    // Time only
    $stime = date('H:i', strtotime($s->event_date));
    $etime = date('H:i', strtotime($s->event_end_date));
    $dow = str_pad(date('z', strtotime($s->event_date)), 3, '0', STR_PAD_LEFT);

    // Will sort on this key
    $k = implode(':', [$dow, $stime, $etime]);

    if (!array_key_exists($k, $grid)) {
      $grid[$k] = (object)[];

      foreach ($days as $day) {
        $grid[$k]->$day = null;
      }
    }

    $day = strtolower(date('D', strtotime($s->event_date)));

    $grid[$k]->$day = (object) [
      'event_capacity' => $s->event_capacity,
      'memberCount' => $s->memberCount,
      'published' => $s->published,
      'id' => $s->id,
      'title' => $s->title,
    ];
  }

  ksort($grid);

  if (!array_key_exists($this->sid, $this->items['coordinators'])) {
    echo "<h1 class=\"text-danger\">Cannot find coordinator for: $this->sid</h1>";
    var_dump($s);
    continue;
  }
?>
  <?php
  foreach ($grid as $k => $row) {
    foreach ($row as $day => $this->shift_info) {
      // Skip empty shifts and other meta data
      if (is_null($this->shift_info) || !in_array($day, $days)) continue;

  ?>
      <h1><?= $this->shift_info->title ?></h1>
      <h2><?= $this->items['coordinators'][$this->sid]['name'] ?> (<?= $this->items['coordinators'][$this->sid]['email'] ?>)</h2>
<?php
      echo $this->loadTemplate('registrants');
    }
  }
}

\ClawCorpLib\Helpers\Bootstrap::rawFooter();
