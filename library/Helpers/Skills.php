<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Lib\Aliases;
use InvalidArgumentException;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Exception\UnsupportedAdapterException;
use Joomla\Database\Exception\QueryTypeAlreadyDefinedException;
use RuntimeException;

class Skills
{
  private static array $cache = [];

  public static function GetPresentersList(DatabaseDriver $db, string $eventAlias = Aliases::current): array
  {
    if (count(Skills::$cache)) return Skills::$cache;

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
   * @return array 
   * @throws UnsupportedAdapterException 
   * @throws QueryTypeAlreadyDefinedException 
   * @throws RuntimeException 
   * @throws InvalidArgumentException 
   */
  public static function GetPresenterList(DatabaseDriver $db, string $eventAlias): array
  {
    $query = $db->getQuery(true);

    $query->select($db->qn(['uid', 'name']))
      ->from($db->qn('#__claw_presenters'))
      ->where($db->qn('published') . '= 1')
      ->where($db->qn('event') . ' = :event')->bind(':event', $eventAlias);

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
   * Returns a simple list of the classes for the given event
   * 
   * @param DatabaseDriver $db 
   * @param string $eventAlias 
   * @return array 
   * @throws UnsupportedAdapterException 
   * @throws QueryTypeAlreadyDefinedException 
   * @throws RuntimeException 
   * @throws InvalidArgumentException 
   */
  public static function GetClassList(DatabaseDriver $db, string $eventAlias): array
  {
    $query = $db->getQuery(true);

    $query->select('*')
      ->from($db->qn('#__claw_skills'))
      ->where($db->qn('published') . '= 1')
      ->where($db->qn('event') . ' = :event')->bind(':event', $eventAlias);

    $db->setQuery($query);
    return $db->loadObjectList() ?? [];
  }

}
