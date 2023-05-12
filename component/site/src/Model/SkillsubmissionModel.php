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
   * event
   * @param int $id 
   * @return int|false 
   * @throws DatabaseNotFoundException 
   */
  public function duplicate(int $id)
  {
    /** @var \Joomla\CMS\Application\CMSApplicationInterface */
    $app = Factory::getApplication();
    $db = $this->getDatabase();
    $uid = $app->getIdentity()->id;

    $query = $db->getQuery(true);
    $query->select('*')
      ->from('#__claw_skills')
      ->where('id = :id')
      ->bind(':id', $id);

    $db->setQuery($query);
    $record = $db->loadAssoc();

    $success = false;

    if ( $record != null ) {
      if ( $record['owner'] == $uid && $record['event'] != Aliases::current ) {
        $record['id'] = 0;
        $record['event'] = Aliases::current;
        $record['published'] = 3;
        $record['day'] = $db->getNullDate();
        $record['mtime'] = Helpers::mtime();
        $record['submission_date'] = date("Y-m-d");
        $record['presenters'] = '';
        $record['location'] = 2147483647;
        $record['handout_id'] = 0;

        $query = $db->getQuery(true);
        $query->insert($db->quoteName('#__claw_skills'))
          ->columns(array_keys($record))
          ->values(implode(',', $db->q(array_values($record))));

        $db->setQuery($query);
        $db->execute();
    
        $success = true;
      }
    }

    if ( $success ) {
      $app->enqueueMessage('Class copied successfully.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_INFO);
    } else {
      $app->enqueueMessage('Class copied failed.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
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
