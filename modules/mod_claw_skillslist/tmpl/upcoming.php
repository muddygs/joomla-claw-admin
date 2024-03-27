<?php

use Joomla\CMS\Date\Date;

if ( !sizeof($classes) )  return;

// Working with raw times, no time zone shifts
$tz = new DateTimeZone('UTC');

$startBlock = null;
$startTime = null;

$firstBlock = null;
$count = 0;

// Check if the first class hasn't even started
reset($classes);
$startUnix = (new Date(current($classes)->start_time, 'America/New_York'))->toUnix();
if ( $timestamp < $startUnix ) {
  return;
}

foreach ($classes as $class):
  $endUnix = (new Date($class->end_time, 'America/New_York'))->toUnix();
  if ( $timestamp > $endUnix ) continue;
  
  if ( is_null($firstBlock) ) {
    $firstBlock = $class->start_time;
  }

  if ( $firstBlock == $class->start_time ) {
    continue;
  }

  // Now we have the first block, so we can start output
  if ( is_null($startBlock) ) {
    $startBlock = $class->start_time;
    $startTime = Date::createFromFormat('Y-m-d H:i', $class->start_time, $tz)->format('g:i A');

    // Start deferred output
    ?>
    <div class="container">
<h1 class="text-center"><span class="badge rounded-pill text-bg-warning">Upcoming</span> Skills &amp; Education Sessions</h1>
<table class="table table-dark table-striped">
  <thead>
    <tr>
      <th>Time</th>
      <th>Class Title</th>
      <th>Location</th>
    </tr>
  </thead>
  <tbody>
    <?php
  }

  // Allow for time blocks with only a couple classes
  if ($startBlock != $class->start_time) {
    if ($count < 3) {
      $startBlock = $class->start_time;
      $startTime = Date::createFromFormat('Y-m-d H:i', $class->start_time, $tz)->format('g:i A');
    } else {
      break;
    }
  }
  
  
  $count++;

  $endTime = Date::createFromFormat('Y-m-d H:i:s.u', $class->end_time, $tz)->format('g:i A');

?>
    <tr>
      <td><?= $startTime ?>&nbsp;&#8209;&nbsp;<?= $endTime ?></td>
      <td><?= $class->title; ?></td>
      <td><?= $locations[$class->location]->value ?? ''; ?></td>
    </tr>


<?php endforeach; ?>
  </tbody>
</table>
</div>

