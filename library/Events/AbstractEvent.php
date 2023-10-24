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
  private array $mainEventPackageTypes = [];
  private array $packageTypesMap = []; // Map of packageType to $events index

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
   * @param EventPackageTypes $packageType Event alias in Event Booking
   * @return int Event ID
   */
  public function getEventId(EventPackageTypes $packageType): int
  {
    if ( array_key_exists($packageType->value, $this->packageTypesMap) ) {
      return $this->events[$this->packageTypesMap[$packageType->value]]->eventId;
    }

    throw new UnexpectedValueException(__FILE__ . ': Unknown packageType: ' . $packageType->value);
  }

  /**
   * Returns the full ClawEvent record for a given EventPackageTypes enum
   * @param EventPackageTypes $packageType Event alias in Event Booking
   * @return ClawEvent Event ID (or null if not found)
   */
  public function getClawEvent(EventPackageTypes $packageType): ?ClawEvent
  {
    if ( array_key_exists($packageType->value, $this->packageTypesMap) ) {
      return $this->events[$this->packageTypesMap[$packageType->value]];
    }

    return null;
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

  public function AppendEvent(ClawEvent $e): int
  {
    $value = $e->eventPackageType->value;

    // Validate main event uniqueness
    if ( $e->isMainEvent ) {
      if ( array_key_exists($value, $this->mainEventPackageTypes) ) {
        throw new UnexpectedValueException(__FILE__ . ': Duplicate eventPackageType: ' . $value);
      }

      $this->mainEventPackageTypes[$value] = true;
    }

    $this->events[] = $e;
    $this->packageTypesMap[$e->eventPackageType->value] = sizeof($this->events) - 1;

    return $e->eventId;
  }

  abstract public function PopulateInfo(): EventInfo;
  abstract public function PopulateEvents(string $prefix, bool $quiet = false);
}
