<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class EventinfoModel extends AdminModel
{
  private $jsonFields = [
    'eb_cat_shifts',
    'eb_cat_supershifts',
    'eb_cat_speeddating',
    'eb_cat_equipment',
    'eb_cat_sponsorship',
    'eb_cat_meals',
    // 'eb_cat_invoice',
  ];

    /**
   * The prefix to use with controller messages.
   *
   * @var    string
   * @since  1.6
   */
  protected $text_prefix = 'COM_CLAW_EVENTINFO';

  public function save($data)
  {
    // Handle JSON data
    foreach ($this->jsonFields as $field) {
      if (isset($data[$field])) {
        // Always make sure we get an array
        if (!is_array($data[$field])) $data[$field] = [$data[$field]];
        $data[$field] = json_encode($data[$field]);
      }
    }

    $data['mtime'] = Helpers::mtime();

    return parent::save($data);
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
    $form = $this->loadForm('com_claw.eventinfo', 'eventinfo', array('control' => 'jform', 'load_data' => $loadData));

    if (empty($form)) return false;

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
    /** @var $app AdministratorApplication */
    $app = Factory::getApplication();
    $data = $app->getUserState('com_claw.edit.eventinfo.data', []);
    if (empty($data)) {
      $data = $this->getItem();

      // Handle JSON data
      foreach ($this->jsonFields as $field) {
        if (property_exists($data, $field) && is_string($data->$field)) $data->$field = json_decode($data->$field);

        // Remove empty values
        if (is_array($data->$field)) $data->$field = array_filter($data->$field);
      }
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
   * @since   3.0
   * @throws  \Exception
   */
  public function getTable($name = '', $prefix = '', $options = [])
  {
    $name = 'Eventinfos';
    // $prefix = 'Table';

    if ($table = $this->_createTable($name, $prefix, $options)) return $table;

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }
}