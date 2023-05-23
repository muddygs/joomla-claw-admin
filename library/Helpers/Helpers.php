<?php

namespace ClawCorpLib\Helpers;

use InvalidArgumentException;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Exception\UnsupportedAdapterException;
use Joomla\Database\Exception\QueryTypeAlreadyDefinedException;
use RuntimeException;

class Helpers
{
  static function ClawHelpersLoaded(): bool
  {
    return true;
  }

  /**
   * Quicky that produces a mostly correct SQL time
   * TODO: set time zone
   * @return string
   */
  static function mtime(): string
  {
    $date = new Date('now');
    return $date->toSQL(true);
  }

  static function getDays(): array
  {
    return [
      'tue',
      'wed',
      'thu',
      'fri',
      'sat',
      'sun',
      'mon',
    ];
  }

  
  /**
   * Returns hh:mm formatted string in seconds
   * @param mixed $t 
   * @return int|bool 
   */
  static function timeToInt($t): int|bool
  {
    $ts = explode(':', $t);
    if (count($ts) < 2) return false;
    return 60*($ts[0] * 60 + $ts[1]);
  }

  static function getClawFieldValues(DatabaseDriver $db, string $section): array
  {
    $query = $db->getQuery(true);
    $query->select(['value','text'])
      ->from('#__claw_field_values')
      ->where('fieldname = :fieldname')
      ->order('value')
      ->bind(':fieldname', $section);
    $db->setQuery($query);
    return $db->loadObjectList('value');
  }


  static function getUsersByGroupName(DatabaseDriver $db, string $groupname): array
  {
    $groupId = Helpers::getGroupId($db, $groupname);

    if (!$groupId) return [];

    $query = <<< SQL
    SELECT m.user_id, u.name
    FROM #__user_usergroup_map m
    LEFT OUTER JOIN #__users u ON u.id = m.user_id
    WHERE m.group_id = $groupId
    ORDER BY u.name
SQL;

    $db->setQuery($query);
    $users = $db->loadObjectList();

    return $users != null ? $users : [];
  }

  static function getSponsorsList(DatabaseDriver $db, array $filter = []): array
  {
    $query = $db->getQuery(true);
    $query->select($db->qn(['id', 'name']))
      ->from($db->qn('#__claw_sponsors'))
      ->where($db->qn('published') . '=1');

    if (sizeof($filter) > 0) {
      $filter = (array)($db->q($filter));
      $query->where($db->qn('type') . ' IN (' . implode(',', $filter) . ')');
    }

    $query->order('name ASC');

    $db->setQuery($query);
    return $db->loadObjectList();
  }

  /**
   * Returns array of locations ordered by catid (parental depth) and ordering (logical order)
   * @param DatabaseDriver $db 
   * @param string $baseAlias 
   * @return array 
   * @throws UnsupportedAdapterException 
   * @throws QueryTypeAlreadyDefinedException 
   * @throws RuntimeException 
   * @throws InvalidArgumentException 
   */
  static function getLocations(DatabaseDriver $db, string $baseAlias = ''): array
  {
    $query = $db->getQuery(true);
    $query->select(['l.id', 'l.value', 'l.catid'])
      ->from($db->qn('#__claw_locations', 'l'));

    if ($baseAlias != '') {
      $query->join('LEFT OUTER', $db->qn('#__claw_locations', 't') . ' ON ' . $db->qn('t.alias') . ' = ' . $db->q($baseAlias))
        ->where($db->qn('t.published') . '= 1')
        ->where($db->qn('l.catid') . '=' . $db->qn('t.id'));
    }

    $query->where($db->qn('l.published') . '= 1');
    $query->order('l.catid ASC, l.ordering ASC');

    $db->setQuery($query);
    return $db->loadObjectList();
  }

  static public function getGroupId(DatabaseDriver $db, $groupName): int
  {
    $query = $db->getQuery(true);
    $query->select($db->qn(['id']))
      ->from($db->qn('#__usergroups'))
      ->where('LOWER('.$db->qn('title') . ')=' . $db->q(strtolower($groupName)));

    $db->setQuery($query);
    $groupId = $db->loadResult();

    return $groupId != null ? $groupId : 0;
  }

  /**
   * Returns array with short day (Mon,Tue) to sql date for the event week starting Monday
   */
  static public function getDateArray(string $startDate, bool $dateOnly = false)
  {
    $result = [];

    $date = Factory::getDate($startDate);

    if ($date->dayofweek != 1) // 0 is Sunday
    {
      die('Starting date must be a Monday');
    }

    $date->setTime(0, 0);
    for ($i = 0; $i < 7; $i++) {
      $date->modify(('+1 day'));
      $d = $date->toSql();
      if ( $dateOnly ) $d = substr($d, 0, 10);
      $result[$date->format('D')] = $d;;
    }

    return $result;
  }

  /**
   * Sets a CLAW-specific Joomla session variable. See code comments for example usage.
   * @param string $key Key to variable
   * @param string $value Key's value
   */
  static function sessionSet(string $key, string $value): void
  {
    /** @var $app \Joomla\CMS\Application\CMSApplicationInterface */
    $app = Factory::getApplication();
    $session = $app->getSession();
    if ($session->isActive()) {
      $session->set($key, $value, 'claw');
    }
  }

  /**
   * Gets a CLAW-specific Joomla session variable
   * @param string Key to the variable
   * @param string Default value if not already set
   * @return string|null Value of key (or null on error)
   */
  static function sessionGet(string $key, string $default = ''): string|null
  {
    /** @var $app \Joomla\CMS\Application\CMSApplicationInterface */
    $app = Factory::getApplication();
    $session = $app->getSession();
    if ($session->isActive()) {
      return $session->get($key, $default, 'claw');
    }

    return null;
  }

  /**
   * Converts hh:mm:ss to hh:mm XM, 00:00 -> Midnight, 12:00 -> Noon
   * @param string Time string (hh:mm:ss)
   * @return string Formatted time
   */
  static function formatTime(string $time): string
  {
    if (0 === strpos($time, '00:00')) {
      $time = "Midnight";
    } else if (0 === strpos($time, '12:00')) {
      $time = "Noon";
    } else {
      date_default_timezone_set('etc/UTC');
      $time = date('g:iA', strtotime(substr($time, 0, 5)));
    }

    return $time;
  }
}
