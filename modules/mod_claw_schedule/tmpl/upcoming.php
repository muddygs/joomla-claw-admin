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

$allSponsors = (new Sponsors())->sponsors;

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
      <?php foreach ($events as $event) :
        $order = 0;
      ?>
        <tr>
          <td><?= Helpers::formatTime($event->start_time) ?>&nbsp;&#8209;&nbsp;<?= Helpers::formatTime($event->end_time) ?></td>
          <td><?= $event->event_title; ?></td>
          <td><?= $locations[$event->location]->value ?></td>
          <td>
            <?php
            $sponsors = json_decode($event->sponsors);
            if ($sponsors !== null) {
            ?>
              <div class="d-flex justify-content-start align-items-stretch">
                <?php
                foreach ($sponsors as $sponsor) {
                  /** @var \ClawCorpLib\Lib\Sponsor */
                  $s = $allSponsors[$sponsor];
                  $type = $s->type->toString();
                ?>
                  <div class="border border-danger rounded pt-1 pb-1 pe-2 ps-2 me-1 order-<?= $order++ ?>">
                    <p class="text-center tight"><?= $s->name ?><br />
                      <span style="font-size: smaller; color:#ffae00"><?= $type ?></span>
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
