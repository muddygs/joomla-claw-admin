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

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\SkillOwnership;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\DbBlob;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Mailer;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Skills\Presenter;
use Joomla\CMS\Component\ComponentHelper;
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

    // Get the task
    $task = $app->input->get('task');
    if ($task == 'save2copy') {
      $data['event'] = $currentEventAlias;
    }

    $new = false;


    // New record handling
    if (0 == $data['id']) {
      if ($data['ownership'] == SkillOwnership::user->value && $this->checkExists($data['id'], $data['event'])) {
        $app->enqueueMessage(
          'Record for this presenter already exists for this event.',
          \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR
        );
        return false;
      }

      $data['submission_date'] = date("Y-m-d");
      $new = true;
    }

    // Handle checkboxes storage
    if (array_key_exists('arrival', $data)) $data['arrival'] = implode(',', $data['arrival']);
    if (array_key_exists('phone_info', $data)) $data['phone_info'] = implode(',', $data['phone_info']);

    $success = $this->handlePhotoUpload($app->input, $data);

    if (!$success) {
      $image_preview = Helpers::sessionGet('image_preview'); // from site model
      if ($image_preview && !$new) {
        $this->mergeImageBlobs($data);
      }
    }

    if (!array_key_exists('ownership', $data)) {
      $data['ownership'] = 1;
    }

    // Need to set to something that's valid, so let's use the admin's uid
    // TODO: there is a potential problem where a record is switched from user
    // TODO: ownership to admin, is already published, and the ACL record permits registration
    if (SkillOwnership::admin->value == $data['ownership'] && empty($data['uid'])) {
      $data['uid'] = 0;
    } else {
      if ($data['event'] == $currentEventAlias && $app->isClient('administrator') && $data['uid'] > 0) {
        $this->publishedAcl = $this->loadEducatorAclId($data['event']);

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
    }

    $result = parent::save($data);

    if ($result) {
      $id  = (int) $this->getState($this->getName() . '.id'); // set in save() method
      $data['id'] = $id;

      // Email if coming from the front end site
      if ($app->isClient('site') && array_key_exists('email', $data)) {
        $this->email(new: $new, data: $data);
      }

      if ($this->publishedAcl != 0 && $app->isClient('administrator')) {
        switch ($data['published']) {
          case (EbPublishedState::published->value):
            $this->ensureAclMembership($data['uid']);
            break;
          default:
            $this->removeAclMembership($data['uid']);
            break;
        }
      }
    }

    return $result;
  }

  private function handlePhotoUpload(\Joomla\Input\Input $input, array &$data): bool
  {
    $files = $input->files->get('jform');
    $tmp_name = $files['photo_upload']['tmp_name'];
    $error = $files['photo_upload']['error'];

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

      $data['photo'] = ''; //deprecated column
      $data['image'] = file_get_contents($path . '/orig_' . $orig);
      $data['image_preview'] = file_get_contents($path . '/thumb_' . $orig);

      return true;
    }

    return false;
  }

  private function mergeImageBlobs(&$data)
  {
    $db = $this->getDatabase();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(['image_preview', 'image']))
      ->from($db->quoteName('#__claw_presenters'))
      ->where($db->quoteName('id') . ' = ' . (int) $data['id']);

    $db->setQuery($query);
    $result = $db->loadObject();

    if ($result) {
      $data['image_preview'] = $result->image_preview;
      $data['image'] = $result->image;
    }
  }

  /**
   * Check if a user ID exists in an existing presenter record
   **/
  private function checkExists($id, $event): bool
  {
    // Load presenter by id
    $presenter = new Presenter($id);
    $uid = $presenter->uid;

    $db = $this->getDatabase();
    $query = $db->getQuery(true);
    $query->select($db->quoteName('id'))
      ->from($db->quoteName('#__claw_presenters'))
      ->where('uid = :uid')
      ->where('event = :event')
      ->bind(':uid', $uid)
      ->bind(':event', $event);

    $db->setQuery($query);
    return $db->loadResult() ?? false;
  }

  public function migrateToCurrentEvent(Table $table)
  {
    $table->id = 0;
    $table->event = Aliases::current(true);
    $table->published = 0;
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
    $package = $eventConfig->getMainEventByPackageType(EventPackageTypes::educator);
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

  private function email(bool $new, array $data)
  {
    $params = ComponentHelper::getParams('com_claw');
    $notificationEmail = $params->get('se_notification_email', 'education@clawinfo.org');

    // prepare image_preview as attachment
    $alias = Aliases::current();
    $info = new EventInfo($alias);
    $config = new Config($alias);
    $presentersDir = $config->getConfigText(ConfigFieldNames::CONFIG_IMAGES, 'presenters', '/images/skills/presenters');
    $data['event'] = $info->description;

    $itemIds = [$data['id']];
    $itemMinAges = [new \DateTime($this->presenter->mtime, new \DateTimeZone('UTC'))];

    // Insert property for cached presenter preview image
    $cache = new DbBlob(
      db: $this->getDatabase(),
      cacheDir: JPATH_ROOT . $presentersDir,
      prefix: 'web_',
      extension: 'jpg'
    );

    $filenames = $cache->toFile(
      tableName: '#__claw_presenters',
      rowIds: $itemIds,
      key: 'image_preview',
      minAges: $itemMinAges
    );

    $image_preview_path = $filenames[$data[$cache->key]] ?? '';

    $subject = $new ? '[New] ' : '[Updated] ';
    $subject .= $info->description . ' Presenter Application - ';
    $subject .= $data['name'];

    $m = new Mailer(
      tomail: [$data['email']],
      toname: [$data['name']],
      bcc: [$notificationEmail],
      fromname: 'CLAW Skills and Education',
      frommail: $notificationEmail,
      subject: $subject,
      attachments: [$image_preview_path ? '/' . $image_preview_path : ''],
    );

    $header = <<< HTML
    <h1>CLAW Skills &amp; Education Bio Submission Record</h1>
    <p>Thank you for your submission. Your next step is to submit your classes. If you have previous classes, you
      can copy them from previous years by editing and resaving them for the current CLAW/Leather Getaway event.</p>
    <p>Go to <a href="https://www.clawinfo.org/index.php?option=com_claw&view=skillssubmissions">Submission Launcher</a> to proceed.</p> 
HTML;

    $m->appendToMessage($header);
    $m->appendToMessage('<p>Application Details:</p>');
    $m->appendToMessage($m->arrayToTable($data, ['photo', 'image', 'image_preview', 'uid', 'email', 'id', 'mtime', 'tags', 'published']));

    $m->appendToMessage('<p>Questions? Please email <a href="mailto:' . $notificationEmail . '">Education Coordinator</a></p>');

    $m->send();
  }
}
