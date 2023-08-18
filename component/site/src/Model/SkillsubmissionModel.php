<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Locations;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Database\Exception\DatabaseNotFoundException;

/**
 * Get a single presenter submission from an authenticated user
 */
class SkillsubmissionModel extends AdminModel
{
  public $db;

  public function __construct()
  {
    parent::__construct();
    $this->db = $this->getDatabase();
    Helpers::sessionSet('skills.submission.tab', 'Classes');
  }

  public function getForm($data = [], $loadData = true)
  {
    // Get the form.
    $form = $this->loadForm(
      'com_claw.skillsubmission',
      'skillsubmission',
      [
        'control' => 'jform',
        'load_data' => $loadData
      ]
    );

    if (empty($form)) {
      return false;
    }

    return $form;
  }

  /**
   * Given the ID of a record, attempt to copy it to the current
   * event while setting the old record to achived.
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
      ->from('#__claw_skills')
      ->where('id = :id')
      ->bind(':id', $id);

    $db->setQuery($query);
    $record = $db->loadObject();

    // Is this user the owner of the record?
    if ( $record == null || $record->owner != $uid ) {
      $app->enqueueMessage('Permission denied.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $skillRoute = Route::_('index.php?option=com_claw&view=skillssubmissions');
      $app->redirect($skillRoute);
    }

    $success = false;

    if ( $record->event != Aliases::current ) {
      $record->id = 0;
      $record->event = Aliases::current;
      $record->published = 3;
      $record->day = $db->getNullDate();
      $record->submission_date = date("Y-m-d");
      $record->presenters = '';
      $record->location = Locations::$blankLocation;
      $record->handout_id = 0;
      $record->archive_state = '';
      $record->mtime = Helpers::mtime();

      $db->insertObject('#__claw_skills', $record, 'id');

      // Set the old record to archived with reference to new ID
      $query = $db->getQuery(true);
      $query->update('#__claw_skills')
        ->set('archive_state = :idx')
        ->where('id = :id')
        ->bind(':id', $id)
        ->bind(':idx', $record->id);
      $db->setQuery($query);
      $db->execute();
      
  
      $success = true;
    }

    if ( $success ) {
      $app->enqueueMessage('Class copied successfully.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_INFO);
    } else {
      $app->enqueueMessage('Class copy failed.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
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

    $data = $this->getItem();

    if ($mergeData) {
      foreach ($mergeData as $key => $value) {
        $data->$key = $value;
      }
    }

    if ($data->photo) {
      Helpers::sessionSet('photo', $data->photo);
    }

    $data->length = $data->length_info ?? 60;

    return $data;
  }

  public function getTable($name = '', $prefix = '', $options = array())
  {
    $name = 'Skills';
    $prefix = 'Table';

    if ($table = $this->_createTable($name, $prefix, $options)) {
      return $table;
    }

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }

  public function GetEventInfo(): \ClawCorpLib\Lib\EventInfo
  {
    $events = new ClawEvents(Aliases::current);

    $info = $events->getClawEventInfo();
    return $info;
  }
}
