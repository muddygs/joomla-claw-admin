<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class LocationsModel extends ListModel
{
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
			$config['filter_fields'] = array(
				'id', 'a.id',
				'published', 'a.published',
				// 'parent', 'a.parent',
				'ordering', 'a.ordering',
				'catid', 'a.catid',
				'value', 'a.value',
				'alias', 'a.alias',
			);
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
	protected function populateState($ordering = 'value', $direction = 'ASC')
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
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.name'));
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	public function saveorder($pks = array(), $order = null)
	{
		try {
			$query = $this->_db->getQuery(true);

			// Validate arguments
			if (is_array($pks) && is_array($order) && count($pks) == count($order)) {
					for ($i = 0, $count = count($pks); $i < $count; $i++) {
							// Do an update to change the lft values in the table for each id
							$query->clear()
									->update('#__claw_locations')
									->where('id = ' . (int) $pks[$i])
									->set('ordering = ' . (int) $order[$i]);

							$this->_db->setQuery($query)->execute();
					}

					// Clean the cache
					$this->cleanCache();
					return true;
			} else {
					return false;
			}
		} catch (\Exception $e) {
			throw $e;
		}
	
		return true;
	}


	public function getItems()
	{
		$db = $this->getDatabase();
		$query = $this->getListQuery();

		// TODO: Next major edit: Have locations tied to events; 0-level locations
		// TODO: will need to have event field

		// $limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');

		// // Create the pagination object and add the object to the internal cache -- implementing ListModel getPagination
		// $store = $this->getStoreId('getPagination');
		// $this->cache[$store] = new Pagination($this->getTotal(), $this->getStart(), $limit);

		// $query->setLimit($limit, $this->getStart());

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$parent = 0; // Root level is start on all locations
		$children = [];
		// first pass - collect children
		if (count($rows))
		{
			foreach ($rows as $v)
			{
				$pt   = $v->catid;

				// Copy important values for treerecurse
				$v->parent_id = $pt;
				$v->title = $v->value;

				$list = @$children[$pt] ? $children[$pt] : [];
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}

		$list             = HTMLHelper::_('menu.treerecurse', $parent, '', [], $children, 9999, 0, 0);

		// Get a storage key.
		$store = $this->getStoreId();

		// The cache is for other things, like paging, that need data counts, but the view wants the list directly
		$this->cache[$store] = $list;
		return $list;
	}

	/**
	 * Get the master query for retrieving a list of Locations.
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
				'list.select',
				[
					$db->quoteName('a.id'),
					$db->quoteName('a.published'),
					$db->quoteName('a.ordering'),
					$db->quoteName('a.catid'),
					$db->quoteName('a.value'),
					$db->quoteName('a.alias'),
				]
			)
		)
			->from($db->quoteName('#__claw_locations', 'a'));

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search)) {
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where('(a.value LIKE ' . $search . ')');
		}

		// Add the list ordering clause.
		$orderCol  = $this->getState('list.ordering', 'a.value');
		$orderDirn = $this->getState('list.direction', 'ASC');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
		return $query;
	}

	public function delete(array $cid): bool
	{
		// TODO: prevent deletion of locations that are tied to active events
		$db = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__claw_locations'))->where($db->quoteName('id') . ' IN (' . implode(',', $cid) . ')');
		$db->setQuery($query);
		$db->execute();
		return true;
	}
}
