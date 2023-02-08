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

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Skills;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class SkillModel extends AdminModel
{
  	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_CLAW_SKILL';

	public function save($data)
	{
		$data['mtime'] = date("Y-m-d H:i:s");

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
		$form = $this->loadForm('com_claw.skill', 'skill', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$parentField = Helpers::castListField($form->getField('day'));

		//$days = Helpers::getDateArray($info->start_date);
		foreach(['Fri','Sat','Sun'] AS $day) {
			$parentField->addOption( $day, ['value' => $day] );
		}
		
		$parentField = Helpers::castListField($form->getField('presenters'));
		foreach(Skills::GetPresentersList($this->getDatabase()) AS $p) {
			$parentField->addOption( $p->name, ['value' => $p->id]);
		}

		$locations = Helpers::getLocations($this->getDatabase(), $info->locationAlias);
		$parentField = Helpers::castListField($form->getField('location'));

		foreach ( $locations AS $l )
		{
			$parentField->addOption($l->value, ['value' => $l->id]);
		}

		// 

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
		$data = $app->getUserState('com_claw.edit.skill.data', array());

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
	public function getTable($name = '', $prefix = '', $options = array())
	{
		$name = 'Skills';
		$prefix = 'Table';

		if ($table = $this->_createTable($name, $prefix, $options))
		{
			return $table;
		}

		throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
	}
}
