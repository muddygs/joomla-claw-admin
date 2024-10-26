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
class LocationsModel extends ListModel
{
  private array $list_fields = [
    'id',
    'event',
    'published',
    'value',
  ];

  public function __construct($config = array())
  {
    if (empty($config['filter_fields'])) {
      $config['filter_fields'] = [];

      foreach ($this->list_fields as $f) {
        $config['filter_fields'][] = $f;
        $config['filter_fields'][] = 'a.' . $f;
      }
    }

    parent::__construct($config);

    $this->db = $this->getDatabase();
  }

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

  protected function getStoreId($id = '')
  {
    // Compile the store id.
    $id .= ':' . serialize($this->getState('filter.name'));
    $id .= ':' . $this->getState('filter.search');
    $id .= ':' . $this->getState('filter.state');

    return parent::getStoreId($id);
  }

  /**
   * Get the master query for retrieving a list of Locations.
   *
   * @return  \Joomla\Database\DatabaseQuery
   */
  protected function getListQuery()
  {
    $db = $this->db;
    $query = $this->db->getQuery(true);

    // Select the required fields from the table.
    $query->select(
      $this->getState(
        'list.select',
        array_map(function ($a) use ($db) {
          return $db->quoteName('a.' . $a);
        }, $this->list_fields)
      )
    )
      ->from($this->db->quoteName('#__claw_locations', 'a'));

    $search = $this->getState('filter.search');
    $eventAlias = $this->getState('filter.event', Aliases::current(true));

    // Filter by search in title.
    if (!empty($search)) {
      $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
      $query->where('(a.value LIKE ' . $search . ')');
    }

    $query->where('a.event = ' . $db->quote($eventAlias));

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
