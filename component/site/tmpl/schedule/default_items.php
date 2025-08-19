<?php

use Joomla\CMS\HTML\HTMLHelper;
use ClawCorpLib\Helpers\Helpers;

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

  foreach ($this->items as $item) {
    $stime = $item->start_time;
    $stime = Helpers::formatTime($stime);
    $etime = $item->end_time;
    $etime = Helpers::formatTime($etime);

    $this->id = $item->id;
    $event = $item->event_title;

    $poster = json_decode($item->poster);
    if (!is_null($poster)) {
      $poster = explode('#', $poster->imagefile)[0];
    }

    if ($this->eventInfo->onsiteActive) {
      $event_description = $item->onsite_description == '' ?  $item->event_description : $item->onsite_description;
    } else {
      $event_description = $item->event_description;
    }

    $fee_event = explode(',', $item->fee_event);
    $event_id = $item->event_id;
    $location = array_key_exists($item->location, $this->locations) ? $this->locations[$item->location]->value : '';
    if ($locationView == 'd-none') {
      $location = '';
    }

    if ($this->eventInfo->onsiteActive) {
      $event_description = $item->onsite_description == '' ?  $item->event_description : $item->onsite_description;
    } else {
      $event_description = $item->event_description;
    }

    $featuredClass = $item->featured ? 'border border-danger border-top-0 border-bottom-0 border-end-0' : '';

    $payHtml = '';

    if (count(array_intersect($fee_event, ['preorder', 'dooronly'])) > 0) {
      if ($event_id != 0) {
        $payHtml = "<a href=\"/index.php?option=com_eventbooking&view=event&id={$event_id}\" data-toggle=\"tooltip\" title=\"Purchase Ticket\"";
      }
      $payHtml .= '<span style="color:var(--claw-danger);"><i class="fa fa-ticket-alt fa-2x align-middle"></i></span>';
      if ($event_id != 0) {
        $payHtml .= "</a>";
      }
    }

    if (count(array_intersect($fee_event, ['door', 'dooronly'])) > 0) {
      $payHtml .= '<span style="color:var(--claw-danger);"><i class="fa fa-door-open fa-2x align-middle"></i></span>';
    }

    if ($payHtml != '') {
      $eventHtml = "<b>$event</b>&nbsp;$payHtml<br>{$event_description}";
    } else {
      $eventHtml = "<b>$event</b><br>{$event_description}";
    }

    $sponsors = json_decode($item->sponsors);
    ob_start();
    if (!is_null($sponsors)) {
      foreach ($sponsors as $sponsorId) {
        /** @var \ClawCorpLib\Lib\Sponsor */
        $sponsorItem = $this->sponsors[$sponsorId];

  ?>
        <div class="text-center" style="font-size: smaller; color:var(--claw-warning)"><?= $sponsorItem->type->toString() ?>&nbsp;Sponsor</div>
    <?php

        echo HTMLHelper::_('image', $sponsorItem->logo_small, $sponsorItem->name, ['class' => 'd-block mx-auto']);
      }
    } else {
      echo '&nbsp;';
    }

    $sponsor_logos = ob_get_clean();

    ?>
    <div class="row row-striped g-0 <?= $featuredClass ?>">
      <div class="col-9 col-lg-10 g-0 row">
        <div class="col-12 col-lg-2 pt-lg-2 pb-lg-2 mt-2 mb-2 tight"><?= $stime ?>&ndash;<?= $etime ?></div>
        <?php
        if (!empty($poster)):
          $this->poster = $poster;
        ?>
          <div class="col-12 col-lg-8 pt-lg-2 pb-lg-2 mt-2 mb-2">
            <div class="row">
              <div class="col-12 col-lg-8"><?= $eventHtml ?></div>
              <div class="col-12 col-lg-4 align-middle text-lg-end"><?= $this->loadTemplate('poster') ?></div>
            </div>
          </div>
        <?php
        else:
        ?>
          <div class="col-12 col-lg-8 pt-lg-2 pb-lg-2 mt-2 mb-2"><?= $eventHtml ?></div>
        <?php
        endif;
        ?>
        <div class="col-12 col-lg-2 pt-lg-2 pb-lg-2 mt-2 mt-lg-1 mb-2 mb-lg-1 <?= $locationView ?>"><?= $location ?></div>
      </div>
      <div class="col-3 col-lg-2 order-last pt-lg-2 pb-lg-2 mt-2 mb-2 g-0"><?= $sponsor_logos ?></div>
    </div>
  <?php
  }
  ?>
</div>
