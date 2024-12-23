<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Grid\GridShift;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Factory;

/**
 * Methods to handle edit of a Shift record
 */
class ShiftModel extends AdminModel
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   */
  protected $text_prefix = 'COM_CLAW';

  public function save($data)
  {
    $data['mtime'] = Helpers::mtime();

    if (!GridShift::validateGrid($data, 'grid')) {
      $app = Factory::getApplication();
      $app->enqueueMessage('Changes to grid conflict with current deployment', 'error');
      return false;
    }


    $result = parent::save($data);

    if (!$result) return $result;

    GridShift::saveGridTimeArray($data, 'grid');

    return true;
  }

  public function validate($form, $data, $group = null)
  {
    return parent::validate($form, $data);
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
    $form = $this->loadForm('com_claw.shift', 'shift', array('control' => 'jform', 'load_data' => $loadData));

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
    // $app = Factory::getApplication();
    // $data = $app->getUserState('com_claw.edit.shift.data', array());

    if (empty($data)) {
      $data = $this->getItem();
    }

    if (is_object($data) && !is_null($data->id) && $data->id > 0) {
      $eventInfo = new EventInfo($data->event, true);
      $gridShift = new GridShift($data->id, $eventInfo);
      $data->grid = $gridShift->timesToFormArray();
    }

    return $data;
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
   * @throws  \Exception
   */
  public function getTable($name = 'Shifts', $prefix = 'Table', $options = array())
  {
    if ($table = $this->_createTable($name, $prefix, $options)) {
      return $table;
    }

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }
}
