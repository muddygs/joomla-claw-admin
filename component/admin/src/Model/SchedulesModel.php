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
use Joomla\CMS\MVC\Model\ListModel;

use ClawCorpLib\Lib\Aliases;
use Joomla\Database\ParameterType;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class SchedulesModel extends ListModel
{
  private array $list_fields = [
    'id',
    'published',
    'day',
    'event',
    'start_time',
    'end_time',
    'event_title',
    'location',
    'sponsors'
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

      foreach ($this->list_fields as $f) {
        $config['filter_fields'][] = $f;
        $config['filter_fields'][] = 'a.' . $f;
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
    // Compile the store id.
    $id .= ':' . serialize($this->getState('filter.name'));
    $id .= ':' . $this->getState('filter.search');
    $id .= ':' . $this->getState('filter.state');

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
    $sponsorsQuery->select($db->quoteName(['id', 'name']))
      ->from($db->quoteName($this->getTable('sponsors')->getTableName()))
      ->where($db->quoteName('published') . '=1');
    $db->setQuery($sponsorsQuery);
    $sponsors = $db->loadAssocList('id', 'name');

    // Replace JSON encoded sponsor array with names
    foreach ($rows as $row) {
      $row->sponsorsText = '';
      if ($row->sponsors ?? 0 == 0) continue;

      $sponsorIds = json_decode($row->sponsors);
      $names = array_intersect_key($sponsors, array_flip($sponsorIds));
      $row->sponsorsText = implode('<br/>', $names);
    }

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
    $db    = $this->getDatabase();
    $query = $db->getQuery(true);

    // Select the required fields from the table.
    $query->select(
      $this->getState(
        'list.select',
        array_map(function ($a) use ($db) {
          return $db->quoteName('a.' . $a);
        }, $this->list_fields)
      )
    )
      ->from($db->quoteName($this->getTable()->getTableName(), 'a'));

    $locationsTable = $this->getTable('locations');

    $query->join('LEFT OUTER', $db->quoteName($locationsTable->getTableName(), 'l') . ' ON ' .
      $db->quoteName('l.id') . ' = ' . $db->quoteName('a.location'));
    $query->select($db->quoteName('l.value', 'location_text'));

    $query->select('SUBSTRING(DAYNAME(a.day),1,3) AS day_text');
    $query->select('TIME_FORMAT(a.start_time, "%h:%i %p") AS start_time_text');
    $query->select('TIME_FORMAT(a.end_time, "%h:%i %p") AS end_time_text');

    // Get filter values
    $search = $this->getState('filter.search');
    $published = $this->getState('filter.published');
    $day = $this->getState('filter.day');
    $event = $this->getState('filter.event', Aliases::current());

    if ($day != null) {
      date_default_timezone_set('etc/UTC');
      $dayInt = date('w', strtotime($day)); 

      if ( $dayInt !== false ) {
        $dayInt++; // PHP to MariaDB conversion
        $query->where('DAYOFWEEK(a.day) = :dayint');
        $query->bind(':dayint', $dayInt, ParameterType::INTEGER);
      }
    }

    if ( $event != 'all' ) {
      $query->where('a.event = :event')->bind(':event', $event);
    }

    if ( $published != null && $published != '*') {
      $query->where('a.published = :published')
        ->bind(':published', $published, ParameterType::INTEGER);
    }

    if (!empty($search)) {
      $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
      $query->where('(a.event_title LIKE ' . $search . ')');
    }

    // Add the list ordering clause.
    $orderCol  = $this->getState('list.ordering', 'a.day');
    $orderDirn = $this->getState('list.direction', 'ASC');

    $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

    $limit = $this->getState('list.limit', 0);
    $start = $this->getState('list.start', 0);

    if ( $limit > 0 ) {
      $query->setLimit($limit, $start);
    }

    return $query;
  }

  public function publish(array $cid, int $state): bool
  {
    $db = $this->getDatabase();
    $cid = $db->quote($cid);

    $query = $db->getQuery(true);
    $query->update($db->quoteName($this->getTable()->getTableName()))
      ->set($db->quoteName('published') . ' = ' . (int) $state)
      ->where($db->quoteName('id') . ' IN (' . implode(',', (array)$cid) . ')');
    $db->setQuery($query);
    $db->execute();
    return true;
  }


  public function delete(array $cid): bool
  {
    $db = $this->getDatabase();

    $cid = $db->quote($cid);

    $query = $db->getQuery(true);
    $query->delete($db->quoteName($this->getTable()->getTableName()))
      ->where($db->quoteName('id') . ' IN (' . implode(',', (array)$cid) . ')');
    $db->setQuery($query);
    $db->execute();
    return true;
  }
}
