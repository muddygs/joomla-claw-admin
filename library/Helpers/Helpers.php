<?php

namespace ClawCorpLib\Helpers;

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

  static public function getGroupId(\Joomla\Database\DatabaseDriver $db, $groupName): int
  {
    $select = 'select id from #__usergroups where title='.$db->q($groupName);
    $db->setQuery($select);
    $groupId = $db->loadResult();

    return $groupId != null ? $groupId : 0;
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
