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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Skills;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\EventInfo;
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
      ->from('#__claw_presenters')
      ->where('id = :id')
      ->bind(':id', $id);

    $db->setQuery($query);
    $record = $db->loadObject();

    // Is this user the owner of the record?
    if ( $record == null || $record->uid != $uid ) {
      $app->enqueueMessage('Permission denied.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $skillRoute = Route::_('index.php?option=com_claw&view=skillssubmissions');
      $app->redirect($skillRoute);
    }

    $success = false;

    if ( $record->event != Aliases::current() ) {
      $record->id = 0;
      $record->event = Aliases::current();
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

    if ( $success ) {
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

    foreach ( $bios AS $bio ) {
      if ( $bio->event == Aliases::current(true) ) {
        $id = $bio->id;
        break;
      }

      if ( $bio->mtime > $mtime ) {
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

    Helpers::sessionSet('photo', '');
    if ($data->photo) {
      Helpers::sessionSet('photo', $data->photo);
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

  public function GetEventInfo(): \ClawCorpLib\Lib\EventInfo
  {
    return new EventInfo(Aliases::current(true));
  }
}
