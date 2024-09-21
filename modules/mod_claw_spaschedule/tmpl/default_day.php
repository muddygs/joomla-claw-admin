<?php

$sessionTitles = [
  'therapeutic' => 'Therapeutic',
  'sensual' => 'Sensual',
  'tie' => 'Tie Down',
];
$itemCount = 0;

?>
<div class="container">
  <table class="table table-dark table-striped">
    <thead>
      <tr>
        <th>Time</th>
        <th>Session Length (Minutes)</th>
        <th>Deposit Required*</th>
        <th>Services Selection</th>
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
          <td>
            <?php foreach ($packageInfo->meta as $session):
              $services = implode("<br/>", array_intersect_key($sessionTitles, array_flip($session->services)));
              $eventId = $session->eventId;
              $itemCount++;
            ?>
              <a href="/index.php?option=com_eventbooking&view=register&event_id=<?= $eventId ?>" role="button" class="mx-auto btn btn-danger"><?= $services ?></a>

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
