<?php

\defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Bootstrap;
use Joomla\CMS\HTML\HTMLHelper;

$tabs = [];
$tabData = [];

foreach ($this->events as $date => $events) {
  if (count($events)) {
    # convert date to day of week
    $tabs[] = strtoupper(date('D', strtotime($date)));

    // Start clean for future tab data
    $html = '';

    $this->items = $events;
    $this->loadTemplate('items'); // handles output buffering
    $html .= $this->_output; // result of output buffering

    $tabData[] = HTMLHelper::_('content.prepare', $html);
  }
}

echo $this->params->get('ScheduleHeader') ?? '';

Bootstrap::writePillTabs($tabs, $tabData, $this->start_tab);

echo $this->params->get('ScheduleFooter') ?? '';
