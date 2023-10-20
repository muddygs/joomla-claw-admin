<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Lib\Aliases;
use InvalidArgumentException;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Exception\UnsupportedAdapterException;
use Joomla\Database\Exception\QueryTypeAlreadyDefinedException;
use RuntimeException;

class Skills
{
  private static array $cache = [];

  public static function GetPresentersList(DatabaseDriver $db, string $eventAlias = ''): array
  {
    if (count(Skills::$cache)) return Skills::$cache;
    if ( $eventAlias == '' ) $eventAlias = Aliases::current();

    $query = $db->getQuery(true);

    $query->select($db->qn(['id', 'uid', 'name', 'published']))
      ->from($db->qn('#__claw_presenters'))
      ->where($db->qn('published') . ' IN (1,3)')
      ->order('name ASC');

    if ( $eventAlias != '' ) {
      $query->where($db->qn('event') . ' = :event')
      ->bind(':event', $eventAlias);
    }

    $db->setQuery($query);
    Skills::$cache = $db->loadObjectList('uid') ?? [];
    return Skills::$cache;
  }

  /**
   * Returns a simple list of the presenters for the given event
   * @param DatabaseDriver $db 
   * @param string $eventAlias 
   * @param bool $published (default: true)
   * @return array 
   * @throws UnsupportedAdapterException 
   * @throws QueryTypeAlreadyDefinedException 
   * @throws RuntimeException 
   * @throws InvalidArgumentException 
   */
  public static function GetPresenterList(DatabaseDriver $db, string $eventAlias, bool $published = true): array
  {
    $query = $db->getQuery(true);

    $query->select($db->qn(['uid', 'name']))
      ->from($db->qn('#__claw_presenters'))
      ->where($db->qn('event') . ' = :event')->bind(':event', $eventAlias);

    if ( $published ) {
      $query->where($db->qn('published') . '= 1');
    }

    $db->setQuery($query);
    return $db->loadObjectList('uid') ?? [];
  }

  /**
   * Load the presenter bio records (check event for determining current)
   * @param DatabaseDriver $db 
   * @param int $uid 
   * @return array|null Bio records array (of objects) or null on error
   */
  public static function GetPresenterBios(DatabaseDriver $db, int $uid, string $current = ''): ?array
  {
    $query = $db->getQuery(true);
    $query->select('*')
      ->from($db->quoteName('#__claw_presenters'))
      ->where($db->qn('uid') . '= :uid')
      ->where('('. $db->qn('archive_state') . ' = "" OR ' . $db->qn('archive_state') . ' IS NULL)')
      ->bind(':uid', $uid);

    if ( $current != '' ) {
      $query->where($db->qn('event') . ' = :event')
      ->bind(':event', $current);
    }

    $query->order('mtime');

    $db->setQuery($query);
    return $db->loadObjectList();
  }

  /**
   * Load the presenter skills class records (check event for determining current)
   * @param DatabaseDriver $db 
   * @param int $uid User ID of Presenter
   * @param string $event Event alias
   * @return array|null Bio records array (of objects) or null on error
   */
  public static function GetPresenterClasses(DatabaseDriver $db, int $uid, string $eventAlias = ''): ?array
  {
    $query = $db->getQuery(true);
    $query->select('*')
      ->from($db->quoteName('#__claw_skills'))
      ->where('((JSON_VALID('.$db->qn('presenters').') AND JSON_CONTAINS(' . $db->qn('presenters') . ', :copresenters)) OR ' . $db->qn('owner') . ' = :uid)')
      ->where('('.$db->qn('archive_state') . ' = "" OR ' . $db->qn('archive_state') . ' IS NULL)')
      ->bind(':uid', $uid)
      ->bind(':copresenters', $uid);

    if ( $eventAlias != '' ) {
      $query->where($db->qn('event') . ' = :event')
      ->bind(':event', $eventAlias);
    }

    $query->order('mtime');
    $query->setLimit(30);

    $db->setQuery($query);
    return $db->loadObjectList();
  }

  /**
   * Returns a list of classes for the given event
   * 
   * @param DatabaseDriver $db 
   * @param string $eventAlias 
   * @param bool $published (default: true)
   * @return array 
   * @throws UnsupportedAdapterException 
   * @throws QueryTypeAlreadyDefinedException 
   * @throws RuntimeException 
   * @throws InvalidArgumentException 
   */
  public static function GetClassList(DatabaseDriver $db, string $eventAlias, bool $published = true): array
  {
    $query = $db->getQuery(true);

    $query->select('*')
      ->from($db->qn('#__claw_skills'))
      ->where($db->qn('event') . ' = :event')->bind(':event', $eventAlias);
    
    if ( $published ) {
      $query->where($db->qn('published') . '= 1');
    }

    $db->setQuery($query);
    return $db->loadObjectList('id') ?? [];
  }

  public static function GetPresenter(DatabaseDriver $db, int $uid, string $eventAlias): ?object
  {
    $query = $db->getQuery(true);

    $query->select('*')
      ->from($db->qn('#__claw_presenters'))
      ->where($db->qn('uid') . ' = :uid')->bind(':uid', $uid)
      ->where($db->qn('event') . ' = :event')->bind(':event', $eventAlias)
      ->where($db->qn('published') . ' = 1');

    $db->setQuery($query);
    $presenter = $db->loadObject();

    if ( $presenter != null )
      $presenter->route = Route::_('index.php?option=com_claw&view=skillspresenter&id=' . $presenter->uid);

    return $presenter;
  }

  public static function GetClass(DatabaseDriver $db, int $cid, string $eventAlias): ?object
  {
    $query = $db->getQuery(true);

    $query->select('*')
      ->from($db->qn('#__claw_skills'))
      ->where($db->qn('id') . ' = :cid')->bind(':cid', $cid)
      ->where($db->qn('event') . ' = :event')->bind(':event', $eventAlias)
      ->where($db->qn('published') . ' = 1');

    $db->setQuery($query);
    $class = $db->loadObject();

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
      $presenter = self::GetPresenter($db, $presenterId, $eventAlias);
      if ( null == $presenter ) continue;
      $class->presenters[] = $presenter;
    }

    return $class;
  }
}
