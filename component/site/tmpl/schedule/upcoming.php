<?php

use ClawCorpLib\Enums\SponsorshipType;
use ClawCorpLib\Helpers\Locations;

?>
<h1 class="text-center">Upcoming Events</h1>

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
    <?php foreach ($this->events as $event) : ?>
      <tr>
        <td><?= $event->start_time; ?></td>
        <td><?= $event->event_title; ?></td>
        <td><?= Locations::GetLocationById($event->location)->value ?></td>
        <td>
          <?php
            $sponsors = json_decode($event->sponsors);
            if ( $sponsors !== null ) {
              foreach ( $sponsors AS $sponsor ) {
                foreach ( SponsorshipType::valuesOrdered() as $sponsorType ) {
                  $s = $this->sponsors->GetSponsorById($sponsor);
                  if ( $s->type != $sponsorType) continue;
                  
                  $type = match((int)$s->type) {
                    SponsorshipType::Legacy_Master->value => 'Legacy Master',
                    SponsorshipType::Legacy_Sustaining->value => 'Legacy Sustaining',
                    SponsorshipType::Master->value => 'Master',
                    SponsorshipType::Sustaining->value => 'Sustaining',
                    default => ''
                  };
          
                  if ( $type != '' ):
                  ?>
                    <div class="text-center" style="font-size: smaller; color:#ffae00"><?=$type?>&nbsp;Sponsor<br/><?= $this->sponsors->GetSmallImageLink($sponsor) ?></div>
                  <?php
                  endif;
                }
              }
            } else {
              echo '&nbsp;';
            }
          ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>