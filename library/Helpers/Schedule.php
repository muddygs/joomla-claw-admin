<?php
namespace ClawCorpLib\Helpers;

use Joomla\Database\DatabaseDriver;

class Schedule {
  private array $cache;

  public function __construct(
    public string $event,
    private DatabaseDriver &$db
  )
  {
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
      ->order('day ASC')
      ->order('featured DESC')
      ->order('start_time_int ASC')
      ->order('end_time_int ASC')
      ->order('event_title ASC');
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

  public function toCSV(string $filename)
  {
    // Load database columns
    $columnNames = array_keys($this->db->getTableColumns('#__claw_schedule'));
    $columnNames[] = 'track';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'. $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
    ob_clean();
    ob_start();
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $columnNames);

    foreach ( $this->cache AS $c) {
      $row = [];
      foreach ( $columnNames AS $col ) {
        switch($col) {
          case 'id':
            $row[] = 'schedule_'.$c->$col;
            break;
          case 'start_time':
          case 'end_time':
            $time = Helpers::formatTime($c->$col);
            if ( $time == 'Midnight' ) $time = '12:00 AM';
            if ( $time == 'Noon' ) $time = '12:00 PM';
            $row[] = $time;
            break;
          case 'sponsors':
            $json = json_decode($c->$col);
            if ( $json !== null ) {
              // prefix with sponsor_
              $json = array_map(function($v) { return 'sponsor_'.$v; }, $json);
              $row[] = implode(',', $json);
            } else {
              $row[] = '';
            }
            break;
          case 'location':
            $location = Locations::GetLocationById($c->$col)->value;
            $row[] = $location;
            break;
          case 'track':
            // track is day converted to day of week
            $row[] = date('l', strtotime($c->day));
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