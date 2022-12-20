<?php

namespace ClawCorpLib\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\Field\SubformField;

class Helpers
{
  const sponsorshipTypes = [
    "1" => 'Sponsor',
    "2" => 'Sustaining',
    "6" => 'Legacy Sustaining',
    "3" => 'Master',
    "5" => 'Legacy Master',
    "4" => 'Media',
  ];

  static function ClawHelpersLoaded(): bool
  {
      return true;
  }
  
  static function getUsersByGroupName(\Joomla\Database\DatabaseDriver $db, string $groupname): array
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

  static function getSponsorsList(\Joomla\Database\DatabaseDriver $db, array $filter = []): array
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
    $sponsors = $db->loadObjectList();

    return $sponsors != null ? $sponsors : [];

  }

  static public function getGroupId(\Joomla\Database\DatabaseDriver $db, $groupName): int
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

  /*
  * Coding helpers for lookup by Intelliphense
  */
  static public function castListField($p): ListField
	{
		return $p;
	}
  static public function castSubFormField($p): SubformField
	{
		return $p;
	}

}
