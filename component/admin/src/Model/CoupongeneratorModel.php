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
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Language\Text;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class CoupongeneratorModel extends FormModel
{
  	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_CLAW_COUPONGENERATOR';

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
	public function getForm($data = [], $loadData = false)
	{
		// Get the form.
		$form = $this->loadForm('com_claw.coupongenerator', 'coupongenerator', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	function getUserViewLevelsByName()
  {
    $identity = Factory::getApplication()->getIdentity();
    if ( !$identity ) return [];

    $views = $identity->getAuthorisedViewLevels();
    $viewids = '(' . implode(',', $views) . ')';

    $db     = Factory::getContainer()->get('DatabaseDriver');
    $query = $db->getQuery(true);
    $query->select($db->qn(['id', 'title']))
      ->from($db->qn('#__viewlevels'))
      ->where('id IN (' . implode(',',$query->bindArray($views)) . ')');
    $db->setQuery($query);
    $avl  = $db->loadAssocList('title');

    return $avl;
  }


}