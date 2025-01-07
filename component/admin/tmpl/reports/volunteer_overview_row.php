<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Helpers;
?>
<tr>
  <td><?= $this->sid ?></td>
  <td><?= $this->stime ?></td>
  <td><?= $this->etime ?></td>
  <?php
  foreach (Helpers::getDays() as $dayKey) {
    $day = $this->row->$dayKey;
    if (null == $day || $day->event_capacity < 1) {
      echo '<td></td>';
      continue;
    }

    $p = ($day->event_capacity - $day->memberCount) / $day->event_capacity;

    switch (true) {
      case (0 == $p):
        $style = "color:white; background-color:green";
        break;
      case ($p > 0 && $p < .3):
        $style = "color:white; background-color:navy";
        break;
      case ($p >= .3 && $p < .5):
        $style = "color:white; background-color:royalblue";
        break;
      case ($p >= .5 && $p < .8):
        $style = "color:white; background-color:maroon";
        break;
      case ($p < 0):
        $style = "color:white; background-color:orange";
        break;
      default:
        $style = "color:white; background-color:red;";
        break;
    }

    $cell = ($day->event_capacity < $day->memberCount ? $day->memberCount : ($day->event_capacity - $day->memberCount)) . '<br/>' . $day->event_capacity;
    $published = '';
    if ($day->published == 0) {
      $style = "color:white; background-color:black";
      $published = '<br>UNPUBLISHED';
    } else {
      $this->needed += $day->event_capacity;
      $this->assigned += $day->memberCount;
    }
  ?>
    <td style="<?= $style ?>">
      <?= $cell ?><?= $published ?>
    </td>
  <?php
  }

  ?>
</tr>
