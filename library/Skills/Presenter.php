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

final class Presenter
{
  const PRESENTERS_TABLE = '#__claw_presenters';

  private DatabaseDriver $db;

  // Not populated unless needed
  public ?string $image = null;
  public ?string $image_preview = null;

  public Date $mtime;
  public Date $submission_date;
  public SkillOwnership $ownership;
  public SkillPublishedState $published;
  public array $arrival;
  public array $phone_info;
  public int $copresenter;
  public int $uid;
  public string $archive_state;
  public string $bio;
  public string $comments;
  public string $copresenting;
  public string $email;
  public string $event;
  public string $legal_name;
  public string $name;
  public string $phone;
  public string $social_media;

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
      ->from(self::PRESENTERS_TABLE)
      ->where('id = :id')
      ->bind(':id', $this->id);
    $this->db->setQuery($query);
    if (is_null($o = $this->db->loadObject())) {
      throw new \InvalidArgumentException("Invalid Presenter ID: $this->id");
    }

    $this->ownership = SkillOwnership::tryFrom($o->ownership) ?? SkillOwnership::admin;
    $this->uid = $o->uid;
    $this->published = SkillPublishedState::tryFrom($o->published) ?? SkillPublishedState::unpublished;
    $this->name = $o->name;
    $this->legal_name = $o->legal_name;
    $this->event = $o->event;
    $this->social_media = $o->social_media;
    $this->email = $o->email;
    $this->phone = $o->phone;
    $this->phone_info = explode(',', $o->phone_info);
    $this->arrival = explode(',', $o->arrival);
    $this->copresenter = $o->copresenter;
    $this->copresenting = $o->copresenting;
    $this->comments = $o->comments;
    $this->bio = $o->bio;

    #list($this->image, $this->image_preview) = self::loadImageBlobs();

    $this->submission_date = new Date($o->submission_date);
    $this->archive_state = $o->archive_state ?? '';
    $this->mtime = new Date($o->mtime);
  }

  private function loadImageBlobs(): array
  {
    $query = $this->db->getQuery(true);
    $query->select(['image', 'image_preview'])
      ->from(self::PRESENTERS_TABLE)
      ->where('id = :id')
      ->bind(':id', $this->id);
    $this->db->setQuery($query);
    if (is_null($o = $this->db->loadObject())) {
      throw new \InvalidArgumentException("Invalid Presenter ID: $this->id");
    }

    return [$o->image, $o->image_preview];
  }

  private function toSqlObject(): object
  {
    list($image, $image_preview) = self::loadImageBlobs();

    $o = new \stdClass();

    $o->ownership = $this->ownership->value;
    $o->uid = $this->uid;
    $o->published = $this->published->value;
    $o->name = $this->name;
    $o->legal_name = $this->legal_name;
    $o->event = $this->event;
    $o->social_media = $this->social_media;
    $o->email = $this->email;
    $o->phone = $this->phone;
    $o->phone_info = explode(',', $o->phone_info);
    $o->arrival = explode(',', $o->arrival);
    $o->copresenter = $this->copresenter;
    $o->copresenting = $this->copresenting;
    $o->comments = $this->comments;
    $o->bio = $this->bio;
    if (is_null($this->image)) $o->image = $image;
    if (is_null($this->image_preview)) $o->image_preview = $image_preview;


    $o->submission_date = $this->submission_date->toSql();
    $o->archive_state = $this->archive_state;
    $o->mtime = $this->mtime->toSql();

    return $o;
  }

  public function save(): int
  {
    $data = self::toSqlObject();
    if ($this->id) {
      $result = $this->db->updateObject(self::PRESENTERS_TABLE, $data, 'id');
      if (!$result) {
        throw new \Exception('Error during Presenter update');
      }
    } else {
      $result = $this->db->insertObject(self::PRESENTERS_TABLE, $data, 'id');
      if (!$result) {
        throw new \Exception('Error during Presenter insert');
      }
      $this->id = $data->id;
    }

    return $this->id;
  }
}
