<?php

/**
 * @package     ClawCorp.Module.Spaschedule
 * @subpackage  mod_claw_spaschedule
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

$itemCount = 0;

?>
<div class="container">
  <table class="table table-dark table-striped table-responsive">
    <thead>
      <tr>
        <th>Time</th>
        <th>Session Length (Minutes)</th>
        <th>Non-Refundable Deposit</th>
        <th>Due At Time of Service</th>
        <th>Therapist Selection(s)</th>
      </tr>
    </thead>
    <tbody>
      <?php
      /** @var \ClawCorpLib\Lib\EventConfig $eventConfig */
      /** @var int $dayStart */
      /** @var int $dayEnd */
      /** @var \ClawCorpLib\Lib\PackageInfo $packageInfo */
      foreach ($eventConfig->packageInfos as $packageInfo):
        if ($packageInfo->start->toUnix() - $dayStart < 0 || $packageInfo->end->toUnix() - $dayEnd > 0) continue;
        $delta_time_minutes = (int)round(($packageInfo->end->toUnix() - $packageInfo->start->toUnix()) / 60);
      ?>
        <tr>
          <td><?= $packageInfo->start->format('g:iA') ?></td>
          <td><?= $delta_time_minutes ?></td>
          <td>$<?= $packageInfo->fee ?></td>
          <td>$90</td>
          <td>
            <?php foreach ($packageInfo->meta as $meta):
              $publicName = $publicNames[$meta->userid] ?? 'TBD';
              $eventId = $meta->eventId;
              $itemCount++;
            ?>
              <a href="/index.php?option=com_eventbooking&view=register&event_id=<?= $eventId ?>" role="button" class="mx-auto btn btn-danger mb-1"><?= $publicName ?></a>

            <?php endforeach ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (!$itemCount): ?>
    <h2>There are no sessions available during this time period.</h2>
  <?php endif; ?>
</div>
