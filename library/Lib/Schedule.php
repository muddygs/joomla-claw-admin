<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Iterators\ScheduleArray;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

class Schedule
{
  public ScheduleArray $scheduleArray;

  private ?DatabaseDriver $db;

  function __construct(
    private EventInfo $eventInfo,
    private string $view = '',
    private int $limit = 10,
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');
    $this->scheduleArray = new ScheduleArray();

    $this->loadSchedule();
  }

  public static function get(EventInfo $eventInfo): ScheduleArray
  {
    return (new Schedule($eventInfo))->scheduleArray;
  }

  private function loadSchedule()
  {
    $alias = $this->eventInfo->alias;
    $published = EbPublishedState::published->value;

    $q = $this->db->getQuery(true);
    $q->select(['*'])
      ->from('#__claw_schedule')
      ->where('published = :published')->bind(':published', $published) // only published items loaded
      ->where('event_alias = :event')->bind(':event', $alias)
      ->order('DATE(datetime_start) ASC');

    switch ($this->view) {
      case 'upcoming':
        $q->where('`datetime_end` >= NOW()')
          ->setLimit($this->limit);
        break;
      default:
        $q->order('featured DESC');
    }

    $q->order('datetime_start ASC');
    $q->order('datetime_end ASC');
    $q->order('event_title ASC');

    $this->db->setQuery($q);
    $rows = $this->db->loadObjectList('id');

    foreach ($rows as $id => $row) {
      $record = new ScheduleRecord();
      $record->fromSql($row);
      $this->scheduleArray[$id] = $record;
    }
  }

  public static function getUpcomingEvents(EventInfo $eventinfo, int $limit = 10): ScheduleArray
  {
    $schedule = new Schedule($eventinfo, 'upcoming', $limit);
    return $schedule->scheduleArray;
  }
}
