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
use Joomla\CMS\Date\Date;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\View\GenericDataException;

use ClawCorpLib\Grid\Grids;
use ClawCorpLib\Helpers\Helpers;


/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class ShiftModel extends AdminModel
{
  	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_CLAW_SHIFT';

	public function save($data)
	{
		$date = new Date('now');
		$data['mtime'] = $date->toSQL();

		$shiftsTable = $this->getTable('Shifts','Table');
		$gridsTable = $this->getTable('ShiftsGrids','Table');

		if ( !$shiftsTable->bind($data))
		{
			throw new GenericDataException('Unable to bind SHIFT data', 500);
		}
		if ( !$gridsTable->bind($data))
		{
			throw new GenericDataException('Unable to bind SHIFT GRID data', 500);
		}

		$shiftsTable->save($data);
		$shiftId = $shiftsTable->id;

		foreach ( $data['grid'] AS $grid )
		{
			$clawgrid = new Grids($shiftId, $grid);
			$clawgrid->store();
		}

		return true;

		return parent::save($data);
	}

	public function validate($form, $data, $group = null)
	{
		foreach ( $data['grid'] AS $grid )
		{
			//$clawgrid = new ClawShiftGrids($this->id, $grid);
			//$clawgrid->validate($form, $data);
		}

		return parent::validate($form, $data);
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
		$form = $this->loadForm('com_claw.shift', 'shift', array('control' => 'jform', 'load_data' => $loadData));

		$p = Helpers::castListField($form->getField('coordinators'));

		$coordinators = Helpers::getUsersByGroupName($this->getDatabase(), 'VolunteerCoord');

		foreach ( $coordinators AS $c )
		{
			$p->addOption($c->name, ['value' => $c->user_id ]);
		}

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
		// $app = Factory::getApplication();
		// $data = $app->getUserState('com_claw.edit.shift.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
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
	public function getTable($name = 'Shifts', $prefix = 'Table', $options = array())
	{
		if ($table = $this->_createTable($name, $prefix, $options))
		{
			return $table;
		}

		throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
	}
}
