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

use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class VendorsModel extends ListModel
{
  protected $db;

  private array $list_fields = [
    'id',
    'published',
    'name',
    'spaces',
    'link',
    'description',
    'logo',
    'ordering',
    'mtime',
  ];	

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
        $config['filter_fields'][] = $f;
        $config['filter_fields'][] = 'a.'.$f;
      }
    }

    parent::__construct($config);

    $this->db = $this->getDatabase();
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
  protected function populateState($ordering = 'name', $direction = 'ASC')
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
    //$id .= ':' . serialize($this->getState('filter.tag'));

    return parent::getStoreId($id);
  }

  /**
   * Get the master query for retrieving a list of vendors.
   *
   * @return  \Joomla\Database\DatabaseQuery
   *
   * @since   1.6
   */
  protected function getListQuery()
  {
    $db    = $this->db;
    $query = $db->getQuery(true);

    // Select the required fields from the table.
    $query->select(
      $this->getState(
        'list.select', array_map( function($a) use($db) { return $db->quoteName('a.'.$a); }, $this->list_fields)
      )
    )
    ->from($db->quoteName('#__claw_vendors', 'a'));

    // Filter by search in title.
    $search = $this->getState('filter.search');
    $event = $this->getState('filter.event', '_current_');
    $published = $this->getState('filter.published', -999);

    if (!empty($search))
    {
      $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
      $query->where('(a.name LIKE ' . $search . ')');
    }

    if ( $event != null ) {
      if ( $event == '_current_' ) $event = Aliases::current();
      $query->where('a.event = :event')->bind(':event', $event);
    }

    if ( $published != -999)
      $query->where('a.published = :published')->bind(':published', $published);

    // Add the list ordering clause.
    $orderCol  = $this->state->get('list.ordering', 'a.name');
    $orderDirn = $this->state->get('list.direction', 'ASC');

    $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
    return $query;
  }

  public function delete(array $cid): bool
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__claw_vendors'))->where($db->quoteName('id') . ' IN (' . implode(',', $cid) . ')');
		$db->setQuery($query);
		$db->execute();
		return true;
	}

  public function saveorder($pks = [], $order = null)
	{
		try {
			$query = $this->_db->getQuery(true);

			// Validate arguments
			if (is_array($pks) && is_array($order) && count($pks) == count($order)) {
					for ($i = 0, $count = count($pks); $i < $count; $i++) {
							// Do an update to change the lft values in the table for each id
							$query->clear()
									->update('#__claw_vendors')
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
}