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
use ClawCorpLib\Helpers\Locations;
use ClawCorpLib\Helpers\Mailer;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Component\ComponentHelper;

\defined('JPATH_PLATFORM') or die;

final class Skill
{
  const SKILLS_TABLE = '#__claw_skills';

  private DatabaseDriver $db;

  public ?Date $day;
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
    public int $id = 0,
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');

    if ($id < 0) {
      throw new \InvalidArgumentException("Skill ID must be 0 (for new) or a valid database row id.");
    }

    if ($this->id) {
      self::fromSqlRow();
    } else {
      $this->submission_date = new Date();
      $this->archive_state = '';
    }
  }

  public static function get(int $id): Skill
  {
    return new Skill($id);
  }

  public function toSimpleObject(): object
  {
    return $this->toSqlObject();
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

    $this->fromSqlObject($o);
  }

  public function fromSqlObject(object $o)
  {
    if ((int)$o->presenter_id < 1) {
      throw new \Exception("Presenter ID not defined");
    }

    $this->id = $o->id ?? 0;
    $this->day = is_null($o->day) || str_starts_with($this->db->getNullDate(), $o->day) ? null : new Date($o->day);
    $this->mtime = new Date($o->mtime);
    $this->submission_date = new Date($o->submission_date);
    $this->ownership = SkillOwnership::tryFrom($o->ownership) ?? SkillOwnership::user;
    $this->published = SkillPublishedState::tryFrom($o->published) ?? SkillPublishedState::new;
    $this->other_presenter_ids = json_decode($o->other_presenter_ids ?? '') ?? [];
    $this->av = $o->av ?? 0;
    $this->length_info = $o->length_info ?? 60;
    $this->location = $o->location ?? Locations::BLANK_LOCATION;
    $this->presenter_id = $o->presenter_id;
    $this->archive_state = $o->archive_state ?? '';
    $this->audience = $o->audience ?? '';
    $this->category = $o->category ?? '';
    $this->comments = $o->comments ?? '';
    $this->copresenter_info = $o->copresenter_info ?? '';
    $this->description = $o->description ?? '';
    $this->equipment_info = $o->equipment_info ?? '';
    $this->event = $o->event;
    $this->requirements_info = $o->requirements_info;
    $this->time_slot = $o->time_slot ?? '';
    $this->title = $o->title ?? '';
    $this->track = $o->track ?? '';
    $this->type = $o->type ?? '';
  }

  private function toSqlObject(): object
  {
    $o = new \stdClass();

    $o->id = $this->id;
    $o->day = is_null($this->day) ? $this->db->getNullDate() : $this->day->toSql();
    $o->mtime = (new Date())->toSql();
    $o->submission_date = $this->submission_date->toSql();
    $o->ownership = $this->ownership->value;
    $o->published = $this->published->value;
    $o->other_presenter_ids = json_encode($this->other_presenter_ids);
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

  /**
   * Given the ID of a record, attempt to copy it to the current
   * event while setting the old record to archived.
   * @param EventInfo $eventInfo Event to copy to
   */
  public function migrate(EventInfo $eventInfo, Presenter $presenter): Skill
  {
    if ($this->event == $eventInfo->alias) {
      throw new \Exception('Class description cannot be copied to the same event.');
    }

    # Too lazy to create a deep copy method
    $newSkill = Skill::get($this->id);

    $success = true;

    $newSkill->id = 0;
    $newSkill->event = $eventInfo->alias;
    $newSkill->published = SkillPublishedState::new;
    $newSkill->submission_date = new Date();
    $newSkill->archive_state = '';
    $newSkill->comments = '';
    $newSkill->day = null;
    $newSkill->ownership = SkillOwnership::user;
    $newSkill->presenter_id = $presenter->id;
    $newSkill->other_presenter_ids = [];
    $newSkill->location = Locations::BLANK_LOCATION;

    try {
      $newId = $newSkill->save();
    } catch (\Exception) {
      throw new \Exception('Class copy failed.');
    }

    if ($success) {
      $this->archive_state = $newId;
      try {
        $this->save();
      } catch (\Exception) {
        throw new \Exception('Class copy was successful, but the old class could not be archived.');
      }
    }

    return $newSkill;
  }

  public function emailResults(EventInfo $eventInfo, Presenter $presenter, bool $new = false)
  {
    $params = ComponentHelper::getParams('com_claw');
    $notificationEmail = $params->get('se_notification_email', 'education@clawinfo.org');
    $notificationMessage = $params->get('se_email_skill_intro', '');
    if ($notificationMessage == '') {
      $notificationMessage = <<< HTML
      <h1>CLAW Skills &amp; Education Class Submission Record</h1>
      <p>Thank you for your interest in presenting at the CLAW/Leather Thanksgiving Skills and Education Program.</p>
      <p>Your class submission has been received and will be reviewed by the CLAW Education Committee. You will be notified of the status of your application by email.</p>
      <p>If you have any questions, please contact us at <a href="mailto:[email]">CLAW S&amp;E Program Manager</a>.</p>
HTML;
    }

    $notificationMessage = str_replace('[email]', $notificationEmail, $notificationMessage);

    $subject = $new ? '[New] ' : '[Updated] ';
    $subject .= $eventInfo->description . ' Class Submission - ';
    $subject .= $presenter->name;

    $m = new Mailer(
      tomail: [$presenter->email],
      toname: [$presenter->name],
      bcc: [$notificationEmail],
      fromname: 'CLAW Skills and Education',
      frommail: $notificationEmail,
      subject: $subject,
    );

    $m->appendToMessage($notificationEmail);
    $m->appendToMessage('<p>Class Submission Details:</p>');

    $m->appendToMessage($m->arrayToTable(
      (array)$this->toSimpleObject(),
      ['id', 'uid', 'published', 'location', 'mtime', 'day', 'presenter_id', 'ownership', 'other_presenter_ids', 'archive_state', 'audience', 'category', 'event']
    ));

    $m->send();
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

  /** serialize/deserialize **/

  public function __serialize(): array
  {
    // Probably don't need this, but versioning is a good pattern
    $result = [
      '__v' => 1,
    ];

    $sqlArray = (array)$this->toSqlObject();

    // Handle specific serialization cases for enums and days

    $result = array_merge($result, $sqlArray);

    return $result;
  }

  public function __unserialize(array $data): void
  {
    // Initialize db
    $this->db = Factory::getContainer()->get('DatabaseDriver');

    $v = $data['__v'] ?? 1;

    $this->fromSqlObject((object)$data);
  }
}
