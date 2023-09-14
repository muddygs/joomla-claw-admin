<?php

namespace ClawCorpLib\Events;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Events\AbstractEvent;

use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Enums\EventPackageTypes;

class virtualclaw extends AbstractEvent
{
  public function PopulateInfo()
  {
    $info = (object)[];
    $info->description = 'Virtual CLAW';
    $info->location = 'Zoom';
    $info->locationAlias = '';
    $info->start_date = '2023-04-03 00:00:00'; // Monday
    $info->end_date = 'next week Tuesday'; // Calculated
    $info->prefix = 'VC';
    $info->shiftPrefix = '';
    $info->mainAllowed = false;
    $info->cancelBy = '2023-04-01 00:00:00'; // Varies too much to calculate
    $info->eventType = EventTypes::vc;
    $info->timezone = 'America/New_York';
    $info->active = true;
    return $info;
  }

  public function PopulateEvents(string $prefix, bool $quiet = false)
  {
    $prefix = strtolower($prefix);

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'X',
      'description' => 'Virtual CLAW',
      'clawPackageType' => EventPackageTypes::none,
      'isMainEvent' => true,
      'link' => '/reg-vc',
      'couponValue' => 20,
      'fee'=>20,
      'eventId' => ClawEvents::getEventId('virtual-claw-2023-08'),
      'category' => ClawEvents::getCategoryId('virtual_claw'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users','Administrator']
    ]));
  }
}
