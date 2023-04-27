<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;
use ClawCorpLib\Helpers\Helpers;

/**
 * Get a single presenter submission from an authenticated user
 */
class SkillsubmissionModel extends AdminModel
{
	public function getForm($data = [], $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_claw.skillsubmission', 'skillsubmission', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData()
	{
		$mergeData = null;
		$submittedFormData = Helpers::sessionGet('formdata');
		if ( $submittedFormData ) {
			$mergeData = json_decode($submittedFormData, true);
		}


		// Check if a record for this presenter exists
		$app = Factory::getApplication();
		$uid = $app->getIdentity()->id;

		/** @var Joomla\Database\Mysqli\MysqliDriver */
		// $db = $this->getDatabase();
		// $query = $db->getQuery(true);

		// $query->select($db->qn(['id']))
		// 	->from($db->qn('#__claw_presenters'))
		// 	->where($db->qn('uid') . '=' . $uid)
		// 	->where($db->qn('published') . '= 1');

		// $db->setQuery($query);
		// $result = $db->loadResult();

		// if ($result) {
		// 	$this->setState($this->getName() . '.id', $result);
		// }

		$data = $this->getItem();

		if ( $mergeData ) {
			foreach ( $mergeData AS $key => $value ) {
				$data->$key = $value;
			}
		}

		if ( $data->photo ) {
			Helpers::sessionSet('photo', $data->photo);
		}

 		return $data;
	}

	public function getTable($name = '', $prefix = '', $options = array())
	{
		$name = 'Skills';
		$prefix = 'Table';

		if ($table = $this->_createTable($name, $prefix, $options)) {
			return $table;
		}

		throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
	}
}