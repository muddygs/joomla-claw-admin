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
use ClawCorpLib\Iterators\GridTimeArray;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

\defined('_JEXEC') or die;

class GridShift
{
  const SHIFTS_TABLE = '#__claw_shifts';
  const SHIFTS_TIMES_TABLE = '#__claw_shift_times';

  public EbPublishedState $published = EbPublishedState::any;
  public string $title = '';
  public string $description = '';
  public string $event = '';
  public string $shift_area = '';
  public string $requirements = '';
  public array $coordinators = []; // json storage
  public ?Date $mtime;

  public GridTimeArray $times;

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
    $result->shift_area = $this->shift_area;
    $result->requirements = $this->requirements;
    $result->coordinators = json_encode($this->coordinators);
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
    $this->shift_area = $result->shift_area;
    $this->requirements = $result->requirements;
    $this->coordinators = json_decode($result->coordinators) ?? [];
    $this->published = EbPublishedState::tryFrom($result->published) ?? EbPublishedState::any;
    $this->mtime = new Date($result->mtime);

    $query = $this->db->getQuery(true);
    $query->select('id')
      ->from(self::SHIFTS_TIMES_TABLE)
      ->where('sid = :id')
      ->bind(':sid', $this->id);
    $this->db->setQuery($query);
    $ids = $this->db->loadColumn();

    $this->times = new GridTimeArray();

    foreach ($ids as $id) {
      // Auto load from id
      $gridTime = new GridTime(
        id: $id,
      );

      $this->times[$id] = $gridTime;
    }
  }

  public function appendGridTime(GridTime $gridTime)
  {
    $this->times[] = $gridTime;
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
