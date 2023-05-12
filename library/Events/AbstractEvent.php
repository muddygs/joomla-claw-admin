<?php

namespace ClawCorpLib\Events;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\EventInfo;

abstract class AbstractEvent
{
  private EventInfo $info;
  private array $events;

  public function __construct(
    public string $alias
  )
  {
    $this->events = [];
    $this->info = new EventInfo($this->PopulateInfo());
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

  public function getInfo(): \ClawCorpLib\Lib\EventInfo {
    return $this->info;
  }

  public function getEvents(): array { 
    return $this->events;
  }

  public function AppendEvent(ClawEvent $e)
  {
    $this->events[] = $e;
  }

  abstract public function PopulateInfo();
  abstract public function PopulateEvents(string $prefix);
}
