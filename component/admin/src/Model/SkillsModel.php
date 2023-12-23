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
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\ParameterType;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Helpers\Skills;
use ClawCorpLib\Helpers\Locations;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class SkillsModel extends ListModel
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
    'owner',
    'presenters',
    'mtime',
    'submission_date'
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
   *
   * @since   1.6
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

  public function getItems()
  {
    $items = parent::getItems();

    $event = $this->getState('filter.event');
    switch ($event) {
      case '':
      case '_current_':
        $event = Aliases::current();
        break;
      case '_all_':
        $event = '';
    }

    $locations = Locations::GetLocationsList();
    $skills = new Skills($this->getDatabase(), $event);
    $presenters = $skills->GetPresentersList();

    $new = ' <span class="badge rounded-pill bg-warning">New</span>';
    $unpublished = ' <span class="badge rounded-pill bg-danger">Unpublished</span>';

    foreach ( $items AS $item ) {
      $item->day_text = '<i class="fa fa-question"></i>';
      if ( isset($item->day) && $item->day != '0000-00-00' ) {
        $datetime = date_create($item->day);
        if ( $datetime !== false ) {
          $item->day_text = date_format($datetime, 'D');
        }

        if ( isset($item->time_slot) && $item->time_slot ) {
          $time = explode(':', $item->time_slot)[0];
          $datetime = date_create($time);
          $item->day_text .= ' '.date_format($datetime, 'h:i A');
        }
      } else {
        $item->day_text = '<i class="fa fa-question"></i>';
      }

      if ( 3 == $item->published) {
        $item->title = $item->title. $new;
      }

      $item->presenter_names = [];

      if ( array_key_exists($item->owner, $presenters)) {
        $presenterRoute = Route::_('index.php?option=com_claw&view=presenter&layout=edit&id='.$presenters[$item->owner]->id);
        $item->presenter_names[] = '<a href="'. $presenterRoute. '">'.$presenters[$item->owner]->name.'</a>';
        if ( $presenters[$item->owner]->published == 3 ) {
          $item->presenter_names[count($item->presenter_names)-1] .= $new;
        }

        if ( $item->presenters != '' ) {
          $json = json_decode($item->presenters);

          if ( is_null($presenters)) {
            $json = explode(',', $item->presenters);
          }

          foreach ( $json AS $p ) {
            if ( array_key_exists($p, $presenters)) {
              $item->presenter_names[] = '<i>'.$presenters[$p]->name.'</i>';
              if ( $presenters[$item->owner]->published == 3 ) {
                $item->presenter_names[count($item->presenter_names)-1] .= $new;
              }
      
            } else {
              $item->presenter_names[] = '<span class="text-danger">ERROR: Deleted co-presenter</span>';
              break;
            }
          }
        }
      } else {
        // Do we have the owner record still but is unpublished?
        $presenter = $skills->GetPresenter($item->owner, false);
        if (!is_null($presenter)) {
          $p = (object)[
            'id' => $presenter->id,
            'uid' => $item->owner,
            'name' => $presenter->name,
            'published' => $presenter->published
          ];
  
          $item->presenter_names[] = '<a href="'. Route::_('index.php?option=com_claw&view=presenter&layout=edit&id='.$p->id). '">'.$p->name.$unpublished.'</a>';
        } else {
          $item->presenter_names[] = '<span class="text-danger">ERROR: Deleted presenter</span>';
        }
      }

      if ( !count($item->presenter_names)) {
        $item->presenter_names[] = '<span class="text-danger">ERROR: No presenter</span>';
      }

      $item->location_text = array_key_exists($item->location, $locations) ? $locations[$item->location]->value : '<i class="fa fa-question"></i>';
    }

    return $items;
  }
  /**
   * Get the master query for retrieving a list of countries subject to the model state.
   *
   * @return  \Joomla\Database\QueryInterface
   *
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
        'list.select', array_map( function($a) use($db) { return $db->quoteName('a.'.$a); }, $this->list_fields)
      )
    )
      ->from($db->quoteName('#__claw_skills', 'a'));

    // Filter by search in title.
    $search = $this->getState('filter.search');
    
    if (!empty($search))
    {
      $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
      $query->where( 'a.title LIKE ' . $search );
    }
    
    $event = $this->getState('filter.event');
    $day = $this->getState('filter.day');
    $presenter = $this->getState('filter.presenter');

    switch ($event) {
      case '':
      case '_current_':
        $event = Aliases::current();
        break;
      case '_all_':
        $event = '';
    }

    Helpers::sessionSet('eventAlias', $event);
    
    if ( $event != '' )
    {
      $query->where('a.event = :event')
      ->bind(':event', $event);
    }
    
    
    if ( $day ) {
      date_default_timezone_set('etc/UTC');
      $dayInt = date('w', strtotime($day)); 

      if ( $dayInt !== false ) {
        $dayInt++; // PHP to MariaDB conversion
        $query->where('DAYOFWEEK(a.day) = :dayint');
        $query->bind(':dayint', $dayInt, ParameterType::INTEGER);
      }
    }

    if ( $presenter ) {
      $query->where('a.owner = :presenter');
      $query->bind(':presenter', $presenter);
    }

    // Add the list ordering clause.
    $orderCol  = $this->state->get('list.ordering', 'a.title');
    $orderDirn = $this->state->get('list.direction', 'ASC');

    $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
    return $query;
  }
}
