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

?>
<h1 class="text-center">Volunteer Detail Report</h1>
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
  <?php

  foreach ($grid as $k => $row) {
    foreach ( $row as $this->shift_info ) {
      if (is_null($this->shift_info)) continue;
      ?>
        <h1><?= $e->title ?></h1>
        <h2><?= $this->items['coordinators'][$this->sid]['name'] ?> (<?= $this->items['coordinators'][$this->sid]['email'] ?>)</h2>
      <?php
      echo $this->loadTemplate('registrants');
    }
  }
}

\ClawCorpLib\Helpers\Bootstrap::rawFooter();
