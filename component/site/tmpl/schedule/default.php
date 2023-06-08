<?php

\defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Bootstrap;

//date_default_timezone_set(Aliases::timezone);

$tabs = [];
$tabData = [];

foreach ( $this->events AS $date => $events ) {
  if ( $date >= $this->start_date && $date <= $this->end_date ) {
    # convert date to day of week
    $tabs[] = strtoupper(date('D', strtotime($date)));

    // Start clean for future tab data
    $html = '';

    ob_start();
    headerSchedule();
    $html .= ob_get_contents();
    ob_clean();

    $this->items = $events;
    $this->loadTemplate('items'); // handles output buffering
    $html .= $this->_output; // result of output buffering

    ob_start();
    endSchedule();
    $html .= ob_get_contents();
    ob_clean();

    $tabData[] = $html;
  }
}

echo $this->params->get('ScheduleHeader') ?? '';

Bootstrap::writePillTabs($tabs, $tabData, $this->start_tab);

echo $this->params->get('ScheduleFooter') ?? '';

function headerSchedule() {
?>
  <div class="container">
    <div class="row row-striped g-0">
      <div class="col-9 col-lg-10 row">
        <div class="col-12 col-lg-2 pt-lg-2 pb-lg-2 mt-2 mb-2 font-weight-bold tight">Time</div>
        <div class="col-12 col-lg-8 pt-lg-2 pb-lg-2 mt-0 mt-lg-1 mb-0 mb-lg-1 font-weight-bold tight">Event</div>
        <div class="col-12 col-lg-2 pt-lg-2 pb-lg-2 mt-0 mt-lg-1 mb-2 mb-lg-1 font-weight-bold tight">Location</div>
      </div>
      <div class="col-3 col-lg-2 order-last pt-2 pb-2 mt-2 mb-2 font-weight-bold text-center g-0">Sponsor</div>
    </div>
<?php
}

function endSchedule() {
?>
  </div>
<?php
}