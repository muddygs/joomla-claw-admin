<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Skills;

use ClawCorpLib\Enums\ConfigFieldNames;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use ClawCorpLib\Enums\SkillOwnership;
use ClawCorpLib\Enums\SkillPublishedState;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\DbBlob;
use ClawCorpLib\Helpers\Mailer;
use ClawCorpLib\Iterators\PresenterArray;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;

\defined('JPATH_PLATFORM') or die;

/** @package ClawCorpLib\Skills */
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
    public int $id = 0,
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');

    if ($id < 0) {
      throw new \InvalidArgumentException("Presenter ID must be 0 (for new) or a valid database row id.");
    }

    if ($this->id) {
      self::fromSqlRow();
    }
  }

  public static function get(int $id): Presenter
  {
    return new Presenter($id);
  }

  public function toSimpleObject(): object
  {
    $result = $this->toSqlObject();
    $result->arrival = $this->arrival;
    return $result;
  }

  /**
   * Checks if a user owns a presenter
   * @param int $presenter_id
   * @param int $uid
   * @return bool True if the presenter is owned by the user
   */
  public static function isOwner(int $presenter_id, int $uid): bool
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');
    $owner = SkillOwnership::user->value;

    $query = $db->getQuery(true);
    $query->select(['id'])
      ->from(Presenter::PRESENTERS_TABLE)
      ->where('id = :id')
      ->where('uid = :uid')
      ->where('ownership = :ownership')
      ->bind(':id', $presenter_id)
      ->bind(':uid', $uid)
      ->bind(':ownership', $owner);
    $db->setQuery($query);
    if (is_null($db->loadResult())) {
      return false;
    }

    return true;
  }

  /**
   * Retrieve the first presenter associated with the event; should be only one
   **/
  public static function getByUid(EventInfo $eventInfo, int $uid, SkillOwnership $ownership): ?Presenter
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');
    $event = $eventInfo->alias;
    $owner = $ownership->value;

    $query = $db->getQuery(true);
    $query->select(['id'])
      ->from(Presenter::PRESENTERS_TABLE)
      ->where('event = :event')
      ->where('uid = :uid')
      ->where('ownership = :owner')
      ->bind(':event', $event)
      ->bind(':owner', $owner)
      ->bind(':uid', $uid);
    $db->setQuery($query);
    if (is_null($id = $db->loadResult())) {
      return null;
    }

    return new Presenter($id);
  }

  public static function getAllByUid(int $uid): PresenterArray
  {
    $result = new PresenterArray();

    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);
    $query->select(['id'])
      ->from(Presenter::PRESENTERS_TABLE)
      ->where('uid = :uid')
      ->bind(':uid', $uid);
    $db->setQuery($query);
    if (is_null($ids = $db->loadColumn())) {
      return $result;
    }

    foreach ($ids as $id) {
      $presenter = new Presenter($id);
      $result[$id] = $presenter;
    }

    return $result;
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
    $this->arrival = json_decode($o->arrival) ?? [];
    $this->copresenter = $o->copresenter;
    $this->copresenting = $o->copresenting;
    $this->comments = $o->comments;
    $this->bio = $o->bio;

    #list($this->image, $this->image_preview) = self::loadImageBlobs();

    $this->submission_date = new Date($o->submission_date);
    $this->archive_state = $o->archive_state ?? '';
    $this->mtime = new Date($o->mtime);
  }

  public function loadImageBlobs()
  {
    list($this->image, $this->image_preview) = $this->getImageBlobs();
  }

  /** Writes the preview image to the web space. The main image is typically
   * not used but is currently kept in the database for reference
   */
  public function writePreviewImageBlobs(): string
  {
    $config = new Config($this->event);
    $presentersDir = $config->getConfigText(ConfigFieldNames::CONFIG_IMAGES, 'presenters', '/images/skills/presenters');

    // Insert property for cached presenter preview image
    $cache = new DbBlob(
      db: $this->db,
      cacheDir: JPATH_ROOT . $presentersDir,
      prefix: 'web_',
      extension: 'jpg'
    );

    $filenames = $cache->toFile(
      tableName: Presenter::PRESENTERS_TABLE,
      rowIds: [$this->id],
      key: 'image_preview',
    );

    return $filenames[$this->id];
  }

  private function getImageBlobs(): array
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
    if ($this->id > 0) {
      list($image, $image_preview) = self::getImageBlobs();
    }

    $o = new \stdClass();

    $o->id = $this->id;
    $o->ownership = $this->ownership->value;
    $o->uid = $this->uid;
    $o->published = $this->published->value;
    $o->name = $this->name;
    $o->legal_name = $this->legal_name;
    $o->event = $this->event;
    $o->social_media = $this->social_media;
    $o->email = $this->email;
    $o->phone = $this->phone;
    $o->arrival = json_encode($this->arrival);
    $o->copresenter = $this->copresenter;
    $o->copresenting = $this->copresenting;
    $o->comments = $this->comments;
    $o->bio = $this->bio;
    $o->image = is_null($this->image) ? $image : $this->image;
    $o->image_preview = is_null($this->image_preview) ? $image_preview : $this->image_preview;

    $o->submission_date = $this->submission_date->toSql();
    $o->archive_state = $this->archive_state;
    $o->mtime = (new Date())->toSql();

    return $o;
  }

  /**
   * Given the ID of a record, attempt to copy it to the current
   * event while setting the old record to archived.
   * @param EventInfo $eventInfo Event to copy to
   */
  public function migrate(EventInfo $eventInfo): Presenter
  {
    if ($this->event == $eventInfo->alias) {
      throw new \Exception('Biography cannot be copied to the same event.');
    }

    # Too lazy to create a deep copy method
    $newPresenter = Presenter::get($this->id);
    $newPresenter->loadImageBlobs();

    $success = true;

    $newPresenter->id = 0;
    $newPresenter->event = $eventInfo->alias;
    $newPresenter->published = SkillPublishedState::new;
    $newPresenter->submission_date = new Date();
    $newPresenter->archive_state = '';
    $newPresenter->arrival = [];
    $newPresenter->copresenter = 0;
    $newPresenter->copresenting = '';
    $newPresenter->comments = '';

    try {
      $newId = $newPresenter->save();
    } catch (\Exception) {
      throw new \Exception('Biography copy failed.');
    }

    if ($success) {
      $this->archive_state = $newId;
      try {
        $this->save();
      } catch (\Exception) {
        throw new \Exception('Biography copy was successful, but the older biography could not be archived.');
      }
    }

    return $newPresenter;
  }

  public function emailResults(bool $new)
  {
    $params = ComponentHelper::getParams('com_claw');
    $notificationEmail = $params->get('se_notification_email', 'education@clawinfo.org');
    $notificationMessage = $params->get('se_email_presenter_intro', '');

    if ($notificationMessage == '') {
      $notificationMessage = <<< HTML
    <h1>CLAW Skills &amp; Education Bio Submission Record</h1>
    <p>Thank you for your submission. Your next step is to submit your classes. If you have previous classes, you
      can copy them from previous years by editing and resaving them for the next upcoming event.</p>
    <p>Go to <a href="https://www.clawinfo.org/index.php?option=com_claw&view=skillssubmissions">Submission Launcher</a> to proceed.</p> 
      <p>If you have any questions, please contact us at <a href="mailto:[email]">CLAW S&amp;E Program Manager</a>.</p>
  HTML;
    }

    $notificationMessage = str_replace('[email]', $notificationEmail, $notificationMessage);

    // prepare image_preview as attachment
    $info = new EventInfo($this->event);
    $data['event'] = $info->description;

    $image_preview_path = $this->writePreviewImageBlobs() ?? '';

    $subject = $new ? '[New] ' : '[Updated] ';
    $subject .= $info->description . ' Presenter Application - ';
    $subject .= $data['name'];

    $m = new Mailer(
      tomail: [$this->email],
      toname: [$this->name],
      bcc: [$notificationEmail],
      fromname: 'CLAW Skills and Education',
      frommail: $notificationEmail,
      subject: $subject,
      attachments: [$image_preview_path ? '/' . $image_preview_path : ''],
    );

    $m->appendToMessage($notificationMessage);
    $m->appendToMessage('<p>Application Details:</p>');
    $m->appendToMessage($m->arrayToTable(
      (array)$this->toSimpleObject(),
      ['ownership', 'photo', 'image', 'image_preview', 'uid', 'email', 'id', 'mtime', 'tags', 'archive_state', 'published']
    ));

    $m->send();
  }

  public function viewRoute(): string
  {
    return Route::_('index.php?option=com_claw&view=skillspresenter&id=' . $this->id);
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
