<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Grid;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Iterators\GridTimeArray;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

\defined('JPATH_PLATFORM') or die;

class GridShift
{
  const SHIFTS_TABLE = '#__claw_shifts';
  const SHIFTS_TIMES_TABLE = '#__claw_shift_times';

  public EbPublishedState $published = EbPublishedState::any;
  public bool $enableNotifications = false;
  public string $title = '';
  public string $description = '';
  public string $event = '';
  public int $category = 0;
  public string $requirements = '';
  public array $coordinators = []; // json storage
  public ?Date $mtime;

  private GridTimeArray $times;
  private DatabaseDriver $db;

  public function __construct(
    public int $id = 0,
    public EventInfo $eventInfo,
  ) {
    if ($id < 0) {
      throw new \InvalidArgumentException('GridShift ID cannot be negative');
    }

    $this->db = Factory::getContainer()->get('DatabaseDriver');
    $this->event = $eventInfo->alias;
    if ($this->id) $this->fromSqlRow();
  }

  private function toSqlObject(): object
  {
    $result = new \stdClass();

    $result->id = $this->id;
    $result->event = $this->event;
    $result->title = $this->title;
    $result->description = $this->description;
    $result->category = $this->category;
    $result->requirements = $this->requirements;
    $result->coordinators = json_encode($this->coordinators);
    $result->notifications = (int)$this->enableNotifications;
    $result->published = $this->published->value;
    $result->mtime = $this->mtime->toSql();

    return $result;
  }

  private function fromSqlRow()
  {
    $query = $this->db->getQuery(true);
    $query->select('*')
      ->from(self::SHIFTS_TABLE)
      ->where('id = :id')
      ->bind(':id', $this->id);
    $this->db->setQuery($query);
    if (is_null($result = $this->db->loadObject())) {
      throw new \InvalidArgumentException('Shift ID does not exist');
    }

    $this->title = $result->title;
    $this->description = $result->description;
    $this->category = $result->category;
    $this->requirements = $result->requirements;
    $this->coordinators = json_decode($result->coordinators) ?? [];
    $this->enableNotifications = boolval($result->notifications);
    $this->published = EbPublishedState::tryFrom($result->published) ?? EbPublishedState::any;
    $this->mtime = new Date($result->mtime);

    $query = $this->db->getQuery(true);
    $query->select('id')
      ->from(self::SHIFTS_TIMES_TABLE)
      ->where('sid = :sid')
      ->bind(':sid', $this->id)
      ->order(['time', 'length', 'weight']);
    $this->db->setQuery($query);
    $ids = $this->db->loadColumn();

    $this->times = new GridTimeArray();

    foreach ($ids as $id) {
      // Auto load from id
      $gridTime = new GridTime(
        id: $id,
        sid: $this->id,
      );

      $this->times[$id] = $gridTime;
    }
  }

  public function getTimes(): GridTimeArray
  {
    return $this->times;
  }

  public function timesToFormArray(): array
  {
    $times = [];
    $timeKeys = GridTime::getKeys();

    /** @var \ClawCorpLib\Grid\GridTime */
    foreach ($this->times as $time) {
      $object = new \stdClass();
      $object->time = $time->time->format('H:i');
      $object->length = $time->length;
      $object->weight = $time->weight;
      $object->grid_id = $time->id;

      $needs = $time->getNeeds();
      $eventIds = $time->getEventIds();

      foreach ($timeKeys as $key) {
        $object->$key = $needs[$key];
        $eventIdKey = "{$key}_eventid";
        $object->$eventIdKey = $eventIds[$key];
      }

      $times[$time->id] = $object;
    }

    return $times;
  }

