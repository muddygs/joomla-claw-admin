<?php

namespace ClawCorpLib\Helpers;

use Joomla\Database\DatabaseDriver;

class Skills
{
  private static array $cache = [];

  public static function GetPresentersList(DatabaseDriver $db): array
  {
    if (count(Skills::$cache)) return Skills::$cache;

    $query = $db->getQuery(true);

    $query->select($db->qn(['uid', 'name', 'published']))
      ->from($db->qn('#__claw_presenters'))
      ->where($db->qn('published') . ' IN (1,3)')
      ->order('name ASC');

    $db->setQuery($query);
    Skills::$cache = $db->loadObjectList('uid') ?? [];
    return Skills::$cache;
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
  public static function GetPresenterClasses(DatabaseDriver $db, int $uid, string $current = ''): ?array
  {
    $query = $db->getQuery(true);
    $query->select('*')
      ->from($db->quoteName('#__claw_skills'))
      ->where($db->qn('owner') . '= :uid')
      ->bind(':uid', $uid);

    if ( $current != '' ) {
      $query->where($db->qn('event') . ' = :event')
      ->bind(':event', $current);
    }

    $query->order('mtime');
    $query->setLimit(30);

    $db->setQuery($query);
    return $db->loadObjectList();
  }

}
