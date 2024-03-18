<?php

use ClawCorpLib\Enums\SponsorshipType;
use ClawCorpLib\Helpers\Locations;
use ClawCorpLib\Helpers\Sponsors;

$allSponsors = new Sponsors();

// Set heading based on time of day
$currentHour = date('G');

// Define the greeting based on the time of day
if ($currentHour >= 5 && $currentHour < 12) {
  $greeting = "Good Morning";
} elseif ($currentHour >= 12 && $currentHour < 18) {
  $greeting = "Good Afternoon";
} else {
  $greeting = "Good Evening";
}

?>
<h1 class="text-center"><?= $greeting ?> - Upcoming Events</h1>

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
        <td><?= $event->start_time; ?></td>
        <td><?= $event->event_title; ?></td>
        <td><?= Locations::GetLocationById($event->location)->value ?></td>
        <td>
          <?php
          $sponsors = json_decode($event->sponsors);
          if ($sponsors !== null) {
          ?>
            <div class="d-flex justify-content-start align-items-stretch">
              <?php
              foreach (SponsorshipType::valuesOrdered() as $sponsorType) {
                foreach ($sponsors as $sponsor) {
                  $s = $allSponsors->GetSponsorById($sponsor);
                  if ($s->type != $sponsorType) continue;

                  $order++;

                  $type = match ((int)$s->type) {
                    SponsorshipType::Legacy_Master->value => 'Legacy Master',
                    SponsorshipType::Legacy_Sustaining->value => 'Legacy Sustaining',
                    SponsorshipType::Master->value => 'Master',
                    SponsorshipType::Sustaining->value => 'Sustaining',
                    default => ''
                  };

                  if ($type != '') :
              ?>
                    <div class="border border-danger rounded pt-1 pb-1 pe-2 ps-2 me-1 order-<?= $order ?>">
                      <p class="text-center tight"><?= $s->name ?><br/>
                      <span style="font-size: smaller; color:#ffae00"><?= $type ?></span></p>
                    </div>
                  <?php
                  else :
                  ?>
                    <div class="border border-warning rounded p-1 me-1 order-<?= $order ?>">
                      <p class="text-center tight"><?= $s->name ?></p>
                    </div>
              <?php
                  endif;
                }
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