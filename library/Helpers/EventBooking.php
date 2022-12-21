<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;

class EventBooking
{
  /**
   * Returns event ID and Title for ticketed events
   */
  static function LoadTicketedEvents(ClawEvents $e): array
  {
    $result = [];

    $info = $e->getClawEventInfo();
    $events = ClawEvents::getEventsByCategoryId(ClawEvents::getCategoryIds(Aliases::categoriesTicketedEvents), $info);

    foreach ($events as $e) {
      $result[$e->id] = $e->title;
    }

    return $result;
  }

  /**
   * Reads session information and returns link to last-visited registration page
   * @return string 
   */
  static function getRegistrationLink(): string
  {
    $clawLink = sessionGet('regtype');

    $referrer = sessionGet('referrer');

    if ('' != $referrer) {
      $clawLink = $clawLink . '?referrer=' . $referrer;
    }

    return $clawLink;
  }

}