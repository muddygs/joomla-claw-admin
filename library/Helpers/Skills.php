<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Lib\Aliases;
use InvalidArgumentException;
use Joomla\CMS\Factory;
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

    if ( $this->eventAlias == '' ) $this->eventAlias = Aliases::current();

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
      $presenterIds = explode(',', $class->presenters);
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
}
