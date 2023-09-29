<?php

namespace ClawCorpLib\Grid;

// This class enforces data format for received form data

use ClawCorpLib\Grid\GridItem;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use Joomla\Database\DatabaseDriver;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Ebmgmt;
use Joomla\CMS\Factory;

\defined('_JEXEC') or die;

class Grids
{
  /** @var ClawCorpLib/Grid/Grid[] */
  private array $grids = [];
  private DatabaseDriver $db;

  public function __construct(
    private string $eventAlias
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');
  }

  public function createEvents()
  {
    $days = Helpers::getDays();

    $events = new ClawEvents($this->eventAlias);
    $eventInfo = $events->getClawEventInfo();

    $newEvents = 0;
    $possibleEvents = 0;
    $this->loadGridsByEventAlias();

    // ===== Update these for new imports =====
    $basetime = strtotime($eventInfo->start_date);
    $prefix = $eventInfo->shiftPrefix;
    $cutoffdate = $eventInfo->start_date;

    $location = ClawEvents::getLocationId($eventInfo->locationAlias);

    $shiftAreas = Config::getColumn('shift_shift_area');

    foreach ( $shiftAreas AS $k => $o ) {
      if ( $k == 'tbd') continue;
      $categoryId = ClawEvents::getCategoryId('shifts-'.$k);
      $o->category_id = $categoryId;
    }

    ?>
    <table class="table">
      <thead>
        <tr>
          <th>Event ID</th>
          <th>Title</th>
          <th>Start</th>
          <th>End</th>
          <th>Slots</th>
        </tr>
      </thead>
      <tbody>

    <?php

    // Grid update tracking - update per shift grid
    $currentGid = 0;
    $currentGriditems = [];

    /** @var \ClawCorpLib\Grid\GridItem */
    foreach ( $this->grids AS $grid ) {
      if ( $currentGid != $grid->id ) {
        if ( count($currentGriditems) ) {
          $this->updateGrids($currentGid, $currentGriditems);
        }

        $currentGid = $grid->id;
        $currentGriditems = [];
      }

      if ( $grid->needed > 0 ) $possibleEvents++;
      
      if ( $grid->event_id != 0 || $grid->needed < 1 || $grid->shift_area == 'tbd' ) {
        continue;
      }

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
      $title = implode(' ', [$eventInfo->prefix, $title, "($stitle-$etitle)"]);

      $description = implode('<br/>', [$grid->description, $grid->requirements]);

      $insert = new Ebmgmt($main_category_id, $alias, $title, $description);

      $insert->set('location_id', $location);
      $insert->set('event_date', $s);
      $insert->set('event_end_date', $e);
      $insert->set('event_capacity', $grid->needed);
      $insert->set('cut_off_date', $cutoffdate);
      $insert->set('enable_cancel_registration', 0);

      $grid->event_id = $insert->insert();

      if ( $grid->event_id != 0 ):
      ?>
        <tr>
          <td><?=$grid->event_id?></td>
          <td><?=$title?></td>
          <td><?=$stitle?></td>
          <td><?=$etitle?></td>
          <td><?=$grid->needed?></td>
        </tr>

      <?php
        $currentGriditems[] = $grid;
        $newEvents++;
      endif;

    }

    ?>
      </tbody>
    </table>
    <pre>Events added <?= $newEvents ?> of <?= $possibleEvents ?> configured.</pre>
    <?php

    if ( count($currentGriditems) ) {
      $this->updateGrids($currentGid, $currentGriditems);
    }
    
  } 

  private function updateGrids(int $shift_id, array $gridItems)
  {
    if ( !count($gridItems)) return;

    $query = $this->db->getQuery(true);
    $query->select('*')
      ->from('#__claw_shifts')
      ->where('id = :shiftid')
      ->bind(':shiftid', $shift_id);
    $this->db->setQuery($query);

    $shift = $this->db->loadObject();
    $grids = json_decode($shift->grid);

    // TODO: Lazy but quick to implement
    foreach ( $grids AS $g ) {
      /** @var \ClawCorpLib\Grid\GridItem */
      foreach ( $gridItems AS $i ) {
        if ( $g->grid_id == $i->grid_id ) {
          $key = $i->day.'pri_eventid';
          $g->$key = $i->event_id;
        }
      }
    }

    $shift->grid = json_encode($grids);
    
    $query = $this->db->updateObject('#__claw_shifts', $shift, 'id', true);
  }

  private function loadGridsByEventAlias(): void
  {
    $query = $this->db->getQuery(true);
    $query->select('*')
      ->from('#__claw_shifts')
      ->where('event = :event')
      ->where('published = 1')
      ->bind(':event', $this->eventAlias)
      ->order('id');
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

    // var_dump($grids);

    $data = get_object_vars($shift);
    $coordinators = json_decode($data['coordinators']);

    /** @var \ClawCorpLib\Grid\GridItem */
    foreach ($grids AS $g) {
      // Loop over set days
      foreach ($days as $day) {
        $pri = $day . 'pri';
        $event = $day . 'pri_eventid';

        $this->grids[] = new GridItem(
          id: $data['id'],
          grid_id: $g->grid_id,
          time: $g->time,
          length: $g->length,
          title: $data['title'],
          description: $data['description'],
          event: $this->eventAlias,
          shift_area: $data['shift_area'],
          requirements: $data['requirements'],
          coordinators: $coordinators,
          published: $data['published'],
          day: $day,
          needed: (int)($g->$pri),
          event_id: $g->$event
        );
      }
    }
  }
}
