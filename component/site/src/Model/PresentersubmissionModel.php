<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\DbBlob;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Skills;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\Router\Route;

/**
 * Get a single presenter submission from an authenticated user
 */
class PresentersubmissionModel extends AdminModel
{
  public function __construct()
  {
    parent::__construct();
    Helpers::sessionSet('skills.submission.tab', 'Biography');
  }

  public function getForm($data = [], $loadData = true)
  {
    // Get the form.
    $form = $this->loadForm('com_claw.presentersubmission', 'presentersubmission', array('control' => 'jform', 'load_data' => $loadData));

    if (empty($form)) {
      return false;
    }

    return $form;
  }

  /**
   * Given the ID of a record, attempt to copy it to the current
   * event while setting the old record to archived.
   * @param int $id 
   * @return int|false 
   * @throws DatabaseNotFoundException 
   */
  public function duplicate(int $id)
  {
    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $db = $this->getDatabase();
    $uid = $app->getIdentity()->id;

    $query = $db->getQuery(true);
    $query->select('*')
      ->from('#__claw_presenters')
      ->where('id = :id')
      ->bind(':id', $id);

    $db->setQuery($query);
    $record = $db->loadObject();

    // Is this user the owner of the record?
    if (is_null($record) || $record->uid != $uid) {
      $app->enqueueMessage('Permission denied.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $skillRoute = Route::_('index.php?option=com_claw&view=skillssubmissions');
      $app->redirect($skillRoute);
    }

    $success = false;
    $currentAlias = Aliases::current(true);

    if ($record->event != $currentAlias) {
      $record->id = 0;
      $record->event = $currentAlias;
      $record->published = 3;
      $record->submission_date = date("Y-m-d");
      $record->archive_state = '';
      $record->arrival = '';
      $record->copresenter = 0;
      $record->copresenting = '';
      $record->comments = '';
      $record->mtime = Helpers::mtime();

      $db->insertObject('#__claw_presenters', $record, 'id');

      // Set the old record to archived with reference to new ID
      $query = $db->getQuery(true);
      $query->update('#__claw_presenters')
        ->set('archive_state = :idx')
        ->where('id = :id')
        ->bind(':id', $id)
        ->bind(':idx', $record->id);
      $db->setQuery($query);
      $db->execute();

      $success = true;
    }

    if ($success) {
      $app->enqueueMessage('Biography copied successfully.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_INFO);
    } else {
      $app->enqueueMessage('Biography copy failed.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
    }

    $skillRoute = Route::_('index.php?option=com_claw&view=skillssubmissions');
    $app->redirect($skillRoute);
  }

  protected function loadFormData()
  {
    $mergeData = null;
    $submittedFormData = Helpers::sessionGet('formdata');
    if ($submittedFormData) {
      $mergeData = json_decode($submittedFormData, true);
    }

    // Check if a record for this presenter exists
    $app = Factory::getApplication();
    $uid = $app->getIdentity()->id;

    $skills = new Skills($this->getDatabase());
    $bios = $skills->GetPresenterBios($uid);

    $id = 0;
    $mtime = 0;

    foreach ($bios as $bio) {
      if ($bio->event == Aliases::current(true)) {
        $id = $bio->id;
        break;
      }

      if ($bio->mtime > $mtime) {
        $id = $bio->id;
        $mtime = $bio->mtime;
      }
    }

    if ($id) {
      $this->setState($this->getName() . '.id', $id);
    }

    $data = $this->getItem();

    if ($mergeData) {
      foreach ($mergeData as $key => $value) {
        $data->$key = $value;
      }
    }

    return $data;
  }

  public function getTable($name = '', $prefix = '', $options = array())
  {
    $name = 'Presenters';
    $prefix = 'Table';

    if ($table = $this->_createTable($name, $prefix, $options)) {
      return $table;
    }

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }

  public function getPresenterImagePath(int $pid, string $eventConfigAlias): string
  {
    $itemIds = [$pid];
    $config = new Config($eventConfigAlias);
    $presentersDir = $config->getConfigText(ConfigFieldNames::CONFIG_IMAGES, 'presenters') ?? '/images/skills/presenters/cache';

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
    );

    return $filenames[$pid] ?? '';
  }
}
