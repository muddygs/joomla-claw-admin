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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\ListModel;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class EventsModel extends ListModel
{

	private array $list_fields = [ 'id', 'published', 'day', 'start_time', 'event_title', 'location', 'sponsors' ];

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     \JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = [];
			
			foreach( $this->list_fields AS $f )
			{
				//$config['filter_fields'][] = $f;
				$config['filter_fields'][] = 'a.'.$f;
			}
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.0.1
	 */
	protected function populateState($ordering = 'day', $direction = 'ASC')
	{
		$app = Factory::getApplication();

		// List state information
		$value = $app->input->get('limit', $app->get('list_limit', 0), 'uint');
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Override since we are handling getItems manually
		$id = '';
		
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.name'));
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		//$id .= ':' . serialize($this->getState('filter.tag'));

		return parent::getStoreId($id);
	}

	public function getItems()
	{
		$db = $this->getDatabase();
		$query = $this->getListQuery();

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Very hacky here; probably can process the JSON in SQL, but that might not be any better

		// Load cache of all published sponsors
		$sponsorsQuery = $db->getQuery(true);
		$sponsorsQuery->select($db->quoteName(['id','name']))
			->from($db->quoteName('#__claw_sponsors'))
			->where($db->quoteName('published') . '=1');
		$db->setQuery($sponsorsQuery);
		$sponsors = $db->loadAssocList('id','name');

		// Replace JSON encoded sponsor array with names
		foreach( $rows AS $row )
		{
			$sponsorIds = json_decode($row->sponsors);
			$names = array_intersect_key($sponsors, array_flip($sponsorIds));
			$row->sponsorsText = implode('<br/>', $names);
		}

		// Get a storage key.
		$store = $this->getStoreId();

		// The cache is for other things, like paging, that need data counts, but the view wants the list directly
		$this->cache[$store] = $rows;
		return $rows;
	}

	/**
	 * Get the master query for retrieving a list of events in the model state.
	 * Create alt version of data for display in the template
	 *
	 * @return  \Joomla\Database\DatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', array_map( function($a) use($db) { return $db->quoteName('a.'.$a); }, $this->list_fields)
			)
		)
			->from($db->quoteName('#__claw_events', 'a'));


		$query->join('LEFT OUTER', $db->quoteName('#__claw_locations', 'l') . ' ON ' . 
			$db->quoteName('l.id') . ' = ' . $db->quoteName('a.location'));
		$query->select($db->quoteName('l.value','location_text'));

		$query->select('SUBSTRING(DAYNAME(a.day),1,3) AS day_text');
		$query->select('TIME_FORMAT(a.start_time, "%h:%i %p") AS start_time_text');

		// Filter by search in title.
		$search = $this->getState('filter.search');
		$daylist = $this->getState('filter.dayfilter');

		if ( $daylist != null )
		{
			$e = new ClawEvents(Aliases::current);
			$info = $e->getClawEventInfo();
			$days = Helpers::getDateArray($info->start_date);
			if ( array_key_exists($daylist, $days))
			{
				$query->where('a.day =' . $db->quote($days[$daylist]));
			}
	
		}
		
		

		if (!empty($search)) {
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where('(a.event_title LIKE ' . $search . ')');
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.day');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
		return $query;
	}

	public function delete(array $cid): bool
	{
		$db = $this->getDatabase();

		$cid = $db->quote($cid);

		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__claw_events'))->where($db->quoteName('id') . ' IN (' . implode(',', (array)$cid) . ')');
		$db->setQuery($query);
		$db->execute();
		return true;
	}
}
