<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Grid;

use Joomla\CMS\Date\Date;
use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

\defined('JPATH_PLATFORM') or die;

class GridTime
{
  private array $keys;
  private DatabaseDriver $db;

  // Volunteers Needed/EventIds needed by DOW (tue->mon)
  private array $needed = [];
  private array $eventIds = [];

  public function __construct(
    public int $id,
    public int $sid = 0,
    public float $length = 0,
    public ?Date $time = null,
    public int $weight = 1,
  ) {
    $this->keys = Helpers::getDays();
    $this->db = Factory::getContainer()->get('DatabaseDriver');

    if ($this->id) {
      self::fromSqlRow();
      return;
    }

    // Initialize needed and eventids to default
    self::keysValidation();

    if ($this->id != 0) {
      throw (new \exception('New GridTime record id must be 0.'));
    }

    # TODO: verify sid is valid
    if ($this->sid < 1) {
      throw (new \exception('Shift record id must be 1 or greater.'));
    }

    if ($this->length < 0.0) {
      throw (new \exception('Shift length must be zero or a positive value.'));
    }

    if (fmod($this->length * 4, 1) !== 0.0) {
      throw (new \exception('Shift length must be in 1/4 hour increments.'));
    }
  }

  public function getKeys()
  {
    return $this->keys;
  }

  private function haveSameKeys(array $array1, array $array2): bool
  {
    return empty(array_diff(array_keys($array1), array_keys($array2))) &&
      empty(array_diff(array_keys($array2), array_keys($array1)));
  }

  private function keysValidation()
  {
    foreach ($this->keys as $day) {
      if (!array_key_exists($day, $this->needed)) {
        $this->needed[$day] = 0;
      }

      if (!array_key_exists($day, $this->eventIds)) {
        $this->eventIds[$day] = 0;
      }
    }

    if (!self::haveSameKeys($this->needed, array_flip($this->keys)))
      throw (new \UnexpectedValueException('Unknown key in GridTime needed'));
    if (!self::haveSameKeys($this->eventIds, array_flip($this->keys)))
      throw (new \UnexpectedValueException('Unknown key in GridTime eventIds'));
  }

  private function fromSqlRow()
  {
    $query = $this->db->getQuery(true);
    $query->select('*')
      ->from(GridShift::SHIFTS_TIMES_TABLE)
      ->where('id = :id')
      ->bind(':id', $this->id);
    $this->db->setQuery($query);
    $o = $this->db->loadObject();

    if ($this->sid != 0 && $o->sid != $this->sid) {
      throw new \InvalidArgumentException("Shift ID mismatch $o->sid != $this->sid");
    }

    $this->sid = $o->sid;
    $this->time = new Date($o->time);
    $this->length = $o->length;
    $this->weight = $o->weight;
    $this->needed = (array)json_decode($o->needed) ?? [];
    $this->eventIds = (array)json_decode($o->event_ids) ?? [];

    self::keysValidation();
  }

  private function toSqlObject(): object
  {
    self::keysValidation();

    $o = new \stdClass();
    $o->id = $this->id;
    $o->sid = $this->sid;
    $o->time = $this->time->toSql();
    $o->length = $this->length;
    $o->weight = $this->weight;
    $o->needed = json_encode($this->needed);
    $o->event_ids = json_encode($this->eventIds);
    return $o;
  }

  public function setNeed(string|int $dow, int $need)
  {
    if ($dow instanceof int) {
      $dow = Helpers::getDays()[$dow];
    }

    $this->needed[$dow] = $need;
  }

  public function getNeeds(): array
  {
    return $this->needed;
  }

  /**
   * Set all needs from an array
   * @param int[] $needs
   * @throws \TypeError|\LengthException|\InvalidArgumentException
   */
  public function setNeeds(int ...$needs)
  {
    if (count($needs) != count($this->keys)) {
      throw (new \LengthException('Count of parameters must be ' . count($this->keys)));
    }

    $tmp_needed = [];
    reset($this->keys);
    $key = current($this->keys);

    foreach ($needs as $need) {
      if ($need >= 0) {
        $tmp_needed[$key] = $need;
        $key = next($this->keys);
      } else {
        throw (new \InvalidArgumentException('Need value must be at least 0.'));
      }
    }

    $this->needed = $tmp_needed;
  }

  public function setEventId(string|int $dow, int $need)
  {
    if ($dow instanceof int) {
      $dow = Helpers::getDays()[$dow];
    }

    $this->eventIds[$dow] = $need;
  }

  public function getEventIds(): array
  {
    return $this->eventIds;
  }

  /**
   * Set all event ids from an array, allows 0 for unconfigured event
   * @param int[] $eventIds
   * @throws \TypeError|\LengthException|\InvalidArgumentException
   */
  public function setEventIds(int ...$eventIds)
  {
    if (count($eventIds) != count($this->keys)) {
      throw (new \LengthException('Count of parameters must be ' . count($this->keys)));
    }

    $tmp_eventids = [];
    reset($this->keys);
    $key = current($this->keys);

    # TODO: when not 0, validate it's a valid event id in #__eb_events
    foreach ($eventIds as $eventId) {
      $tmp_eventids[$key] = $eventId;
      $key = next($this->keys);
    }

    $this->eventIds = $tmp_eventids;
  }

  public static function byEventId(int $eventId): ?GridTime
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');
    $query = $db->getQuery(true);

    $query->select('id')
      ->from(GridShift::SHIFTS_TIMES_TABLE)
      ->where('JSON_SEARCH(`event_ids`, \'one\', ' . $db->q($eventId) . ') IS NOT NULL');
    $db->setQuery($query);
    $gridTimeId = $db->loadResult();

    if (!is_null($gridTimeId)) {
      return new GridTime($gridTimeId);
    }

    return null;
  }

  public function save(): int
  {
    $data = self::toSqlObject();
    if ($this->id) {
      $result = $this->db->updateObject(GridShift::SHIFTS_TIMES_TABLE, $data, 'id');
      if (!$result) {
        throw new \Exception('Error during GridTime update');
      }
    } else {
      $result = $this->db->insertObject(GridShift::SHIFTS_TIMES_TABLE, $data, 'id');
      if (!$result) {
        throw new \Exception('Error during GridTime insert');
      }
      $this->id = $data->id;
    }

    return $this->id;
  }
}
