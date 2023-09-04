<?php

namespace ClawCorpLib\Events;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Events\AbstractEvent;
use ClawCorpLib\Enums\EventTypes;

class refunds extends AbstractEvent
{
  public function PopulateInfo()
  {
    $info = (object)[];
    $info->description = 'refunds';
    $info->location = '';
    $info->locationAlias = '';
    $info->start_date = '2023-04-03 00:00:00'; // Monday
    $info->end_date = 'next week Tuesday'; // Calculated
    $info->prefix = 'XXX';
    $info->shiftPrefix = '';
    $info->mainAllowed = false;
    $info->cancelBy = '2023-04-01 00:00:00'; // Varies too much to calculate
    $info->eventType = EventTypes::refunds;
    $info->timezone = 'America/New_York';
    $info->active = true;
    return $info;
  }

  public function PopulateEvents(string $prefix)
  {
  }
}
