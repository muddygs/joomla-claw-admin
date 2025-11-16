<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use ClawCorpLib\Lib\Registrants;

$registrants = Registrants::byEventId($this->shift_info->id);

foreach ($registrants as $r) {
  $r->mergeFieldValues(['Z_SHIFT_CHECKIN', 'Z_SHIFT_CHECKOUT']);
}

?>
<div class="ml-1 mr-1">
  <table class="table table-striped table-hover table-sm table-bordered">
    <thead class="thead">
      <tr>
        <th scope="col" class="col-1">#</th>
        <th scope="col" class="col-2">First</th>
        <th scope="col" class="col-2">Last</th>
        <th scope="col" class="col-3">Email</th>
        <th scope="col" class="col-2">Badge #</th>
        <th scope="col" class="col-1">In</th>
        <th scope="col" class="col-1">Out</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $count = 1;
      foreach ($registrants as $r) {
        $records = $r->records();
        $record = reset($records);

        $in = $record->fieldValue->Z_SHIFT_CHECKIN != 0 ? 'bg-success' : '';
        $out = $record->fieldValue->Z_SHIFT_CHECKOUT != 0 ? 'bg-success' : '';
      ?>
        <tr>
          <td class="col-1"><?php echo $count; ?></td>
          <td class="col-2 text-capitalize"><?php echo $record->registrant->first_name; ?></td>
          <td class="col-2 text-capitalize"><?php echo $record->registrant->last_name; ?></td>
          <td class="col-3 text-lowercase"><?php echo $record->registrant->email; ?></td>
          <td class="col-2"><?php echo $r->badgeId; ?></td>
          <td class="col-1 <?php echo $in ?>"></td>
          <td class="col-1 <?php echo $out ?>"></td>
        </tr>
      <?php
        $count++;
      }
      ?>
    </tbody>
  </table>
  <div class="text-danger text-left"><b>Capacity: </b><?php echo $this->shift_info->event_capacity; ?></div>
</div>
<hr>
<div class="page-break"></div>
