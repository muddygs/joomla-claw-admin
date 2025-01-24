<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\SkillPublishedState;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\ParameterType;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Helpers\Locations;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Skills\Presenters;

/**
 * Methods to handle a list of records.
 */
class SkillsModel extends ListModel
{
  private array $list_fields = [
    'category',
    'day',
    'event',
    'id',
    'location',
    'mtime',
    'other_presenter_ids',
    'presenter_id',
    'published',
    'submission_date',
    'time_slot',
    'title',
    'track',
    'type',
  ];

  /**
   * Constructor.
   *
   * @param   array  $config  An optional associative array of configuration settings.
   */
  public function __construct($config = [])
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
   */
  protected function populateState($ordering = 'a.title', $direction = 'ASC')
  {
    $app = Factory::getApplication();

    // Load the parameters.
    $this->setState('params', ComponentHelper::getParams('com_claw'));

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
   */
  protected function getStoreId($id = '')
  {
    // Compile the store id.
    $id .= ':' . serialize($this->getState('filter.title'));
    $id .= ':' . $this->getState('filter.search');

    // Include all Filter Options parameters
    $id .= ':' . $this->getState('filter.presenter');
    $id .= ':' . $this->getState('filter.day');
    $id .= ':' . $this->getState('filter.event');

    return parent::getStoreId($id);
  }

  public function getTable($type = 'Skill', $prefix = 'Administrator', $config = [])
  {
    return parent::getTable($type, $prefix, $config);
  }

  # TODO: much of the jiggering here should be put into the view
  public function getItems()
  {
    $items = parent::getItems();

    $event = $this->getState('filter.event', Aliases::current(true));
    $l = new Locations($event);
    $locations = $l->GetLocationsList();

    $eventInfo = new EventInfo($event, true);
    $presenters = Presenters::get($eventInfo);

    $newPill = ' <span class="badge rounded-pill bg-warning">New</span>';
    $unpublishedPill = ' <span class="badge rounded-pill bg-danger">Unpublished</span>';

    foreach ($items as $item) {
      $item->day_text = '<i class="fa fa-question"></i>';
      if (isset($item->day) && $item->day != '0000-00-00') {
        $datetime = date_create($item->day);
        if ($datetime !== false) {
          $item->day_text = date_format($datetime, 'D');
        }

        if (isset($item->time_slot) && $item->time_slot) {
          $time = explode(':', $item->time_slot)[0];
          $datetime = date_create($time);
          $item->day_text .= ' ' . date_format($datetime, 'h:i A');
        }
      } else {
        $item->day_text = '<i class="fa fa-question"></i>';
      }

      if (SkillPublishedState::new->value == $item->published) {
        $item->title .= $newPill;
      }

      $item->presenter_names = [];
      $presenter = null;
      if ($presenters->offsetExists($item->presenter_id)) {
        $presenter = $presenters[$item->presenter_id];
      }

      if (is_null($presenter)) {
        $item->presenter_names = ['<span class="text-danger">ERROR: Deleted or missing presenter</span>'];
        $item->location_text = '<i class="fa fa-question"></i>';
        continue;
      }

      $presenterRoute = Route::_('index.php?option=com_claw&view=presenter&layout=edit&id=' . $item->presenter_id);
      $item->presenter_names[] = '<a href="' . $presenterRoute . '">' . $presenter->name . '</a>';
      if ($presenter->published == SkillPublishedState::new) {
        $item->presenter_names[count($item->presenter_names) - 1] .= $newPill;
      }
      if ($presenter->published == SkillPublishedState::unpublished) {
        $item->presenter_names[count($item->presenter_names) - 1] .= $unpublishedPill;
      }

      $otherPresenterIds = json_decode($item->other_presenter_ids);

      if (!is_null($otherPresenterIds)) {
        foreach ($otherPresenterIds as $p) {
          if ($presenters->offsetExists($p)) {
            $item->presenter_names[] = '<i>' . $presenters[$p]->name . '</i>';
            if ($presenters[$p]->published == SkillPublishedState::new) {
              $item->presenter_names[count($item->presenter_names) - 1] .= $newPill;
            } else if ($presenters[$p]->published == SkillPublishedState::unpublished) {
              $item->presenter_names[count($item->presenter_names) - 1] .= $unpublishedPill;
            }
          } else {
            $item->presenter_names[] = '<span class="text-danger">ERROR: Deleted co-presenter</span>';
          }
        }
      }

      $item->location_text = array_key_exists($item->location, $locations) ? $locations[$item->location]->value : '<i class="fa fa-question"></i>';
    }

    return $items;
  }
  /**
   * Get the master query for retrieving a list of Skills (classes).
   *
   * @return  \Joomla\Database\QueryInterface
   */
  protected function getListQuery()
  {
    $db    = $this->getDatabase();
    $query = $db->getQuery(true);

    // Cache Locations

    // Cache Presenter Public Names

    // Select the required fields from the table.
    $query->select(
      $this->getState(
        'list.select',
        array_map(function ($a) use ($db) {
          return $db->quoteName('a.' . $a);
        }, $this->list_fields)
      )
    )
      ->from($db->quoteName('#__claw_skills', 'a'));

    // Filter by search in title.
    $search = $this->getState('filter.search');

    if (!empty($search)) {
      $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
      $query->where('a.title LIKE ' . $search);
    }

    $event = $this->getState('filter.event', Aliases::current());
    $day = $this->getState('filter.day');
    $presenter = $this->getState('filter.presenter');
    $type = $this->getState('filter.type');
    $category = $this->getState('filter.category');

    Helpers::sessionSet('eventAlias', $event);

    if ($event != 'all') {
      $query->where('a.event = :event')->bind(':event', $event);
    }

    if ($day) {
      date_default_timezone_set('etc/UTC');
      $dayInt = date('w', strtotime($day));

      if ($dayInt !== false) {
        $dayInt++; // PHP to MariaDB conversion
        $query->where('DAYOFWEEK(a.day) = :dayint');
        $query->bind(':dayint', $dayInt, ParameterType::INTEGER);
      }
    }

    if ($presenter) {
      $query->where('a.presenter_id = :presenter');
      $query->bind(':presenter', $presenter);
    }

    if ($type) {
      $query->where('a.type = :type');
      $query->bind(':type', $type);
    }

    if ($category) {
      $query->where('a.category = :category');
      $query->bind(':category', $category);
    }


    // Add the list ordering clause.
    $orderCol  = $this->getState('list.ordering', 'a.title');
    $orderDirn = $this->getState('list.direction', 'ASC');

    $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
    return $query;
  }
}
