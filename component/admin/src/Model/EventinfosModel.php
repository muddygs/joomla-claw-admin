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

use ClawCorpLib\Enums\EventTypes;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class EventInfosModel extends ListModel
{
  protected $db;

  private array $list_fields = [
    'id',
    'active',
    'alias',
    'description',
    'start_date',
    'end_date',
    'ebLocationId',
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
  protected function populateState($ordering = 'a.start_date', $direction = 'DESC')
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

  public function getItems()
  {
    $query = $this->getListQuery();

    $this->db->setQuery($query);
    $rows = $this->db->loadObjectList();


    // Get a storage key.
    $store = $this->getStoreId();

    // The cache is for other things, like paging, that need data counts, but the view wants the list directly
    $this->cache[$store] = $rows;
    return $rows;
  }

  /**
   * Get the master query for retrieving a list of countries subject to the model state.
   *
   * @return  \Joomla\Database\DatabaseQuery
   *
   * @since   1.6
   */
  protected function getListQuery()
  {
    $db = $this->db;
    $query = $this->db->getQuery(true);

    // Select the required fields from the table.
    $query->select(
      $this->getState(
        'list.select', array_map( function($a) use($db) { return $db->quoteName('a.'.$a); }, $this->list_fields)
      )
    )
      ->from($this->db->quoteName('#__claw_eventinfos', 'a'));

    // Filter by search in title.
    $search = $this->getState('filter.search');

    if (!empty($search)) {
      $search = $this->db->quote('%' . str_replace(' ', '%', $this->db->escape(trim($search), true) . '%'));
      $query->where('(a.alias LIKE ' . $search . ')', 'OR')
        ->where('(a.description LIKE ' . $search . ')');
    }

    // Never show "refund" rows
    $query->where('a.eventType != '. $db->quote(EventTypes::refunds->value));

    // Add the list ordering clause.
    $orderCol  = $this->state->get('list.ordering', 'a.start_date');
    $orderDirn = $this->state->get('list.direction', 'DESC');

    $query->order($this->db->escape($orderCol) . ' ' . $this->db->escape($orderDirn));
    return $query;
  }

  public function delete(array $cid): bool
  {
    $query = $this->db->getQuery(true);
    $query->delete($this->db->quoteName('#__claw_eventinfos'))->where($this->db->quoteName('id') . ' IN (' . implode(',', $cid) . ')');
    $this->db->setQuery($query);
    $this->db->execute();
    return true;
  }
}
