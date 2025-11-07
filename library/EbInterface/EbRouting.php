<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\EbInterface;

use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

\defined('_JEXEC') or die;

final class EbRouting
{
  private static ?DatabaseDriver $db = null;

  // Call this from anywhere in the class
  private static function db(): DatabaseDriver
  {
    if (!self::$db) {
      self::$db = Factory::getContainer()->get(DatabaseDriver::class);
    }
    return self::$db;
  }

  // This code is based on components/com_eventbooking/router.php:478
  // But, I think there's a bug in their code that isn't handling routing of
  // category paths when they don't exist in the database, so let's make sure
  // they exist

  /** Update EventBooking routing tables and our eventid-to-alias lookup
   * @param EbEventTable $row - Event needing routing/indexing
   * @return void
   */

  public static function updateRoutingTables(EbEventTable $row, EventInfo $eventInfo): void
  {
    self::db();
    self::insertEbCategoryRouting($row);
    self::insertEbEventRouting($row);
    self::insertEventMapping($row, $eventInfo);
  }

  private static function insertEbCategoryRouting($row): void
  {
    $queryString = "view=category&layout=table&id={$row->main_category_id}";
    $categoryPathArray = self::getEbCategoryPath($row->main_category_id);

    $segments    = array_map('Joomla\CMS\Application\ApplicationHelper::stringURLSafe', $categoryPathArray);
    $route       = implode('/', $segments);
    $key         = md5($route);

    $dbQuery = self::$db->getQuery(true)
      ->select('id')
      ->from('#__eb_urls')
      ->where('md5_key = ' . self::$db->quote($key));
    self::$db->setQuery($dbQuery);
    $urlId = (int) self::$db->loadResult();

    if (!$urlId) {
      $dbQuery->clear()
        ->insert('#__eb_urls')
        ->columns(self::$db->quoteName(['md5_key', 'query', 'route', 'view', 'record_id']))
        ->values(implode(',', self::$db->quote([$key, $queryString, $route, 'category', (int) $row->main_category_id])));
      self::$db->setQuery($dbQuery);
      self::$db->execute();
    }
  }

  private static function insertEbEventRouting(EbEventTable $row): void
  {
    $queryString = "view=event&id=0&catid={$row->main_category_id}";
    $categoryPathArray = self::getEbCategoryPath($row->main_category_id);
    $categoryPathArray[] = $row->id . '-' . $row->alias;
    $record_id = $row->id;

    $segments    = array_map('Joomla\CMS\Application\ApplicationHelper::stringURLSafe', $categoryPathArray);
    $route       = implode('/', $segments);
    $key         = md5($route);

    $dbQuery = self::$db->getQuery(true)
      ->select('id')
      ->from('#__eb_urls')
      ->where('md5_key = ' . self::$db->quote($key));
    self::$db->setQuery($dbQuery);
    $urlId = (int) self::$db->loadResult();

    if (!$urlId) {
      $dbQuery->clear()
        ->insert('#__eb_urls')
        ->columns(self::$db->quoteName(['md5_key', 'query', 'route', 'view', 'record_id']))
        ->values(implode(',', self::$db->quote([$key, $queryString, $route, 'event', $record_id])));
      self::$db->setQuery($dbQuery);
      self::$db->execute();
    }
  }

  /** 
   * Event mapping allows for getting the event alias quickly by event id
   */
  private static function insertEventMapping(EbEventTable $row, EventInfo $eventInfo): void
  {
    $query = self::$db->getQuery(true);

    // Does this entry already exist?
    $query->select('*')
      ->from('#__claw_eventid_mapping')
      ->where('eventid = :eventid')
      ->bind(':eventid', $row->id);
    self::$db->setQuery($query);
    $result = self::$db->loadObject();

    if ($result != null) {
      if ($result->alias == $eventInfo->alias)
        return;

      // Alias is incorrect, we wipe all information on this event id
      $query = self::$db->getQuery(true)
        ->delete('#__claw_eventid_mapping')
        ->where('eventid = :eventid')
        ->bind(':eventid', $row->id);
    }

    $query = self::$db->getQuery(true)
      ->insert(self::$db->quoteName('#__claw_eventid_mapping'))
      ->columns(self::$db->quoteName(['eventid', 'alias']))
      ->values(implode(',', (array)self::$db->quote([$row->id, $eventInfo->alias])));
    self::$db->setQuery($query);
    self::$db->execute();
  }


  // Honestly, this mess came from the gipities...it has been tested as mostly functional
  // I wasn't aware of WITH in the newer MariaDB, so we can learn from an LLM once in a while
  // Nice to be able to find the chain of hierarchy at the db level instead of using
  // a loop
  private static function getEbCategoryPath(int $mainCategoryId)
  {
    $sql = <<<SQL
WITH RECURSIVE ancestor_chain AS (
  SELECT c.id, c.parent, c.alias, 0 AS depth, CAST(c.id AS CHAR(512)) AS path_guard
  FROM s1fi8_eb_categories c
  WHERE c.id = {$mainCategoryId}
  UNION ALL
  SELECT p.id, p.parent, p.alias, ac.depth + 1, CONCAT(ac.path_guard, ',', p.id)
  FROM s1fi8_eb_categories p
  JOIN ancestor_chain ac ON p.id = ac.parent
  WHERE p.id <> ac.id
    AND FIND_IN_SET(p.id, ac.path_guard) = 0
    AND ac.depth < 64
)
SELECT id, parent, alias, depth
FROM ancestor_chain
ORDER BY depth DESC
SQL;

    self::$db->setQuery($sql);

    $results = self::$db->loadAssocList();
    $results = array_column($results, 'alias');
    return $results;
  }
}
