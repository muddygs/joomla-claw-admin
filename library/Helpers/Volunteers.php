<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Factory;

class Volunteers
{
  /**
   * Given a key/value id/title array of shift events, loads the details
   * based on the event ids
   * @param string Event alias from which to pull shifts
   * @return array eb_event records based on input event ids
   */
  public static function getShiftEventDetails(string $clawEventAlias): array
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $eventConfig = new EventConfig($clawEventAlias);

    $shiftEvents = $eventConfig->getEventsByCategoryId(ClawEvents::getCategoryIds(Aliases::shiftCategories()));

    $eventIds = array_column($shiftEvents, 'id');

    if (sizeof($eventIds) == 0) return [];

    $e = implode(',', $eventIds);

    $option = [];

    $query = $db->getQuery(true);
    $query->select($db->qn(['id', 'title', 'alias', 'event_capacity', 'event_date', 'event_end_date', 'published']))
      ->select('( SELECT count(*) FROM #__eb_registrants r WHERE r.event_id = e.id AND r.published = 1 ) AS memberCount')
      ->from($db->qn('#__eb_events', 'e'))
      ->where($db->qn('id') . ' IN (' . $e . ')')
      ->order($db->qn('title'));

    $db->setQuery($query);
    $rows = $db->loadObjectList();

    foreach ($rows as $row) {
      if (!str_starts_with($row->alias, $eventConfig->eventInfo->shiftPrefix)) continue;

      $sid = (int)(explode('-', substr($row->alias, strlen($eventConfig->eventInfo->shiftPrefix)))[1]);

      // Handle exception for old style shift ids
      if ('l1123' == $clawEventAlias) {
        $sid = match ($sid) {
          143 => 21,
          144 => 22,
          184 => 23,
          191 => 24,
          192 => 25,
          193 => 26,
          194 => 27,
          195 => 28,
          196 => 29,
          197 => 30,
          198 => 31,
          199 => 32,
          200 => 33,
          201 => 34,
          default => 0,
        };
      }

      if (!array_key_exists($sid, $option)) {
        $option[$sid] = [];
      }

      $option[$sid][$row->id] = $row;
    }

    return $option;
  }

  /**
   * Retrieve shift title and coordinator user ids
   * @param int $sid Shift ID
   * @return object Shift information or null
   */
  public static function getShiftInfo(int $sid): ?object
  {
    $db = Factory::getContainer()->get('DatabaseDriver');
    $sid = $db->quote($sid);

    $query = $db->getQuery(true);
    $query->select($db->qn(['title', 'coordinators']))
      ->from($db->qn('#__claw_shifts'))
      ->where($db->qn('id') . '=' . $sid);

    $db->setQuery($query);
    $row = $db->loadObject();

    if (null == $row) return null;

    $row->coordinators = json_decode($row->coordinators);

    return $row;
  }

  /**
   * Retrieves array of volunteer shift grids associated with the specific
   * or current user
   * 
   * @param int $uid Lookup for a specific UID or 0 (for use signed in user)
   * @param string $adminGroup If user is in this group, returns all enabled shifts
   * @return array Shift grid IDs
   */

  function getGridIDByUser(int $uid = 0, string $adminGroup = ''): array
  {
    $db = Factory::getContainer()->get('DatabaseDriver');

    $groupNames = [];

    if (0 == $uid) {
      $uid = Factory::getUser()->id;
    }

    if ($uid != 0) $groupNames = Helpers::getUserGroupsByName($uid);

    if (0 == $uid) return [];

    $query = <<< SQL
SELECT DISTINCT g.id
FROM `s1fi8_fabrik_shift_grids` g
LEFT OUTER JOIN `s1fi8_fabrik_coord` AS c ON c.coordinator_user = $uid
LEFT OUTER JOIN `s1fi8_fabrik_shift_grids_repeat_other_coordinators` AS o ON o.other_coordinators=c.id
WHERE (g.coordinator = c.id OR g.id = o.parent_id) AND g.enabled = 1
ORDER BY g.shift_title
SQL;

    if ('' != $adminGroup && array_key_exists($adminGroup, $groupNames)) {
      // New query for admins
      $query = <<< SQL
SELECT id
FROM `s1fi8_fabrik_shift_grids`
WHERE enabled = 1
ORDER BY shift_title
SQL;
    }

    $db->setQuery($query);
    $rows = $db->loadColumn();
    return $rows;
  }

  function getEventsByGridID($db, $gid = 0)
  {
    $info = new EventInfo(Aliases::current(true));

    $query = <<< SQL
SELECT *
FROM `s1fi8_eb_events`
WHERE alias RLIKE '^{$info->shiftPrefix}.*-$gid-[[:digit:]]+-.*$' AND published=1
ORDER BY event_date
SQL;

    $db->setQuery($query);
    $rows = $db->loadObjectList('id');
    return $rows;
  }
}
