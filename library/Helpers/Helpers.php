<?php

namespace ClawCorpLib\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\Field\SubformField;
use Joomla\Database\DatabaseDriver;

class Helpers
{
  static function ClawHelpersLoaded(): bool
  {
      return true;
  }
  
  static function getUsersByGroupName(DatabaseDriver $db, string $groupname): array
  {
    $groupId = Helpers::getGroupId($db, $groupname);

    if ( !$groupId ) return [];

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
    $query->select($db->qn(['id','name']))
    ->from($db->qn('#__claw_sponsors'))
    ->where($db->qn('published') . '=1');

    if ( sizeof($filter) > 0 )
    {
      $filter = (array)($db->q($filter));
      $query->where($db->qn('type'). ' IN ('.implode(',',$filter).')');
    }

    $query->order('name ASC');

    $db->setQuery($query);
    return $db->loadObjectList();
  }

  static function getLocations(DatabaseDriver $db, string $baseAlias = ''): array
  {
    $query = $db->getQuery(true);
    $query->select(['l.id','l.value'])
    ->from($db->qn('#__claw_locations', 'l'));

    if ( $baseAlias != '' ) {
      $query->join('LEFT OUTER', $db->qn('#__claw_locations', 't') . ' ON ' . $db->qn('t.alias') . ' = ' . $db->q($baseAlias))
      ->where($db->qn('t.published') . '= 1')
      ->where($db->qn('l.catid') . '=' . $db->qn('t.id'));
    }

    $query->where($db->qn('l.published') . '= 1');

    $db->setQuery($query);
    return $db->loadObjectList();
  }

  static public function getGroupId(DatabaseDriver $db, $groupName): int
  {
    $query = $db->getQuery(true);
    $query->select($db->qn(['id']))
    ->from($db->qn('#__usergroups'))
    ->where($db->qn('title') . '='. $db->q($groupName));

    $db->setQuery($query);
    $groupId = $db->loadResult();

    return $groupId != null ? $groupId : 0;
  }

  /**
   * Returns array with short day (Mon,Tue) to sql date for the event week starting Monday
   */
  static public function getDateArray(string $startDate)
  {
    $result = [];

    $date = Factory::getDate($startDate);

    if ( $date->dayofweek != 1 ) // 0 is Sunday
    {
      die('Starting date must be a Monday');
    }

    $date->setTime(0,0);
    for ( $i = 0; $i < 7; $i++)
    {
      $date->modify(('+1 day'));
      $result[$date->format('D')] = $date->toSql();
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
   * @return string Value of key (or null on error)
   */
  static function sessionGet(string $key, string $default = ''): ?string
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
