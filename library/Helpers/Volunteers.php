<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Lib\EventConfig;
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

    $shiftEvents = $eventConfig->getEventsByCategoryId(
      array_merge($eventConfig->eventInfo->eb_cat_shifts, $eventConfig->eventInfo->eb_cat_supershifts)
    );

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

      $pattern = '/-(\d+)-/';

      if (preg_match($pattern, substr($row->alias, strlen($eventConfig->eventInfo->shiftPrefix)), $matches)) {
        $sid = $matches[1];
      } else {
        continue;
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
}
