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
use ClawCorpLib\Lib\ClawEvents;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class EventModel extends AdminModel
{
  	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_CLAW_EVENT';

	public function save($data)
	{
		// deprecate $input = Factory::getApplication()->input;

		// $input = Factory::getApplication()->getInput();
		// $app = Factory::getApplication();
		// $app = $this::castAdministratorApplication($app); // 
		// $oldcatid = $app->getUserState('com_claw.event.old', array());

		// if ( 0 == $data['id'] || -1 == $input->data['ordering'] || $oldcatid != $data['catid'] )
		// {
		// 	$data['ordering'] = EventHelper::nextOrdering($this->getDatabase(), (int)$data['catid'])+1;
		// }

		// // Basic replacement to avoid database uniqueness error (aliasindex)
		// if ( $data['alias'] == '' )
		// {
		// 	$patterns = [
		// 		'/\s/',
		// 		'/[^a-z0-9_]/'
		// 	];

		// 	$replacements = [
		// 		'_',
		// 		''
		// 	];

		// 	$data['alias'] = preg_replace($patterns, $replacements, strtolower($data['value']));
		// }
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
		$form = $this->loadForm('com_claw.event', 'event', array('control' => 'jform', 'load_data' => $loadData));

		$s = $this->getState('event.id');

		$e = new ClawEvents("l1122");
		$info = $e->getClawEventInfo();

		$parentField = Helpers::castListField($form->getField('day'));
		$current = $info->start_date;

		$days = Helpers::getDateArray($info->start_date);
		$parentField->addOption('Wed', ['value' => $days['Wed']]);
		$parentField->addOption('Thu', ['value' => $days['Thu']]);
		$parentField->addOption('Fri', ['value' => $days['Fri']]);
		$parentField->addOption('Sat', ['value' => $days['Sat']]);
		$parentField->addOption('Sun', ['value' => $days['Sun']]);

		if (empty($form))
		{
			return false;
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
		$data = $app->getUserState('com_claw.edit.event.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		//$app->setUserState("com_claw.event.old", $data->catid);

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
		$name = 'Events';
		$prefix = 'Table';

		if ($table = $this->_createTable($name, $prefix, $options))
		{
			return $table;
		}

		throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
	}
}