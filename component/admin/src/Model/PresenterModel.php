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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\DatabaseInterface;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class PresenterModel extends AdminModel
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   * @since  1.6
   */
  protected $text_prefix = 'COM_CLAW';

  public function delete(&$pks)
  {
    parent::delete($pks);
  }

  public function validate($form, $data, $group = null)
  {
    // Handle readonly account data 
    if (array_key_exists('uid_readonly_uid', $data) && $data['uid_readonly_uid'] != 0) {
      $data['uid'] = $data['uid_readonly_uid'];
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
    if ($data['id'] == 0) {
      $data['submission_date'] = date("Y-m-d");

      if ($this->checkExists($data['uid'], $data['event'])) {
        $app->enqueueMessage(
          'Record for this presenter already exists for this event.',
          \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR
        );
        return false;
      }

      $new = true;
    }

    // Handle checkboxes storage
    if (array_key_exists('arrival', $data)) $data['arrival'] = implode(',', $data['arrival']);
    if (array_key_exists('phone_info', $data)) $data['phone_info'] = implode(',', $data['phone_info']);

    $input = $app->input;

    $success = $this->handlePhotoUpload($input, $data);

    if (!$success) {
      $image_preview = Helpers::sessionGet('image_preview'); // from site model
      if ($image_preview && !$new) {
        $this->mergeImageBlobs($data);
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

      if ($data['event'] == $currentEventAlias && $app->isClient('administrator') && $data['uid'] != 0) {
        $publishedAcl = $this->loadEducatorAclId($data['event']);

        if (!$publishedAcl) {
          $app->enqueueMessage('No approval group set in configuration.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
          return false;
        }

        switch ($data['published']) {
          case (EbPublishedState::published->value):
            $this->ensureAclMembership($data['uid'], $publishedAcl);
            break;
          default:
            $this->removeAclMembership($data['uid'], $publishedAcl);
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

  private function checkExists($uid, $event): bool
  {
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

  public function migrateToCurrentEvent(Table $table, bool $copy = true)
  {
    if ($copy) {
      $table->id = 0;
    }

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
    $package = $eventConfig->getMainEventByPackageType(EventPackageTypes::educator);
    return $package->group_id; // the ACL id
  }

  private function ensureAclMembership($uid, $targetAcl)
  {
    $userFactory = Factory::getContainer()->get(UserFactoryInterface::class);
    $user = $userFactory->loadUserById($uid);
    $acl = $user->getAuthorisedViewLevels();

    return;

    if (!in_array($targetAcl, $acl)) {
      $user->groups = array_merge($acl, [$targetAcl]);
      $user->save(updateOnly: true);
    }
  }

  private function removeAclMembership($uid, $targetAcl)
  {
    $userFactory = Factory::getContainer()->get(UserFactoryInterface::class);
    $user = $userFactory->loadUserById($uid);
    $groups = $user->getAuthorisedViewLevels();

    return;

    if (in_array($targetAcl, $groups)) {
      $user->groups = array_diff($groups, [$targetAcl]);
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
   *
   * @since   1.6
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
   *
   * @since   1.6
   */
  protected function loadFormData()
  {
    // Check the session for previously entered form data.
    /** @var \Joomla\CMS\Application\AdministratorApplication */
    $app = Factory::getApplication();
    $data = $app->getUserState('com_claw.edit.presenter.data', []);

    if (empty($data)) {
      $data = $this->getItem();
    } else {
      // Handle readonly account data 
      if (!array_key_exists('uid', $data) && $data['uid_readonly_uid'] ?? 0 != 0) {
        $data['uid'] = $data['uid_readonly_uid'];
      }
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
    $presentersDir = $config->getConfigText(ConfigFieldNames::CONFIG_IMAGES, 'presenters') ?? '/images/skills/presenters/cache';
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
