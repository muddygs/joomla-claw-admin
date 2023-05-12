<?php

namespace ClawCorpLib\Events;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Events\AbstractEvent;

use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Enums\EventPackageTypes;

class l1122 extends AbstractEvent
{
  public function PopulateInfo()
  {
    $info = (object)[];
    $info->description = 'Leather Getaway 22 STUB EVENT';
    $info->location = 'Cleveland, OH';
    $info->locationAlias = 'renaissance-cleveland';
    $info->start_date = '2023-04-03 00:00:00'; // Monday
    $info->end_date = 'next week Tuesday'; // Calculated
    $info->prefix = 'L22';
    $info->shiftPrefix = strtolower($info->prefix . '-shift-la-');
    $info->shiftPrefix = strtolower($info->prefix . '-shift-la-');
    $info->mainAllowed = true;
    $info->cancelBy = '2023-04-01 00:00:00'; // Varies too much to calculate
    $info->eventType = EventTypes::main;
    return $info;
  }

  public function PopulateEvents(string $prefix)
  {
    $prefix = strtolower($prefix);
  }
}
