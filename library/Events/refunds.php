<?php

namespace ClawCorpLib\Events;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Events\AbstractEvent;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Lib\EventInfo;

class refunds extends AbstractEvent
{
  public function PopulateInfo(): EventInfo
  {
    return new EventInfo(
      description: 'refunds',
      location: '',
      locationAlias: '',
      start_date: '1999-01-04 00:00:00', // Monday
      end_date: 'next week Tuesday', // Calculated
      prefix: 'XXX',
      shiftPrefix: '',
      mainAllowed: false,
      cancelBy: '2023-04-01 00:00:00', // Varies too much to calculate
      eventType: EventTypes::refunds,
      timezone: 'America/New_York',
      active: true,
      onsiteActive: false,
      termsArticleId: 0,
    );
  }

  public function PopulateEvents(string $prefix, bool $quiet = false)
  {
  }
}
