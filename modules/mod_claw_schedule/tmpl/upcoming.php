<?php

/**
 * @package     CLAW.Schedule
 * @subpackage  mod_claw_schedule
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Sponsors;

$allSponsors = Sponsors::get();

?>
<h1 class="text-center">Upcoming Events</h1>

<div class="container">
  <table class="table table-dark table-striped">
    <thead>
      <tr>
        <th>Time</th>
        <th>Event</th>
        <th>Location</th>
        <th>Sponsors</th>
      </tr>
    </thead>
    <tbody>
      <?php /** @var \ClawCorpLib\Lib\ScheduleRecord */  ?>
      <?php foreach ($events as $event) :
        $order = 0;
      ?>
        <tr>
          <td><?= Helpers::formatDateTime($event->datetime_start) ?>&nbsp;&#8209;&nbsp;<?= Helpers::formatDateTime($event->datetime_end) ?></td>
          <td><?= $event->event_title; ?></td>
          <td><?= $locations[$event->location]->value ?></td>
          <td>
            <?php
            if (count($event->sponsors)) {
            ?>
              <div class="d-flex justify-content-start align-items-stretch">
                <?php
                foreach ($event->sponsors as $sponsor) {
                  /** @var \ClawCorpLib\Lib\Sponsor */
                  $s = $allSponsors[$sponsor];
                  if (is_null($s)) continue;
                  $type = $s->type->toString();
                ?>
                  <div class="border border-danger rounded pt-1 pb-1 pe-2 ps-2 me-1 order-<?= $s->ordering ?>">
                    <p class="text-center tight"><?= $s->name ?><br />
                      <span style="font-size: smaller; color:--var(claw-warning)"><?= $type ?></span>
                    </p>
                  </div>
                <?php
                }
                ?>
              </div>
            <?php
            } else {
              echo '&nbsp;';
            }
            ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
