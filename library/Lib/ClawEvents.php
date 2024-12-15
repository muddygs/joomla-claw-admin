<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

use Joomla\CMS\Factory;

use UnexpectedValueException;

\defined('_JEXEC') or die;

class ClawEvents
{
  private static $eventIds = null;
  private static $categoryIds = null;
  private static $fieldIds = null;

  /**
   * Converts event alias to its id
   * @param string $eventAlias Event alias in Event Booking
   * @param bool $quiet Quietly return 0 if alias does not exist
   * @return int Event ID
   */
  public static function getEventId(string $eventAlias, bool $quiet = false): int
  {
    $eventAlias = strtolower(trim($eventAlias));

    if ('' == $eventAlias) die(__FILE__ . ': event alias cannot be blank');

    if (null == self::$eventIds) self::cacheEventAliases();

    if (array_key_exists($eventAlias, self::$eventIds)) {
      return intval(self::$eventIds[$eventAlias]->id);
    } else {
      if ($quiet) return 0;
      throw new UnexpectedValueException(__FILE__ . ': Unknown eventAlias: ' . $eventAlias);
    }
  }

  /**
   * Given a category alias, return its category id
   * @param string Category alias in Event Booking
   * @return int Category ID
   */
  public static function getCategoryId(string $categoryAlias): int
  {
    if (self::$categoryIds == null) self::cacheCategoryAliases();
    if (!array_key_exists($categoryAlias, self::$categoryIds)) {
      throw new UnexpectedValueException(__FILE__ . ': Unknown category alias: ' . $categoryAlias);
    }

    return self::$categoryIds[$categoryAlias];
  }

  /**
   * Given a category id, return its category alias
   * @param int Category ID
   * @return bool|string Category alias in Event Booking (false if not found)
   */
  public static function getCategoryAlias(int $categoryId): bool|string
  {
    if (self::$categoryIds == null) self::cacheCategoryAliases();

    return array_search($categoryId, self::$categoryIds);
  }

  /**
   * Given a list of category aliases, returns array of their ids
   * @param array $categoryAliases Optional list of specific category ids to return
   * @return array Array of category ids
   */
  public static function getCategoryIds(array $categoryAliases, bool $associative = false): array
  {
    if (self::$categoryIds == null) self::cacheCategoryAliases();

    if (count($categoryAliases) == 0) die('List of aliases must be provided');

    $result = [];

    foreach ($categoryAliases as $c) {
      $cid = self::getCategoryId($c);

      if ($associative) {
        $result[$c] = $cid;
      } else {
        $result[] = $cid;
      }
    }

    return $result;
  }

  public static function getRawCategories(array $categoryIds): ?array
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);
    $query->select('*')
      ->from($db->qn('#__eb_categories'))
      ->where($db->qn('id') . ' IN (' . implode(',', (array)($db->q($categoryIds))) . ')');
    $db->setQuery($query);
    $rows = $db->loadObjectList('id');

    return $rows;
  }

  /**
   * Converts field alias to its id
   * @param string $fieldName Field alias
   * @return int Field ID
   */
  public static function getFieldId(string $fieldName): int
  {
    $fieldName = trim($fieldName);

    if ('' == $fieldName) die(__FILE__ . ': field name cannot be blank');

    if (null == self::$fieldIds) self::cacheFieldIds();

    if (array_key_exists($fieldName, self::$fieldIds)) {
      return intval(self::$fieldIds[$fieldName]->id);
    } else {
      throw new UnexpectedValueException(__FILE__ . ': Unknown field name: ' . $fieldName);
    }
  }

  /**
   * Returns the raw database row for an event
   * @param int $event_id The event row ID
   * @return object Database row as object or null on error
   */
  public static function loadEventRow(int $event_id): ?object
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $q = $db->getQuery(true);

    $q->select('*')
      ->from('#__eb_events')
      ->where($db->qn('id') . '=' . $db->q($event_id));
    $db->setQuery($q);
    return $db->loadObject();
  }

  public static function eventIdtoAlias(int $eventId): bool|string
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');
    $query = $db->getQuery(true);

    $query->select('alias')
      ->from('#__claw_eventid_mapping')
      ->where('eventid = :eventid')
      ->bind(':eventid', $eventId);
    $db->setQuery($query);
    $result = $db->loadResult();

    if (null == $result) {
      Ebmgmt::rebuildEventIdMapping();
    } else {
      return $result;
    }

    $db->setQuery($query);
    $result = $db->loadResult();

    return $result == null ? false : $result;
  }

  private static function cacheEventAliases(): void
  {
    if (self::$eventIds != null) return;

    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');
    $query = $db->getQuery(true);
    $query->select('alias,id')
      ->from('#__eb_events')
      ->where('published=1')
      ->order('id');
    $db->setQuery($query);
    self::$eventIds = $db->loadObjectList('alias');

    if (self::$eventIds == null) {
      throw new UnexpectedValueException(__FILE__ . ': Event IDs db error.');
    }
  }

  private static function cacheCategoryAliases(): void
  {
    if (self::$categoryIds != null) return;
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);
    $query->select('alias,id')
      ->from('#__eb_categories')
      ->where('published=1')
      ->order('id');
    $db->setQuery($query);
    self::$categoryIds = $db->loadAssocList('alias', 'id');

    if (self::$categoryIds == null) {
      throw new UnexpectedValueException(__FILE__ . ': Category alias db error.');
    }
  }

  private static function cacheFieldIds(): void
  {
    if (self::$fieldIds != null) return;
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);
    $query->select('name,id')
      ->from('#__eb_fields')
      ->where('published=1')
      ->order('id');

    $db->setQuery($query);
    self::$fieldIds = $db->loadObjectList('name');

    if (self::$fieldIds == null) {
      throw new UnexpectedValueException(__FILE__ . ': Field IDs db error.');
    }
  }
}
