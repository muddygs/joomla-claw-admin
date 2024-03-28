<?php

use ClawCorpLib\Enums\SponsorshipType;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Locations;

\defined('_JEXEC') or die;

$locationView = $this->params->get('ShowLocation') ? '' : 'd-none';

?>
  <div class="container">
    <div class="row row-striped g-0">
      <div class="col-9 col-lg-10 row">
        <div class="col-12 col-lg-2 pt-lg-2 pb-lg-2 mt-2 mb-2 font-weight-bold tight">Time</div>
        <div class="col-12 col-lg-8 pt-lg-2 pb-lg-2 mt-0 mt-lg-1 mb-0 mb-lg-1 font-weight-bold tight">Event</div>
        <div class="col-12 col-lg-2 pt-lg-2 pb-lg-2 mt-0 mt-lg-1 mb-2 mb-lg-1 font-weight-bold tight <?= $locationView ?>">Location</div>
      </div>
      <div class="col-3 col-lg-2 order-last pt-2 pb-2 mt-2 mb-2 font-weight-bold text-center g-0">Sponsor</div>
    </div>
<?php

foreach ($this->items AS $item) {
  $stime = $item->start_time;
  $stime = Helpers::formatTime($stime);
  $etime = $item->end_time;
  $etime = Helpers::formatTime($etime);
  
  $id = $item->id;
  $event = $item->event_title;

  if ( $this->eventInfo->onsiteActive ) {
    $event_description = $item->onsite_description == '' ?  $item->event_description : $item->onsite_description;
  } else {
    $event_description = $item->event_description;
  }

  $fee_event = explode(',',$item->fee_event);
  $event_id = $item->event_id;
  $location = Locations::GetLocationById($item->location);
  if ( $locationView == 'd-none' ) {
    $location->value = '';
  }

  if ( $this->eventInfo->onsiteActive ) {
    $event_description = $item->onsite_description == '' ?  $item->event_description : $item->onsite_description;
  } else {
    $event_description = $item->event_description;
  }

  $featuredClass = $item->featured ? 'border border-danger border-top-0 border-bottom-0 border-end-0' : '';


  //event (optional poster)

  $thumb = $poster = '';

  if ( !empty($item->poster) ) {
    $json = json_decode($item->poster);
    $poster = explode('#', $json->imagefile)[0];
    $dirname = dirname($poster);
    $basename = basename($poster);
    $thumbname = $dirname . DIRECTORY_SEPARATOR . 'thumb_' . $basename;
    $thumb = <<<HTML
<button id="show-img-$id" type="button" class="btn btn-default p-0 align-top" data-bs-toggle="modal" data-bs-target="#modal-$id">
  <img src="$thumbname"/>
</button>
<div id="modal-$id" class="modal fade" aria-labelledby="modal-{$id}Label" aria-hidden="true" tabindex="-1" role="dialog">
<div class="modal-dialog" data-dismiss="modal">
<div class="modal-content"> 
  <div class="model-header">
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
  </div>
  <div class="modal-body">
    <img src="$poster" class="img-responsive" style="width: 100%;">
  </div> 
</div>
</div>
</div>
HTML;
  }


  $payHtml = '';

  if (count(array_intersect($fee_event, ['preorder','dooronly']))>0) {
  	if ($event_id != 0) {
  		$payHtml = "<a href=\"/index.php?option=com_eventbooking&view=event&id={$event_id}\" data-toggle=\"tooltip\" title=\"Purchase Ticket\"";
  	}
  	$payHtml .= '<span style="color:red;"><i class="fa fa-ticket-alt fa-2x align-middle"></i></span>';
  	if ($event_id != 0) {
  		$payHtml .= "</a>";
  	}
  }

  if (count(array_intersect($fee_event, ['door', 'dooronly']))>0) {
  	$payHtml .= '<span style="color:red;"><i class="fa fa-door-open fa-2x align-middle"></i></span>';
  }

  if ($payHtml != '') {
  	$eventHtml = "<b>$event</b>&nbsp;$payHtml<br>{$event_description}";
  } else {
  	$eventHtml = "<b>$event</b><br>{$event_description}";
  }

  $sponsors = json_decode($item->sponsors);
  ob_start();
  if ( $sponsors !== null ) {
    foreach ( SponsorshipType::valuesOrdered() as $sponsorType ) {
      foreach ( $sponsors AS $sponsor ) {
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
          <div class="text-center" style="font-size: smaller; color:#ffae00"><?=$type?>&nbsp;Sponsor</div>
        <?php
        endif;

        echo($this->sponsors->GetSmallImageLink($sponsor));
      }
    }
  } else {
    echo '&nbsp;';
  }

  $sponsor_logos = ob_get_clean();

  //<div class="col-12 col-lg-2 pt-lg-2 pb-lg-2 mt-2 mt-lg-1 mb-2 mb-lg-1">$location</div>
  ?>
  <div class="row row-striped g-0 <?=$featuredClass?>">
    <div class="col-9 col-lg-10 g-0 row">
      <div class="col-12 col-lg-2 pt-lg-2 pb-lg-2 mt-2 mb-2 tight"><?=$stime?>&ndash;<?=$etime?></div>
  <?php
  if ( $thumb != ''):
  else:
  endif;
      if ($thumb != ''):
        ?>
        <div class="col-12 col-lg-8 pt-lg-2 pb-lg-2 mt-2 mb-2">
          <div class="row">
          <div class="col-12 col-lg-8"><?=$eventHtml?></div>
          <div class="col-12 col-lg-4 align-middle text-lg-end"><?=$thumb?></div>
          </div>
        </div>
        <?php 
      else:
        ?>
        <div class="col-12 col-lg-8 pt-lg-2 pb-lg-2 mt-2 mb-2"><?=$eventHtml?></div>
        <?php
      endif;
      ?>
      <div class="col-12 col-lg-2 pt-lg-2 pb-lg-2 mt-2 mt-lg-1 mb-2 mb-lg-1 <?= $locationView ?>"><?=$location->value?></div>
    </div>
    <div class="col-3 col-lg-2 order-last pt-lg-2 pb-lg-2 mt-2 mb-2 g-0"><?=$sponsor_logos?></div>
  </div>
  <?php
}
?>
  </div>
