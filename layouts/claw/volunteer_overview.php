<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use ClawCorpLib\Helpers\Bootstrap;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Enums\EbPublishedState;
use Joomla\CMS\Factory;

// No direct access to this file
\defined('_JEXEC') or die('Restricted Access');

$items = $displayData['items'];
// When set, add callbacks to cells
$htmx = $displayData['htmx'] ?? false;
$htmxloaded = $displayData['htmxloaded'] ?? false;

$tableClass = match ($htmx) {
  true => 'table table-sm table-bordered table-dark',
  default => 'table table-striped table-hover table-sm table-bordered',
};

if (!$htmx) {
  Bootstrap::rawHeader([], ['/media/com_claw/css/print_letter.css']);
} else {
  if (!$htmxloaded) {
    #/** @var Joomla\CMS\Application\AdministratorApplication */
    $app = Factory::getApplication();
    /** @var Joomla\CMS\WebAsset\WebAssetManager */
    $wa = $app->getDocument()->getWebAssetManager();
    $wa->useScript('htmx');
  }
}

$master_assigned = 0;
$master_needed = 0;

?>
<h1 class="text-center"><?= $displayData['title'] ?></h1>
<div class="row">
  <div class="col-2" style="<?= Bootstrap::percentColor(0) ?>">Empty</div>
  <div class="col-2" style="<?= Bootstrap::percentColor(25) ?>">&gt;25%</div>
  <div class="col-2" style="<?= Bootstrap::percentColor(50) ?>">&gt;50%</div>
  <div class="col-2" style="<?= Bootstrap::percentColor(75) ?>">&gt;75%</div>
  <div class="col-2" style="<?= Bootstrap::percentColor(100) ?>">100%</div>
  <div class="col-2" style="<?= Bootstrap::percentColor(101) ?>">Overbooked</div>
</div>
<?php
foreach ($items['shifts'] as $sid => $events) {
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
      foreach (Helpers::days as $day) {
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

  if (!array_key_exists($sid, $items['coordinators'])) {
    echo "<h1 class=\"text-danger\">Cannot find coordinator for: $sid</h1>";
    var_dump($e);
    continue;
  }
?>
  <h1><?= $items['coordinators'][$sid]['title'] ?></h1>
  <?php if (!$htmx): ?>
    <h2><?= $items['coordinators'][$sid]['name'] ?> (<?= $items['coordinators'][$sid]['email'] ?>)</h2>
  <?php endif; ?>
  <table class="<?= $tableClass ?>">
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

      $needed = 0;
      $assigned = 0;

      foreach ($grid as $k => $row) {
        list($stime, $etime) = explode('-', $k);
        $stime = Helpers::formatTime($stime);
        $etime = Helpers::formatTime($etime);
      ?>
        <tr>
          <td><?= $sid ?></td>
          <td><?= $stime ?></td>
          <td><?= $etime ?></td>
          <?php
          foreach (Helpers::days as $dayKey) {
            $day = $row->$dayKey;
            if (null == $day || $day->event_capacity < 1) {
              echo '<td></td>';
              continue;
            }

            $p = ($day->event_capacity - $day->memberCount) / $day->event_capacity;
            $style = Bootstrap::percentColor(100 - $p * 100);
            if ($htmx) {
              $style .= 'cursor:copy;';
            }

            $cell = ($day->event_capacity < $day->memberCount ? $day->memberCount : ($day->event_capacity - $day->memberCount)) . '<br/>' . $day->event_capacity;
            $cell = $day->memberCount . ' of ' . $day->event_capacity;
            $published = '';
            if ($day->published != EbPublishedState::published->value) {
              $style = "color:white; background-color:black";
              $published = '<br>UNPUBLISHED';
            } else {
              $needed += $day->event_capacity;
              $assigned += $day->memberCount;
            }

            $id = implode('-', [$sid, $dayKey]);
          ?>
            <td style="<?= $style ?>"
              <?php if ($htmx): ?>
              hx-post="/index.php?option=com_claw&task=rollcall.rollcallAddShift&format=raw"
              hx-target="#shifts"
              hx-vals='{"eventid":"<?= $day->id ?? 0 ?>"}'
              <?php endif; ?>
              id="<?= $id ?>"
              name="<?= $id ?>">
              <?= $cell ?><?= $published ?>
            </td>
          <?php
          }

          ?>
        </tr>
      <?php
      }
      ?>
    </tbody>
  </table>

  <?php
  if ($needed > 0) {
    $master_needed += $needed;
    $master_assigned += $assigned;

    $priPercentage = floor(100.0 - ($needed - $assigned) / $needed * 100.0);

    if ($priPercentage < 0) $priPercentage = 0;
    if ($priPercentage > 100) $priPercentage = 100;

    $label = $priPercentage == 100 ? 'FULFILLED' : "$assigned / $needed ($priPercentage %)";

    if (!$htmx):
      $colorStyle = Bootstrap::percentColor($priPercentage);
  ?>
      <div class="progress mb-4" style="height:30px;">
        <div class="progress-bar" role="progressbar" style="<?= $colorStyle ?>; width: <?= $priPercentage ?>%" aria-valuenow="<?= $priPercentage ?>" aria-valuemin="0" aria-valuemax="100">
          <b><?= $label ?></b>
        </div>
      </div>
  <?php
    endif;
  }
}

if ($master_needed > 0 && !$htmx) {
  echo "<h1>OVERALL SHIFT STATUS</h1>";
  $priPercentage = floor(100.0 - ($master_needed - $master_assigned) / $master_needed * 100.0);

  if ($priPercentage < 0) $priPercentage = 0;
  if ($priPercentage > 100) $priPercentage = 100;
  $colorStyle = Bootstrap::percentColor($priPercentage);

  $label = "Overall Shift Status: $master_assigned / $master_needed ($priPercentage %)";
  ?>
  <div class="progress mb-4" style="height:30px;">
    <div class="progress-bar" role="progressbar" style="<?= $colorStyle ?> width: <?= $priPercentage ?>%" aria-valuenow="<?= $priPercentage ?>" aria-valuemin="0" aria-valuemax="100">
      <b><?= $label ?></b>
    </div>
  </div>

<?php
}

if (!$htmx) Bootstrap::rawFooter();
