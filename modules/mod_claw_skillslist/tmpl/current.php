<?php

use Joomla\CMS\Date\Date;

if ( !sizeof($classes) )  return;

// Set heading based on time of day
$currentHour = date('G');

// Working with raw times, no time zone shifts
$tz = new DateTimeZone('UTC');

// Define the greeting based on the time of day
if ($currentHour >= 5 && $currentHour < 12) {
  $greeting = "Good Morning";
} elseif ($currentHour >= 12 && $currentHour < 18) {
  $greeting = "Good Afternoon";
} else {
  $greeting = "Good Evening";
}

$startBlock = null;
$startTime = null;
$count = 0;

foreach ($classes as $class):
  $endUnix = (new Date($class->end_time, 'America/New_York'))->toUnix();
  if ( $timestamp > $endUnix ) continue;
  
  if ( is_null($startBlock) ) {
    $startBlock = $class->start_time;
    $startTime = Date::createFromFormat('Y-m-d H:i', $class->start_time, $tz)->format('g:i A');

    // Start deferred output
    ?>
    <div class="container">
<h1 class="text-center"><?= $greeting ?> - Current Skills &amp; Education Sessions</h1>
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
  if ( $startBlock != $class->start_time ) {
    if ( $count < 3 ) {
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
      <td><?= $startTime . ' - ' . $endTime; ?></td>
      <td><?= $class->title; ?></td>
      <td><?= $locations[$class->location]->value ?? ''; ?></td>
    </tr>


<?php endforeach; ?>
  </tbody>
</table>
</div>

