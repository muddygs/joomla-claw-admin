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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;

use ClawCorpLib\Helpers\Helpers;

/**
 * Methods to handle editing a record
 */
class VendorModel extends AdminModel
{
	protected $text_prefix = 'COM_CLAW_VENDOR';

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    array
	 */
	public function save($data)
	{
    $input = Factory::getApplication()->getInput();

    if ( 0 == $data['id'] || -1 == $input->data['ordering'] )
		{
			$this->nextOrdering($data);
		}

		$data['mtime'] = Helpers::mtime();

		return parent::save($data);
	}

  private function nextOrdering(&$data)
  {
    $db = $this->getDatabase();

    $query = $db->getQuery(true);
    $query->select('MAX(ordering)')
      ->from('#__claw_vendors')
      ->where('spaces = :spaces')
      ->bind(':spaces', $data['spaces']);

    $db->setQuery($query);
    $result = $db->loadResult() ?? 0;
    $data['ordering'] = $result + 1;
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
		$form = $this->loadForm('com_claw.vendor', 'vendor', ['control' => 'jform', 'load_data' => $loadData]);

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
		/** @var Joomla\CMS\Application\AdministratorApplication */
		$app = Factory::getApplication();
		$data = $app->getUserState('com_claw.edit.vendor.data', []);

		if (empty($data))
		{
			$data = ($this->getItem());
		}

		$data = (object)$data;

		// TODO: Fix calendar date (w/o time) in "null" case
		if ( !property_exists($data, 'expires') || str_starts_with($data->expires, '0000-00-00')) 
			$data->expires = $this->getDatabase()->getNullDate();

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
		$name = 'Vendors';
		$prefix = 'Table';

		if ($table = $this->_createTable($name, $prefix, $options))
		{
			return $table;
		}

		throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
	}
}