<?php

namespace ClawCorpLib\Grids;

// This class enforces data format for received form data

use ClawCorpLib\Grid\GridItem;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use Joomla\Database\DatabaseDriver;
use ClawCorpLib\Lib\ClawEvents;

class Grids
{
  /** @var ClawCorpLib/Grid/Grid[] */
  private array $grids = [];

  public function __construct(
    public DatabaseDriver $db,
    public array $coodinators = []
  ) {
  }

  public function createEvents()
  {
    $events = new ClawEvents(Aliases::current);
    $eventInfo = $events->getClawEventInfo();

    $event_count = 0;
    $this->loadGridsByEventAlias(Aliases::current);
  }

  private function loadGridsByEventAlias(string $eventAlias)
  {
    $query = $this->db->getQuery(true);
    $query->select('*')
      ->from('#__claw_shifts')
      ->where('event = :event')
      ->where('published = 1')
      ->bind(':event', Aliases::current);
    $this->db->setQuery($query);

    $shifts = $this->db->loadObjectList('id');

    foreach ($shifts as $s) {
      $this->parseGridJson($s);
    }
  }

  private function parseGridJson(object $shift)
  {
    $days = Helpers::getDays();

    $grids = json_decode($shift->grid);

    $data = get_object_vars($shift);

    foreach ($grids as $g) {
      $time = $g->time;
      $length = $g->length;

      // Loop over set days

      foreach ($days as $day) {
        $pri = $day . 'pri';
        $event = $day . 'pri_eventid';
        $needed = $g->$pri;
        $event_id = $g->$event;

        $grids[] = new GridItem(
          $data['id'],
          $time,
          $length,
          $data['title'],
          $data['description'],
          Aliases::current,
          $data['shift_area'],
          $data['requirements'],
          $data['coordinators'],
          $data['published'],
          $day,
          $needed,
          $event_id
        );
      }
    }
  }

  private function nextId(): int
  {
    $query = 'SELECT max(row_id) FROM #__claw_shifts_grids WHERE shift_id=' . $this->db->q($this->sid);
    $this->db->setQuery($query);
    $lastId = $this->db->loadResult();

    return !$lastId ? 1 : $lastId + 1;
  }
}
