<?php

namespace ClawCorpLib\Grid;

// This class enforces data format for received form data

use ClawCorpLib\Grid\GridItem;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use Joomla\Database\DatabaseDriver;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Ebmgmt;
use Joomla\CMS\Factory;

class Grids
{
  /** @var ClawCorpLib/Grid/Grid[] */
  private array $grids = [];
  private DatabaseDriver $db;

  public function __construct(
    public array $coodinators = []
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');
  }

  public function createEvents()
  {
    $days = Helpers::getDays();

    $events = new ClawEvents(Aliases::current);
    $eventInfo = $events->getClawEventInfo();

    $event_count = 0;
    $this->loadGridsByEventAlias(Aliases::current);

    // ===== Update these for new imports =====
    $basetime = strtotime($eventInfo->start_date);
    $prefix = $eventInfo->shiftPrefix;
    $cutoffdate = $eventInfo->start_date;

    $location = ClawEvents::getLocationId(Aliases::location);

    $shiftAreas = Helpers::getClawFieldValues($this->db, 'shift_shift_area');

    foreach ( $shiftAreas AS $k => $o ) {
      if ( $k == 'tbd') continue;
      $categoryId = ClawEvents::getCategoryId('shifts-'.$k);
      $o->category_id = $categoryId;
    }

    /** @var \ClawCorpLib\Grid\GridItem */
    foreach ( $this->grids AS $grid ) {
      if ( $grid->event_id != 0 ) continue;
      if ( $grid->needed < 1 ) continue;
      if ( $grid->shift_area == 'tbd' ) continue;

      $main_category_id = $shiftAreas[$grid->shift_area]->category_id;
      $title = ucwords($grid->title);

      $btime = $basetime + (array_search($grid->day, $days)+1) * 86400; // seconds in a day
      $offset = Helpers::timeToInt($grid->time);

      if ( $offset === false ) die('Time error');
      
      $stime = $btime + $offset;
			$etime = $stime + $grid->length*60*60;

      $s = date('Y-m-d H:i:s', $stime);
      $stitle = date('D h:iA', $stime);
      //if ( $stitle == '12:00AM' ) $stitle = 'Midnight';
      //if ( $stitle == '12:00PM' ) $stitle = 'Noon';
      $e = date('Y-m-d H:i:s', $etime);
      $etitle = date('D h:iA', $etime);
      //if ( $etitle == '12:00AM' ) $etitle = 'Midnight';
      //if ( $etitle == '12:00PM' ) $etitle = 'Noon';
      
      $alias = strtolower($prefix.preg_replace('/[^a-z0-9_]+/','_',strtolower($title)).'-'.$grid->id.'-'.$grid->grid_id.'-'.$grid->day);
      $title .= " ($stitle-$etitle)";

      echo "<pre>Adding: {$grid->day},$alias,$s,$e,$title,{$grid->needed}</pre>\n";

      $description = implode('<br/>', [$grid->description, $grid->requirements]);

      $insert = new Ebmgmt($main_category_id, $alias, $title, $description);

      $insert->set('location_id', $location);
      $insert->set('event_date', $s);
      $insert->set('event_end_date', $e);
      $insert->set('event_capacity', $grid->needed);
      $insert->set('cut_off_date', $cutoffdate);
      $insert->set('enable_cancel_registration', 0);

      $insert->insert();
      
      $event_count++;

    }

    echo "<pre>Events Added: $event_count</pre>";

  }

  private function loadGridsByEventAlias(string $eventAlias)
  {
    $query = $this->db->getQuery(true);
    $query->select('*')
      ->from('#__claw_shifts')
      ->where('event = :event')
      ->where('published = 1')
      ->bind(':event', $eventAlias);
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
    $coordinators = json_decode($data['coordinators']);

    foreach ($grids as $gid => $g) {
      $time = $g->time;
      $length = $g->length;

      // Loop over set days

      foreach ($days as $day) {
        $pri = $day . 'pri';
        $event = $day . 'pri_eventid';
        $needed = (int)($g->$pri);
        $event_id = $g->$event;

        $this->grids[] = new GridItem(
          $data['id'],
          $gid,
          $time,
          $length,
          $data['title'],
          $data['description'],
          Aliases::current,
          $data['shift_area'],
          $data['requirements'],
          $coordinators,
          $data['published'],
          $day,
          $needed,
          $event_id
        );
      }
    }
  }
}
