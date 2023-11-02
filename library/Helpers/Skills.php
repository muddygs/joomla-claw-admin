<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Lib\Aliases;
use InvalidArgumentException;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Exception\UnsupportedAdapterException;
use Joomla\Database\Exception\QueryTypeAlreadyDefinedException;
use RuntimeException;

class Skills
{
  private array $presenterCache = [];
  private array $classCache = [];

  // constructor
  public function __construct(
    public DatabaseDriver $db,
    public string $eventAlias = ''
  )
  {
  }

  public function GetPresentersList(bool $publishedOnly = false): array
  {
    if (count($this->presenterCache)) return $this->presenterCache;

    //if ( $this->eventAlias == '' ) $this->eventAlias = Aliases::current();

    $query = $this->db->getQuery(true);

    $query->select($this->db->qn(['id', 'uid', 'name', 'published']))
      ->from($this->db->qn('#__claw_presenters'))
      ->order('name ASC');

    if ( $publishedOnly ) {
      $query->where($this->db->qn('published') . ' = 1');
    } else {
      $query->where($this->db->qn('published') . ' IN (1,3)'); // published or new
    }

    if ( $this->eventAlias != '' ) {
      $query->where($this->db->qn('event') . ' = :event')
      ->bind(':event', $this->eventAlias);
    }

    $this->db->setQuery($query);
    $this->presenterCache = $this->db->loadObjectList('uid') ?? [];
    return $this->presenterCache;
  }

  /**
   * Load the presenter bio records (check event for determining current)
   * @param int $pid Presenter User ID
   * @return array|null Bio records array (of objects) or null on error
   */
  public function GetPresenterBios(int $pid): ?array
  {
    $query = $this->db->getQuery(true);
    $query->select('*')
      ->from($this->db->quoteName('#__claw_presenters'))
      ->where($this->db->qn('uid') . '= :uid')
      ->where('('. $this->db->qn('archive_state') . ' = "" OR ' . $this->db->qn('archive_state') . ' IS NULL)')
      ->bind(':uid', $pid);

    if ( $this->eventAlias != '' ) {
      $query->where($this->db->qn('event') . ' = :event')
      ->bind(':event', $this->eventAlias);
    }

    $query->order('mtime');

    $this->db->setQuery($query);
    return $this->db->loadObjectList();
  }

  /**
   * Load the presenter skills class records (check event for determining current)
   * @param int $pid User ID of Presenter
   * @return array|null Bio records array (of objects) or null on error
   */
  public function GetPresenterClasses(int $pid): ?array
  {
    $query = $this->db->getQuery(true);
    $query->select('*')
      ->from($this->db->quoteName('#__claw_skills'))
      ->where('((JSON_VALID('.$this->db->qn('presenters').') AND JSON_CONTAINS(' . $this->db->qn('presenters') . ', :copresenters)) OR ' . $this->db->qn('owner') . ' = :uid)')
      ->where('('.$this->db->qn('archive_state') . ' = "" OR ' . $this->db->qn('archive_state') . ' IS NULL)')
      ->bind(':uid', $pid)
      ->bind(':copresenters', $pid);

    if ( $this->eventAlias != '' ) {
      $query->where($this->db->qn('event') . ' = :event')
      ->bind(':event', $this->eventAlias);
    }

    $query->order('mtime');
    $query->setLimit(30);

    $this->db->setQuery($query);
    return $this->db->loadObjectList();
  }

  /**
   * Returns a list of classes for the given event
   * 
   * @param DatabaseDriver $db 
   * @param bool $published (default: true)
   * @return array 
   * @throws UnsupportedAdapterException 
   * @throws QueryTypeAlreadyDefinedException 
   * @throws RuntimeException 
   * @throws InvalidArgumentException 
   */
  public function GetClassList(bool $published = true): array
  {
    if (count($this->classCache)) return $this->classCache;

    $query = $this->db->getQuery(true);

    $query->select('*')
      ->from($this->db->qn('#__claw_skills'))
      ->where($this->db->qn('event') . ' = :event')->bind(':event', $this->eventAlias);
    
    if ( $published ) {
      $query->where($this->db->qn('published') . '= 1')
        ->where($this->db->qn('day') . ' != "0000-00-00"')
        ->where($this->db->qn('time_slot') . ' IS NOT NULL')
        ->where($this->db->qn('time_slot') . ' != ""');
    }

    $this->db->setQuery($query);
    $this->classCache = $this->db->loadObjectList('id') ?? [];
    return $this->classCache;
  }

  public function GetPresenter(int $uid, bool $published = true): ?object
  {
    $query = $this->db->getQuery(true);

    $query->select('*')
      ->from($this->db->qn('#__claw_presenters'))
      ->where($this->db->qn('uid') . ' = :uid')->bind(':uid', $uid)
      ->where($this->db->qn('event') . ' = :event')->bind(':event', $this->eventAlias);

    if ( $published ) {
      $query->where($this->db->qn('published') . ' = 1');
    }

    $this->db->setQuery($query);
    $presenter = $this->db->loadObject();

    if ( $presenter != null )
      $presenter->route = Route::_('index.php?option=com_claw&view=skillspresenter&id=' . $presenter->uid);

    return $presenter;
  }

