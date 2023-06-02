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
use Joomla\CMS\Application\AdministratorApplication;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Helpers\EventBooking;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class ScheduleModel extends AdminModel
{
  	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_CLAW_SCHEDULE';

	public function save($data)
	{
		// Handle array merges
		// https://github.com/muddygs/joomla-claw-admin/wiki/Joomla-Form-Load-Save-of-Checkboxes-and-Multi-Select-Lists

		$data['sponsors'] = json_encode($data['sponsors']);
		$data['fee_event'] = implode(',',$data['fee_event']);
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
		// Get the form and add dynamic values

		$form = $this->loadForm('com_claw.schedule', 'schedule', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		$event = $form->getField('event')->value;
		$e = new ClawEvents( !empty($event) ? $event : Aliases::current);
		$info = $e->getEvent()->getInfo();

		/** @var $parentField \Joomla\CMS\Form\Field\ListField */
		$parentField = $form->getField('day');
		$days = Helpers::getDateArray($info->start_date);
		foreach(['Wed','Thu','Fri','Sat','Sun'] AS $day) {
			$parentField->addOption($day, ['value' => $days[$day]]);

		}

		$locations = Helpers::getLocations($this->getDatabase(), $info->locationAlias);
		/** @var $parentField \Joomla\CMS\Form\Field\ListField */
		$parentField = $form->getField('location');
		foreach ( $locations AS $l )
		{
			$parentField->addOption($l->value, ['value' => $l->id]);
		}

		$sponsors = Helpers::getSponsorsList($this->getDatabase());

		/** @var $parentField \Joomla\CMS\Form\Field\ListField */
		$parentField = $form->getField('sponsors');
		foreach ( $sponsors AS $s )
		{
			$parentField->addOption($s->name, ['value' => $s->id]);
		}

		$events = EventBooking::LoadTicketedEvents($e);

		/** @var $parentField \Joomla\CMS\Form\Field\ListField */
		$parentField = $form->getField('event_id');
		foreach ( $events AS $id => $title )
		{
			$parentField->addOption($title, ['value' => $id]);
		}

		return $form;
	}



	/**
	 * Hack for Intelliphense
	 * @param mixed $x 
	 * @return AdministratorApplication 
	 */
	public static function castAdministratorApplication($x): AdministratorApplication
	{
		return $x;
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
		$app = $this::castAdministratorApplication($app);
		$data = $app->getUserState('com_claw.edit.schedule.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		// Expand checkboxes

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
		$name = 'Schedules';
		$prefix = 'Table';

		if ($table = $this->_createTable($name, $prefix, $options))
		{
			return $table;
		}

		throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
	}
}