  public static function validateGrid(array $formData, string $key): bool
  {
    // TODO: if it's a new entry, some of the validations are still needed
    if (0 == $formData['id']) return true;

    if (!array_key_exists($key, $formData)) {
      throw new \InvalidArgumentException("$key does not exist in form data");
    }

    // We need to load the grid in the database for comparison
    $sid = $formData['id'];
    $eventInfo = new EventInfo($formData['event']);
    $gridShift = new GridShift($sid, $eventInfo);
    $times = $gridShift->getTimes();
    $timeKeys = GridTime::getKeys();

    // for a given start time/length, only one times is permitted to include
    $usageTracking = [];

    // unset entries as we find them
    $unprocessedRows = array_flip($times->keys());

    foreach ($formData[$key] as $input) {
      // grid_id is `id` from #__claw_shift_times
      $grid_id = $input['grid_id'];
      $grid_time = substr($input['time'], 0, 5); // assume H:i format

      // usage track init
      if (!array_key_exists($grid_time, $usageTracking)) {
        $usageTracking[$grid_time] = array_fill_keys($timeKeys, 0);
      }

      // merge usage or fail if slot is already used
      foreach ($timeKeys as $tk) {
        if (!empty($input[$tk]) && !empty($usageTracking[$grid_time][$tk])) {
          return false;
        }
        $usageTracking[$input['time']][$tk] = $input[$tk];
      }

      // New entries cannot be compared
      if (empty($grid_id)) continue;
      //




      // TODO: not that I do this anywhere, but this is assuming single editor
      unset($unprocessedRows[$grid_id]);

      if (!$times->offsetExists($grid_id)) {
        die("Conflict between form and database ids");
      }

      $time = $times[$grid_id];
      $eventIds = $time->getEventIds();
      $needed = $time->getNeeds();

      foreach ($timeKeys as $day) {
        // Not deployed? Skip because changes don't matter
        if (0 == $eventIds[$day]) continue;

        // Need decrease is permitted if registrants <= new value
        if ($input[$day] < $needed[$day]) {
          // TODO: check registrant count, for now: we error
          return false;
        }
      }
    }

    // Handle any rows disappearing
    // TODO: For now, delete permitted so long as all eventids are 0 
    foreach (array_keys($unprocessedRows) as $id) {
      $eventIds = array_filter($times[$id]->getEventIds());
      if (count($eventIds)) return false;
    }

    return true;
  }

  public static function saveGridTimeArray(array $formData, string $key)
  {
    $keys = Helpers::getDays();

    if ($formData['id'] < 1) {
      throw new \InvalidArgumentException("Shift must be save prior to parsing times");
    }

    if (!array_key_exists($key, $formData)) {
      throw new \InvalidArgumentException("$key does not exist in form data");
    }

    $sid = $formData['id'];
    $eventInfo = new EventInfo($formData['event']);
    $gridShift = new GridShift($sid, $eventInfo);
    $currentTimeIds = array_flip($gridShift->times->keys());

    $gridTimeArray = new GridTimeArray();

    foreach ($formData[$key] as $data) {
      if (empty($data['grid_id'])) $data['grid_id'] = 0;

      // TODO: something need to be fixed regarding changes on deployed events
      $gridTime = new GridTime($data['grid_id'], $sid, $data['length'], new Date($data['time']), $data['weight']);
      $gridTime->weight = $data['weight'];
      $gridTime->time = new Date($data['time']);
      $gridTime->length = $data['length'];

      foreach ($keys as $key) {
        $gridTime->setNeed($key, $data[$key]);
        $gridTime->setEventId($key, $data[$key . '_eventid']);
      }

      $gridTimeArray[] = $gridTime;
      unset($currentTimeIds[$data['grid_id']]);
    }

    // Anything left gets deleted
    foreach (array_keys($currentTimeIds) as $sid) {
      $gridShift->deleteTime($sid);
    }

    foreach ($gridTimeArray as $time) {
      $time->save();
    }
  }

  private function deleteTime(int $sid): bool
  {
    #TODO: validate existing events aren't being used

    $query = $this->db->getQuery(true);
    $query->delete(self::SHIFTS_TIMES_TABLE)
      ->where('id = :id')
      ->bind(':id', $sid);

    $this->db->setQuery($query);
    return $this->db->execute();
  }

  public function save(): int
  {
    $data = $this->toSqlObject();

    if ($this->id) {
      $result = $this->db->updateObject(self::SHIFTS_TABLE, $data, 'id');
      if (!$result) {
        throw new \Exception('Error during GridShift update');
      }
    } else {
      $result = $this->db->insertObject(self::SHIFTS_TABLE, $data, 'id');
      if (!$result) {
        throw new \Exception('Error during GridShift insert');
      }
      $this->id = $data->id;
    }

    foreach ($this->times as $time) {
      $time->save();
    }

    return $this->id;
  }
}
