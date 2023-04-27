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
use Joomla\CMS\MVC\Model\ListModel;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Helpers\Skills;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\ParameterType;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class SkillssubmissionsModel extends ListModel
{

	private array $list_fields = [
		'id',
		'published',
		'title',
		'event',
		'day',
		'time_slot',
		'location',
		'track',
		'presenters',
		'equipment_info',
		'copresenter_info',
		'requirements_info',
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

	protected function populateState($ordering = 'mtime', $direction = 'DESC')
	{
		// List state information.
		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');

		return parent::getStoreId($id);
	}
	/**
	 * Get the master query for retrieving a list of skills classes
	 *
	 * @return  \Joomla\Database\QueryInterface
	 *
	 */
  protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

    $app = Factory::getApplication('site');
    $uid = $app->getIdentity()->id;
    // if ( 0 == $uid ) return $query;

    // Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', array_map( function($a) use($db) { return $db->quoteName('a.'.$a); }, $this->list_fields)
			)
		)
			->from($db->quoteName('#__claw_skills', 'a'));

    $query->where('find_in_set(:uid,a.presenters) <> 0')
    ->bind(':uid', $uid);

    $query->where($db->quoteName('a.published') . '=1');

    $event = $this->getState('filter.event');

		switch ($event) {
			case '':
			case '_current_':
				$event = Aliases::current;
				break;
			case '_all_':
				$event = '';
		}
		
		if ( $event != '' )
		{
			$query->where('a.event = :event')
			->bind(':event', $event);
		}

    $query->order($db->quoteName('a.mtime'), 'DESC');

    return $query;
  }


}