  public function GetClass(int $cid): ?object
  {
    $query = $this->db->getQuery(true);

    $query->select('*')
      ->from($this->db->qn('#__claw_skills'))
      ->where($this->db->qn('id') . ' = :cid')->bind(':cid', $cid)
      ->where($this->db->qn('event') . ' = :event')->bind(':event', $this->eventAlias)
      ->where($this->db->qn('published') . ' = 1');

    $this->db->setQuery($query);
    $class = $this->db->loadObject();

    if ( null == $class ) return $class;

    if (empty($class->presenters)) {
      $presenterIds = [];
    } else {
      $presenterIds = json_decode($class->presenters);
    }
    array_unshift($presenterIds, $class->owner);

    $location = Locations::GetLocationById($class->location);
    $class->location = $location->value != '' ? $location->value : 'TBD';

    // day
    $class->day = date('l', strtotime($class->day));

    [$time, $length] = explode(':', $class->time_slot);
    // time
    $class->time = Helpers::formatTime($time);

    // length
    $class->length = (int)$length;

    if ( $class->category != 'None' ) $class->category = Config::getConfigValuesText('skill_category', $class->category);

    // Get the presenters
    $class->presenters = [];

    foreach ( $presenterIds AS $presenterId ) {
      $presenter = $this->GetPresenter($presenterId);
      if ( null == $presenter ) continue;
      $class->presenters[] = $presenter;
    }

    return $class;
  }

  public static function rsformJson()
  {
    // Database driver
    $db = Factory::getContainer()->get('DatabaseDriver');
    $skills = new Skills($db, Aliases::current(true));
    $classes = $skills->GetClassList();

    $results = [];

    foreach ( $classes as $class ) {
      // stime corresponds to the tabs, just to help people find their class in the list
      $results[] = (object)[
        'id' => $class->id,
        'stime' => explode(':', $class->time_slot)[0],
        'title' => htmlentities($class->title),
        'gid' => $class->id,
        'day' => date('w', strtotime($class->day)), // 0 = Sunday, 1 = Monday, etc.
      ];
    }

    return json_encode($results);
  }

  public function presentersCSV(string $filename)
  {
    if ( $this->eventAlias == '' ) {
      // error message
      throw new GenericDataException('eventAlias must be specified', 500);
    }

    $query = $this->db->getQuery(true);
    $columnNames = ['id', 'uid', 'name', 'bio', 'photo'];

    $query->select($this->db->qn($columnNames))
      ->from($this->db->qn('#__claw_presenters'))
      ->where($this->db->qn('published') . ' = 1')
      ->where($this->db->qn('event') . ' = :event')
      ->bind(':event', $this->eventAlias)
      ->order('name ASC');

    $this->db->setQuery($query);
    $presenters = $this->db->loadObjectList() ?? [];

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'. $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
    ob_clean();
    ob_start();
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $columnNames);

    foreach ( $presenters AS $p ) {
      $row = [];
      foreach ( $columnNames AS $col ) {
        switch ( $col ) {
          case 'id':
            $row[] = 'presenter_' . $p->$col;
            break;
          case 'photo':
            // Remove leading '/' from path
            $link = Helpers::convertMediaManagerUrl(ltrim($p->$col, '/'));
            $row[] = is_null($link) ? '' : $link;
            break;
          default:
            $row[] = $p->$col;
            break;
        }
      }

      fputcsv($fp, $row);
    }

    fclose($fp);
    ob_end_flush();
  }

  public function classesCSV(string $filename)
  {
    if ( $this->eventAlias == '' ) {
      // error message
      throw new GenericDataException('eventAlias must be specified', 500);
    }

    $this->GetClassList(published: true);
    $this->GetPresentersList(publishedOnly: true);

    // Load database columns
    $columnNames = array_keys($this->db->getTableColumns('#__claw_skills'));
    $columnNames[] = 'track';
    $columnNames[] = 'people';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'. $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
    ob_clean();
    ob_start();
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $columnNames);

    foreach ( $this->classCache AS $c) {
      $row = [];
      foreach ( $columnNames AS $col ) {
        switch ( $col ) {
          case 'id':
            $row[] = 'class_'.$c->$col;
            break;
          case 'start_time':
            $time = Helpers::formatTime($c->$col);
            if ( $time == 'Midnight' ) $time = '12:00 AM';
            if ( $time == 'Noon' ) $time = '12:00 PM';
            $row[] = $time;
            break;
          case 'end_time':
            $row[] = '';
            break;
          case 'people':
            if (empty($c->presenters)) {
              $presenterIds = [];
            } else {
              // TODO: Fix decode
              $presenterIds = json_decode($c->presenters);
            }
            array_unshift($presenterIds, $c->owner);

            // Remove any unpublished presenter ids
            $presenterIds = array_filter($presenterIds, function($id) {
              return isset($this->presenterCache[$id]);
            });

            // Prepend "presenter_" to each id and join with commas
            $row[] = implode(',', array_map(function($id) {
              return 'presenter_' . $id;
            }, $presenterIds));
            break;
          case 'location':
            $location = Locations::GetLocationById($c->$col)->value;
            $row[] = $location;
            break;
          case 'track':
            // track is day converted to day of week
            $time = $c->day . ' ' . explode(':', $c->time_slot)[0];
            $row[] = date('l A', strtotime($time));
            break;
          default:
            $row[] = $c->$col;
            break;
        }
      }

      fputcsv($fp, $row);

    }
  }
}
