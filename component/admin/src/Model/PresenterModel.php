<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\SkillOwnership;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Enums\SkillPublishedState;
use ClawCorpLib\Skills\Presenter;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\DatabaseInterface;

/**
 * Methods to edit a single record
 */
class PresenterModel extends AdminModel
{
  protected $text_prefix = 'COM_CLAW';
  private ?array $aclGroupIds = [];
  private int $publishedAcl = 0;

  public function delete(&$pks)
  {
    parent::delete($pks);
  }

  public function validate($form, $data, $group = null)
  {
    if ($data['ownership'] == SkillOwnership::user->value && empty($data['uid'])) {
      $app = Factory::getApplication();
      $app->enqueueMessage('Record ownership not set', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    return parent::validate($form, $data, $group);
  }

  public function save($data)
  {
    $app = Factory::getApplication();

    $data['mtime'] = Helpers::mtime();
    $currentEventAlias = Aliases::current(true);

    $new = 0 == $data['id'];

    // New record handling
    if ($new) {
      if ($data['ownership'] == SkillOwnership::user->value && $this->checkExists($data['uid'], $currentEventAlias)) {
        $app->enqueueMessage(
          'Record for this presenter already exists for this event.',
          \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR
        );
        return false;
      }

      $data['submission_date'] = date("Y-m-d");
      $presenter = new Presenter();
      $presenter->uid = $data['uid'];
      $presenter->submission_date = new Date();
      $presenter->archive_state = '';
    } else {
      $presenter = new Presenter($data['id']);
      $presenter->loadImageBlobs();
    }

    $presenter->arrival = $data['arrival'] ?? [];
    $presenter->event = $data['event'] ?? $currentEventAlias;
    $presenter->published = SkillPublishedState::tryFrom($data['published']) ?? SkillPublishedState::unpublished;
    $presenter->name = $data['name'];
    $presenter->legal_name = $data['legal_name'];
    $presenter->social_media = $data['social_media'];
    $presenter->email = $data['email'];
    $presenter->phone = $data['phone'];
    $presenter->copresenter = $data['copresenter'] ?? 0;
    $presenter->copresenting = $data['copresenting'];
    $presenter->comments = $data['comments'];
    $presenter->bio = $data['bio'];

    $success = $this->handlePhotoUpload($app->input, $presenter);

    if (!$success) {
      $app->enqueueMessage(
        'An error occurred during image upload',
        \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR
      );
      return false;
    }

    $presenter->ownership = SkillOwnership::tryFrom($data['ownership']) ?? SkillOwnership::user;

    // Need to set to something that's valid, so let's use the admin's uid
    // TODO: there is a potential problem where a record is switched from user
    // TODO: ownership to admin, is already published, and the ACL record permits registration
    if (
      SkillOwnership::user == $presenter->ownership
      && $app->isClient('administrator')
      && $presenter->event == $currentEventAlias
      && $data['uid'] > 0
    ) {
      $this->publishedAcl = $this->loadEducatorAclId($currentEventAlias);

      if (!$this->publishedAcl) {
        $app->enqueueMessage('No registration ACL set in configuration.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
        return false;
      }

      $this->aclGroupIds = Helpers::AclToGroups($this->publishedAcl);
      if (is_null($this->aclGroupIds) || count($this->aclGroupIds) != 1) {
        $app->enqueueMessage('Package ACL must contain only one group: ' . $this->publishedAcl, \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
        return false;
      }
    }

    try {
      $presenter->save();
      $this->setState($this->getName() . '.id', $presenter->id);
    } catch (\Exception $e) {
      $app->enqueueMessage($e->getMessage(), \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    // Email if coming from the front end site
    if ($app->isClient('site')) {
      $presenter->emailResults($new);
    }

    if ($this->publishedAcl != 0 && $app->isClient('administrator')) {
      switch ($data['published']) {
        case (EbPublishedState::published->value):
          $this->ensureAclMembership($presenter->uid);
          break;
        default:
          $this->removeAclMembership($presenter->uid);
          break;
      }
    }

    return true;
  }

  private function handlePhotoUpload(\Joomla\Input\Input $input, Presenter $presenter): bool
  {
    $files = $input->files->get('jform');
    $photo_upload = $files['photo_upload'];

    if (!array_key_exists('size', $photo_upload) || $photo_upload['size'] < 1) return true;

    $tmp_name = $photo_upload['tmp_name'];
    $error = $photo_upload['error'];

    if (0 == $error) {
      // Copy original out of tmp

      $path = implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, 'tmp']);
      $orig = basename($tmp_name) . '.jpg';

      if (!Helpers::ProcessImageUpload(
        source: $tmp_name,
        thumbnail: $path . '/thumb_' . $orig,
        copyto: $path . '/orig_' . $orig,
        deleteSource: true,
        origsize: 1024,
      )) {
        Factory::getApplication()->enqueueMessage('Unable to process uploaded photo file.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
        return false;
      }

      // read blobs

      $presenter->image = file_get_contents($path . '/orig_' . $orig);
      $presenter->image_preview = file_get_contents($path . '/thumb_' . $orig);

      return true;
    }

    return false;
  }

  /**
   * Check if a user ID exists in an existing presenter record
   **/
  private function checkExists(int $uid, string $eventAlias): bool
  {
    $eventInfo = new EventInfo($eventAlias);
    return (bool)Presenter::getByUid($eventInfo, $uid, SkillOwnership::admin);
  }

  public function migrateToCurrentEvent(Table $table)
  {
    $table->id = 0;
    $table->event = Aliases::current(true);
    $table->published = 0;
    $table->ownership = SkillOwnership::admin->value;
    $table->uid = 0;
    $table->mtime = Helpers::mtime();
  }

  /**
   * Look up the event config and extract the ACL required for registration to
   * the Educator event
   */
  private function loadEducatorAclId(string $eventAlias): int
  {
    $eventConfig = new EventConfig($eventAlias);
    /** @var \ClawCorpLib\Lib\PackageInfo */
    try {
      $package = $eventConfig->getMainEventByPackageType(EventPackageTypes::educator);
    } catch (\Exception) {
      throw new \Exception('Educator package not configured.');
    }

    if ($package->eventId == 0) {
      throw new \Exception('Educator package not deployed.');
    }

    return $package->acl_id; // the ACL id
  }

  private function ensureAclMembership($uid)
  {
    $userFactory = Factory::getContainer()->get(UserFactoryInterface::class);
    $user = $userFactory->loadUserById($uid);
    $acl = $user->getAuthorisedViewLevels();

    if (!in_array($this->publishedAcl, $acl)) {
      $user->groups = array_merge($user->groups, $this->aclGroupIds);
      $user->save(updateOnly: true);
    }
  }

  private function removeAclMembership($uid)
  {
    $userFactory = Factory::getContainer()->get(UserFactoryInterface::class);
    $user = $userFactory->loadUserById($uid);
    $acl = $user->getAuthorisedViewLevels();

    if (in_array($this->publishedAcl, $acl)) {
      $user->groups = array_diff($user->groups, $this->aclGroupIds);
      $user->save();
    }
  }

  /**
   * Method to get the record form.
   *
   * @param   array    $data      Data for the form.
   * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
   *
   * @return  Form|boolean  A Form object on success, false on failure
   */
  public function getForm($data = array(), $loadData = true)
  {
    // Get the form.
    $form = $this->loadForm('com_claw.presenter', 'presenter', array('control' => 'jform', 'load_data' => $loadData));

    if (empty($form)) {
      return false;
    }

    return $form;
  }

  /**
   * Method to get the data that should be injected in the form.
   *
   * @return  mixed  The data for the form.
   */
  protected function loadFormData()
  {
    // Check the session for previously entered form data.
    /** @var \Joomla\CMS\Application\AdministratorApplication */
    $app = Factory::getApplication();
    $data = $app->getUserState('com_claw.edit.presenter.data', []);

    if (empty($data)) {
      $data = $this->getItem();
      $data->arrival = json_decode($data->arrival) ?? [];
    }

    return $data;
  }

  public function getDatabase(): DatabaseInterface
  {
    return parent::getDatabase();
  }

  /**
   * Method to get a table object, load it if necessary.
   *
   * @param   string  $name     The table name. Optional.
   * @param   string  $prefix   The class prefix. Optional.
   * @param   array   $options  Configuration array for model. Optional.
   *
   * @return  Table  A Table object
   *
   * @since   3.0
   * @throws  \Exception
   */
  public function getTable($name = '', $prefix = '', $options = [])
  {
    $name = 'Presenters';
    $prefix = 'Table';

    if ($table = $this->_createTable($name, $prefix, $options)) {
      return $table;
    }

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }
}
