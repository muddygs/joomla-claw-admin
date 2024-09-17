<?php

\defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Bootstrap;

//date_default_timezone_set(Aliases::timezone);


$tabs = [];
$tabData = [];

foreach ($this->events as $date => $events) {
  if ($date >= $this->start_date && $date <= $this->end_date) {
    # convert date to day of week
    $tabs[] = strtoupper(date('D', strtotime($date)));

    // Start clean for future tab data
    $html = '';

    $this->items = $events;
    $this->loadTemplate('items'); // handles output buffering
    $html .= $this->_output; // result of output buffering

    $tabData[] = $html;
  }
}

echo $this->params->get('ScheduleHeader') ?? '';

Bootstrap::writePillTabs($tabs, $tabData, $this->start_tab);

echo $this->params->get('ScheduleFooter') ?? '';
