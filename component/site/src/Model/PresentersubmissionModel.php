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

/**
 * Get a single presenter submission from an authenticated user
 */
class PresentersubmissionModel extends AdminModel
{
  public function getForm($data = [], $loadData = true)
  {
    // Get the form.
    $form = $this->loadForm('com_claw.presentersubmission', 'presentersubmission', array('control' => 'jform', 'load_data' => $loadData));

    if (empty($form)) {
      return false;
    }

    return $form;
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

    $bios = Skills::GetPresenterBios($this->getDatabase(), $uid);

    $id = 0;
    $mtime = 0;

    foreach ( $bios AS $bio ) {
      if ( $bio->event == Aliases::current ) {
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
    $events = new ClawEvents(Aliases::current);

    $info = $events->getClawEventInfo();
    return $info;
  }
}
