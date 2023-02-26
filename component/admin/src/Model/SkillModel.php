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
use ClawCorpLib\Enums\SkillsAudiences;
use ClawCorpLib\Enums\SkillsCategories;
use ClawCorpLib\Enums\SkillsStartTimes;
use ClawCorpLib\Enums\SkillsTracks;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;

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
		$e = new ClawEvents($data['event']);
		$info = $e->getEvent()->getInfo();

		$data['day'] = $info->modify($data['day']);

		$data['audience'] = implode(',',$data['audience']);
		$data['presenters'] = implode(',',$data['presenters']);

		//$data['start_time'] = 
		$data['start_time'] = SkillsStartTimes::Find($data['start_time'])->ToSql();

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
	public function getForm($data = [], $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_claw.skill', 'skill', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		// For cases, see libraries/claw/Enums

		/** @var $filter \Joomla\CMS\Form\FormField */
		$audience = $form->getField('audience');
		foreach(SkillsAudiences::cases() AS $c )
		{
			if ( $c->name == 'Open' ) continue;
			$audience->addOption( $c->value, [ 'value' => $c->name]);
		}

		/** @var $filter \Joomla\CMS\Form\FormField */
		$audience = $form->getField('start_time');
		foreach(SkillsStartTimes::cases() AS $c )
		{
			$audience->addOption( $c->ToString(), [ 'value' => $c->name]);
		}

		/** @var $filter \Joomla\CMS\Form\FormField */
		$audience = $form->getField('category');
		foreach(SkillsCategories::cases() AS $c )
		{
			if ( $c->name == 'TBD' ) continue;
			$audience->addOption( $c->value, [ 'value' => $c->name]);
		}

		/** @var $filter \Joomla\CMS\Form\FormField */
		$audience = $form->getField('track');
		foreach(SkillsTracks::cases() AS $c )
		{
			if ( $c->name == 'None' ) continue;
			$audience->addOption( $c->value, [ 'value' => $c->name]);
		}

		$locations = Helpers::getLocations($this->getDatabase());

		/** @var $parentField \Joomla\CMS\Form\Field\ListField */
		$parentField = $form->getField('location');
		foreach ( $locations AS $l )
		{
			$parentField->addOption($l->value, ['value' => $l->id]);
		}


		// $locations = Helpers::getLocations($this->getDatabase(), $info->locationAlias);

		// /** @var $parentField \Joomla\CMS\Form\Field\ListField */
		// $parentField = $form->getField('location');
		// foreach ( $locations AS $l )
		// {
		// 	$parentField->addOption($l->value, ['value' => $l->id]);
		// }

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
