<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Skills;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use ClawCorpLib\Enums\SkillOwnership;
use ClawCorpLib\Enums\SkillPublishedState;

\defined('JPATH_PLATFORM') or die;

final class Skill
{
  const SKILLS_TABLE = '#__claw_skills';

  private DatabaseDriver $db;

  public Date $day;
  public Date $mtime;
  public Date $submission_date;
  public SkillOwnership $ownership;
  public SkillPublishedState $published;
  public array $other_presenter_ids;
  public int $av;
  public int $length_info;
  public int $location;
  public int $presenter_id;
  public string $archive_state;
  public string $audience;
  public string $category;
  public string $comments;
  public string $copresenter_info;
  public string $description;
  public string $equipment_info;
  public string $event;
  public string $requirements_info;
  public string $time_slot;
  public string $title;
  public string $track;
  public string $type;

  public function __construct(
    public int $id,
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');

    if ($id < 0) {
      throw new \InvalidArgumentException("Presenter ID must be 0 (for new) or a valid database row id.");
    }

    if ($this->id) {
      self::fromSqlRow();
    }
  }

  private function fromSqlRow()
  {
    $query = $this->db->getQuery(true);
    $query->select('*')
      ->from(self::SKILLS_TABLE)
      ->where('id = :id')
      ->bind(':id', $this->id);
    $this->db->setQuery($query);
    if (is_null($o = $this->db->loadObject())) {
      throw new \InvalidArgumentException("Invalid Presenter ID: $this->id");
    }

    $this->day = new Date($o->day);
    $this->mtime = new Date($o->mtime);
    $this->submission_date = new Date($o->submission_date);
    $this->ownership = SkillOwnership::tryFrom($o->ownership) ?? SkillOwnership::admin;
    $this->published = SkillPublishedState::tryFrom($o->published) ?? SkillPublishedState::unpublished;
    $this->other_presenter_ids = json_decode($o->other_presenter_ids) ?? [];
    $this->av = $o->av;
    $this->length_info = $o->length_info;
    $this->location = $o->location;
    $this->presenter_id = $o->presenter_id;
    $this->archive_state = $o->archive_state ?? '';
    $this->audience = $o->audience ?? '';
    $this->category = $o->category ?? '';
    $this->comments = $o->comments ?? '';
    $this->copresenter_info = $o->copresenter_info;
    $this->description = $o->description;
    $this->equipment_info = $o->equipment_info;
    $this->event = $o->event;
    $this->requirements_info = $o->requirements_info;
    $this->time_slot = $o->time_slot;
    $this->title = $o->title;
    $this->track = $o->track;
    $this->type = $o->type;
  }

  private function toSqlObject(): object
  {
    $o = new \stdClass();

    $o->day = $this->day->toSql();
    $o->mtime = $this->mtime->toSql();
    $o->submission_date = $this->submission_date->toSql();
    $o->ownership = $o->ownership->value;
    $o->published = $this->published->value;
    $o->other_presenter_ids = $this->other_presenter_ids;
    $o->av = $this->av;
    $o->length_info = $this->length_info;
    $o->location = $this->location;
    $o->presenter_id = $this->presenter_id;
    $o->archive_state = $this->archive_state;
    $o->audience = $this->audience;
    $o->category = $this->category;
    $o->comments = $this->comments;
    $o->copresenter_info = $this->copresenter_info;
    $o->description = $this->description;
    $o->equipment_info = $this->equipment_info;
    $o->event = $this->event;
    $o->requirements_info = $this->requirements_info;
    $o->time_slot = $this->time_slot;
    $o->title = $this->title;
    $o->track = $this->track;
    $o->type = $this->type;

    return $o;
  }

  public function save(): int
  {
    $data = self::toSqlObject();
    if ($this->id) {
      $result = $this->db->updateObject(self::SKILLS_TABLE, $data, 'id');
      if (!$result) {
        throw new \Exception('Error during Skills update');
      }
    } else {
      $result = $this->db->insertObject(self::SKILLS_TABLE, $data, 'id');
      if (!$result) {
        throw new \Exception('Error during Skills insert');
      }
      $this->id = $data->id;
    }

    return $this->id;
  }
}
