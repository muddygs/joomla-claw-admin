<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EbPublishedState;
use Joomla\CMS\Date\Date;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Factory;

class ScheduleRecord
{
  const TABLE_NAME = "#__claw_schedule";

  private DatabaseDriver $db;

  public ?Date $datetime_start;
  public ?Date $datetime_end;
  public string $event_alias;
  public string $event_description;
  public string $event_id; // maps into #__eventbooking_events
  public string $event_title;
  public bool $featured;
  public array $fee_event;
  public int $location; // maps into #__claw_locations
  public ?Date $mtime;
  public string $onsite_description;
  public string $poster;
  public EbPublishedState $published;
  public array $sponsors;

  public function __construct(
    public int $id = 0
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');
    date_default_timezone_set('UTC');

    if ($id > 0) {
      $this->fromSqlRow();
    }
  }

  private function toSqlObject(): object
  {
    $result = new \stdClass();

    $result->id = $this->id;
    $result->published = $this->published->value;
    $result->datetime_start = $this->datetime_start->toSql();
    $result->datetime_end = $this->datetime_end->toSql();
    $result->event_alias = $this->event_alias;
    $result->event_description = $this->event_description;
    $result->event_id = $this->event_id;
    $result->event_title = $this->event_title;
    $result->featured = $this->featured ? '1' : '0';
    $result->fee_event = json_encode($this->fee_event);
    $result->location = $this->location;
    $result->onsite_description = $this->onsite_description;
    $result->poster = $this->poster;
    $result->sponsors = json_encode($this->sponsors);
    $result->mtime = $this->mtime->toSql();

    return $result;
  }

  private function fromSqlRow()
  {
    $query = $this->db->getQuery(true);
    $query->select('*')
      ->from(self::TABLE_NAME)
      ->where('id = :id')
      ->bind(':id', $this->id);
    $this->db->setQuery($query);
    $result = $this->db->loadObject();

    if ($result == null) {
      throw new \Exception("Schedule record $this->id not found.");
    }

    $this->fromSql($result);
  }

  public function fromSql(object $o)
  {
    $this->id = $o->id;
    $this->published = EbPublishedState::tryFrom($o->published) ?? EbPublishedState::any;
    try {
      $this->datetime_start = Factory::getDate($o->datetime_start);
    } catch (\DateMalformedStringException) {
      $this->datetime_start = null;
    }
    try {
      $this->datetime_end = new Date($o->datetime_end);
    } catch (\DateMalformedStringException) {
      $this->datetime_end = null;
    }
    $this->event_alias = $o->event_alias;
    $this->event_description = $o->event_description;
    $this->event_id = $o->event_id; // EventBooking id
    $this->event_title = $o->event_title;
    $this->featured = boolval($o->featured);
    $this->fee_event = json_decode($o->fee_event ?? []) ?? [];
    $this->location = $o->location;
    $this->mtime = Factory::getDate($o->mtime);
    $this->onsite_description = $o->onsite_description;
    $this->poster = $o->poster;
    $this->sponsors = json_decode($o->sponsors ?? []) ?? [];
  }

  public function save(): bool
  {
    $this->mtime = new Date('now', 'UTC');
    $data = $this->toSqlObject();

    if ($this->id == 0) {
      $this->db->insertObject(self::TABLE_NAME, $data);
      $this->id = $this->db->insertid();
    } else {
      $this->db->updateObject(self::TABLE_NAME, $data, 'id');
    }

    return true;
  }
}
