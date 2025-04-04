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
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class ConfigurationModel extends AdminModel
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   */
  protected $text_prefix = 'COM_CLAW_CONFIGURATION';

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
    $form = $this->loadForm('com_claw.configuration', 'configuration', array('control' => 'jform', 'load_data' => $loadData));

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
    $app = Factory::getApplication();
    $data = $app->getUserState('com_claw.edit.configuration.data', []);

    if (empty($data)) {
      $data = $this->getItem();

      // Convert the fieldname to an int
      $data->fieldname = is_null($data->fieldname) ? 0 : ConfigFieldNames::fromName($data->fieldname)->value ?? 0;
    }

    return $data;
  }

  public function save($data): bool
  {
    // Verify the section value
    $fieldname = ConfigFieldNames::tryFrom($data['fieldname']);
    if ($fieldname === null) {
      $app = Factory::getApplication();
      $app->enqueueMessage('Invalid fieldname', 'error');
      return false;
    }

    $data['fieldname'] = $fieldname->toString();

    return parent::save($data);
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
  public function getTable($name = 'Configuration', $prefix = 'Table', $options = array())
  {
    if ($table = $this->_createTable($name, $prefix, $options)) {
      return $table;
    }

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }
}
