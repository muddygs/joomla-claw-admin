<?php

namespace ClawCorpLib\Events;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\EventInfo;
use UnexpectedValueException;

abstract class AbstractEvent
{
  private EventInfo $info;
  private array $events;

  public function __construct(
    public string $alias
  )
  {
    $this->events = [];
    $this->info = $this->PopulateInfo();
    $this->PopulateEvents($this->info->prefix);
  }

  /**
   * Returns an array of all the enrolled events in this class when initialized
   * @return array List of event IDs
   */
  public function getEventIds(): array
  {
    $ids = array_column($this->events, 'eventId');
    sort($ids);
    return $ids;
  }

  /**
   * Returns the Event Booking event ID for a given EventPackageTypes enum
   * @param EventPackageTypes $eventAlias Event alias in Event Booking
   * @return int Event ID
   */
  public function getEventId(EventPackageTypes $eventAlias): int
  {
    /** @var \ClawInfoLib\Lib\ClawEvent */
    foreach ( $this->events as $e ) {
      if ($e->clawPackageType == $eventAlias) {
        return $e->eventId;
      }
    }

    throw new UnexpectedValueException(__FILE__ . ': Unknown eventAlias: ' . $eventAlias->value);
  }


  public function getInfo(): \ClawCorpLib\Lib\EventInfo {
    return $this->info;
  }

  public function getEvents(): array { 
    return $this->events;
  }

  public function mergeEvents(array $otherEvents) {
    $this->events = array_merge($this->events, $otherEvents);
  }

  public function AppendEvent(ClawEvent $e)
  {
    $this->events[] = $e;
  }

  abstract public function PopulateInfo(): EventInfo;
  abstract public function PopulateEvents(string $prefix, bool $quiet = false);
}
