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

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\DbBlob;
use ClawCorpLib\Lib\Aliases;
use DateTimeZone;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class PresentersModel extends ListModel
{
  protected $db;

  private array $list_fields = [
    'id',
    'published',
    'uid',
    'name',
    'legal_name',
    'event',
    'email',
    'phone',
    'arrival',
    'image_preview',
    'mtime',
    'submission_date'
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

    return parent::getStoreId($id);
  }

  /**
   * Get the master query for retrieving a list of Presenters.
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
        array_map(function ($a) use ($db) {
          return $db->quoteName('a.' . $a);
        }, $this->list_fields)
      )
    )
      ->from($db->quoteName('#__claw_presenters', 'a'));

    // Filter by search in title.
    $search = $this->getState('filter.search');

    if (!empty($search)) {
      $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
      $query->where('(a.name LIKE ' . $search . ')');
    }

    $event = $this->getState('filter.event', Aliases::current(true));

    if ($event != 'all') {
      $query->where('a.event = :event')->bind(':event', $event);
    }

    // Add the list ordering clause.
    $orderCol  = $this->getState('list.ordering', 'a.name');
    $orderDirn = $this->getState('list.direction', 'ASC');

    $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
    return $query;
  }

  public function getItems()
  {
    $items = parent::getItems();

    $config = new Config($this->getState('filter.event', Aliases::current(true)));
    $path = $config->getConfigText(ConfigFieldNames::CONFIG_IMAGES, 'presenters') ?? '/images/skills/presenters/cache';

    $itemIds = array_column($items, 'id');
    $itemMinAges = array_map(function ($item) {
      return new \DateTime($item->mtime, new DateTimeZone('UTC'));
    }, $items);

    // Insert property for cached presenter preview image
    $cache = new DbBlob(
      db: $this->db,
      cacheDir: JPATH_ROOT . $path,
      prefix: 'web_',
      extension: 'jpg'
    );

    $filenames = $cache->toFile(
      tableName: '#__claw_presenters',
      rowIds: $itemIds,
      key: 'image_preview',
      minAges: $itemMinAges
    );

    foreach ($items as $item) {
      if (3 == $item->published) {
        $item->name .= ' <span class="badge rounded-pill bg-warning">New</span>';
      }

      if (array_key_exists($item->id, $filenames)) {
        $item->image_preview = $filenames[$item->id];
      } else {
        $item->image_preview = null;
      }
    }

    return $items;
  }
}

