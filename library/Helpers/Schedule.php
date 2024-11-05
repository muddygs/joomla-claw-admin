<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Helpers;

use DateTime;
use Joomla\Database\DatabaseDriver;

class Schedule
{
  private array $cache;

  public function __construct(
    public string $event,
    private DatabaseDriver &$db,
    public readonly string $view = 'default',
    public readonly ?DateTime $date = null
  ) {
    $this->loadSchedule();
  }

  private function loadSchedule()
  {
    $q = $this->db->getQuery(true);
    $q->select(['*'])
      ->select('TIME_TO_SEC(start_time) AS start_time_int')
      ->select('IF (TIME_TO_SEC(TIMEDIFF(end_time, start_time)) > 0, TIME_TO_SEC(end_time), TIME_TO_SEC(end_time) + 86400) AS end_time_int')
      ->from('#__claw_schedule')
      ->where('published = 1')
      ->where('event = :event')->bind(':event', $this->event)
      ->order('day ASC');

    if ('default' == $this->view) {
      $q->order('featured DESC');
    }

    $q->order('start_time_int ASC')
      ->order('end_time_int ASC')
      ->order('event_title ASC');

    switch ($this->view) {
      case 'upcoming':
        $now = $this->date == null ? 'NOW()' : $this->date->format('Y-m-d H:i:s');
        $q->where('TIMESTAMP(day,start_time) >= ' . $now);
        break;
      default:
    }

    $this->db->setQuery($q);
    $this->cache = $this->db->loadObjectList('id');
  }

  public function getScheduleByDate(string $date): array
  {
    $result = [];
    foreach ($this->cache as $c) {
      if ($c->day == $date) $result[] = $c;
    }

    return $result;
  }

  public function getUpcomingEvents(int $limit = 10)
  {
    $result = [];
    reset($this->cache);

    for ($i = 0; $i < $limit && current($this->cache); $i++) {
      $result[] = current($this->cache);
      next($this->cache);
    }

    return $result;
  }

  public function toCSV(string $filename)
  {
    $locations = new Locations($this->event);

    // Load database columns
    $columnNames = array_keys($this->db->getTableColumns('#__claw_schedule'));
    $columnNames[] = 'track';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
    ob_clean();
    ob_start();
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $columnNames);

    foreach ($this->cache as $c) {
      $row = [];
      foreach ($columnNames as $col) {
        switch ($col) {
          case 'id':
            $row[] = 'schedule_' . $c->$col;
            break;
          case 'start_time':
          case 'end_time':
            $time = Helpers::formatTime($c->$col);
            if ($time == 'Midnight') $time = '12:00 AM';
            if ($time == 'Noon') $time = '12:00 PM';
            $row[] = $time;
            break;
          case 'sponsors':
            $json = json_decode($c->$col);
            if ($json !== null) {
              // prefix with sponsor_
              $json = array_map(function ($v) {
                return 'sponsor_' . $v;
              }, $json);
              $row[] = implode(',', $json);
            } else {
              $row[] = '';
            }
            break;
          case 'location':
            $location = $locations->GetLocationById($c->$col)->value;
            $row[] = $location;
            break;
          case 'track':
            // track is day converted to day of week
            $row[] = date('l', strtotime($c->day));
            break;
          case 'event_description':
            $row[] = Helpers::cleanHtmlForCsv($c->$col);
            break;
          default:
            $row[] = $c->$col;
            break;
        }
      }

      fputcsv($fp, $row);
    }

    fclose($fp);
    ob_end_flush();
  }
}
