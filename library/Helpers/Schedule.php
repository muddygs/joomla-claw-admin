<?php
namespace ClawCorpLib\Helpers;

use Joomla\Database\DatabaseDriver;

class Schedule {
  private array $cache;

  public function __construct(
    public string $event,
    private DatabaseDriver $db
  )
  {
    $this->loadSchedule();
  }

  private function loadSchedule()
  {
    $q = $this->db->getQuery(true);
    $q->select(['*'])
      ->from('#__claw_schedule')
      ->where('published = 1')
      ->where('event = :event')->bind(':event', $this->event)
      ->order('day ASC')
      ->order('featured DESC')
      ->order('start_time ASC');
    $this->db->setQuery($q);
    $this->cache = $this->db->loadObjectList('id');
  }

  public function getScheduleByDate(string $date): array
  {
    $result = [];
    foreach ( $this->cache AS $c ) {
      if ( $c->day == $date ) $result[] = $c;
    }

    return $result;
  }

}