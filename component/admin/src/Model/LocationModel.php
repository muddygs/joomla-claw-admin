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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;
use ClawCorpLib\Lib\Aliases;

class LocationModel extends AdminModel
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   */
  protected $text_prefix = 'COM_CLAW_LOCATION';

  public function save($data)
  {
    if ($data['event'] == 0) {
      $data['event'] = Aliases::current(true);
    }

    return parent::save($data);
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
    $form = $this->loadForm('com_claw.location', 'location', array('control' => 'jform', 'load_data' => $loadData));

    if (empty($form)) return false;

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
    $data = $app->getUserState('com_claw.edit.location.data', []);

    if (empty($data)) {
      $data = $this->getItem();
    }

    return $data;
  }

  public function getTable($name = '', $prefix = '', $options = array())
  {
    $name = 'Locations';
    $prefix = 'Table';

    if ($table = $this->_createTable($name, $prefix, $options)) {
      return $table;
    }

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }
}

