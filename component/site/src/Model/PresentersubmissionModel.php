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

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class PresentersubmissionModel extends AdminModel
{

	private array $list_fields = [
		'id',
		'uid',
		'published',
		'name',
		'legal_name',
		'social_media',
		'phone',
		'phone_info',
		'arrival',
		'copresenting',
		'comments',
		'bio',
		'photo'
	];	

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 */
	public function __construct($config = [])
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = [];
			
			foreach( $this->list_fields AS $f )
			{
				$config['filter_fields'][] = $f;
				$config['filter_fields'][] = 'a.'.$f;
			}
		}

		parent::__construct($config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_claw.presentersubmission', 'presentersubmission', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	protected function loadFormData()
	{
		// Check if a record for this presenter exists
		$app = Factory::getApplication();
		$uid = $app->getIdentity()->id;

		// if ( !$uid ) {
		// 	$app->enqueueMessage('You must be signed in to submit a presenter biography.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
		// 	$app->set

		// }

		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select($db->qn(['id']))
			->from($db->qn('#__claw_presenters'))
			->where($db->qn('uid') . '=' . $uid)
			->where($db->qn('published') . '= 1');

		$db->setQuery($query);
		$result = $db->loadResult();

		if ( $result ) {
			$data = $this->getItem($result);
		} else {
			$data = $this->getItem();
		}

		return $data;
	}


	public function getTable($name = '', $prefix = '', $options = array())
	{
		$name = 'Presenters';
		$prefix = 'Table';

		if ($table = $this->_createTable($name, $prefix, $options))
		{
			return $table;
		}

		throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
	}
	
}