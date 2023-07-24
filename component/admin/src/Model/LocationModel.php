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
use Joomla\CMS\Form\Field\FormField;
use Joomla\CMS\Form\Field\ListField;
use ClawCorp\Component\Claw\Administrator\Helper\LocationHelper;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\CMSApplication;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class LocationModel extends AdminModel
{
    /**
   * The prefix to use with controller messages.
   *
   * @var    string
   * @since  1.6
   */
  protected $text_prefix = 'COM_CLAW_LOCATION';

  public function save($data)
  {
    $input = Factory::getApplication()->getInput();
    /** @var $app AdministratorApplication */
    $app = Factory::getApplication();
    $oldcatid = $app->getUserState('com_claw.location.old', array());

    if ( 0 == $data['id'] || -1 == $input->data['ordering'] || $oldcatid != $data['catid'] )
    {
      $data['ordering'] = LocationHelper::nextOrdering($this->getDatabase(), (int)$data['catid']);
    }

    // Basic replacement to avoid database uniqueness error (aliasindex)
    if ( $data['alias'] == '' )
    {
      $patterns = [
        '/\s/',
        '/[^a-z0-9_]/'
      ];

      $replacements = [
        '_',
        ''
      ];

      $data['alias'] = preg_replace($patterns, $replacements, strtolower($data['value']));
    }
    // TODO: further validation for uniqueness

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
    // Get the form.
    $form = $this->loadForm('com_claw.location', 'location', array('control' => 'jform', 'load_data' => $loadData));

    if (empty($form))
    {
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
    /** @var $app AdministratorApplication */
    $app = Factory::getApplication();
    $data = $app->getUserState('com_claw.edit.location.data', array());

    if (empty($data))
    {
      $data = $this->getItem();
    }

    $app->setUserState("com_claw.location.old", $data->catid);

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
  public function getTable($name = '', $prefix = '', $options = array())
  {
    $name = 'Locations';
    $prefix = 'Table';

    if ($table = $this->_createTable($name, $prefix, $options))
    {
      return $table;
    }

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }
}