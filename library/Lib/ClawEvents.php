<?php

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EventPackageTypes;
use Joomla\CMS\Factory;

use ClawCorpLib\Events\AbstractEvent;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Lib\EventInfo;;

use ClawCorpLib\Lib\Aliases;
use ReflectionClass;
use ReflectionException;
use UnexpectedValueException;

\defined('_JEXEC') or die;

class ClawEvents
{
  public array $mainEventIds = [];
  private array  $couponRequired = [];
  private array $overlapEventCategories = [];
  private array $shiftCategoryIds = [];

  private AbstractEvent $event;

  private static $eventIds = null;
  private static $categoryIds = null;
  private static $fieldIds = null;
  private static $_EventList = [];

  public function __construct(string $clawEventAlias)
  {
    if (!in_array($clawEventAlias, Aliases::active())) {
      die(__FILE__ . ': Invalid event request: ' . $clawEventAlias);
    }

    self::mapEventAliases();
    self::mapCategoryAliases();
    self::mapFieldIds();

    // $this->clawEventAlias = $clawEventAlias;

    if ($clawEventAlias != 'refunds') {
      $classname = "\\ClawCorpLib\\Events\\$clawEventAlias";
      $this->event = new $classname($clawEventAlias);
    } else {
      $this->defineHistoricEventMapping();
    }

    if ($this->event->getInfo()->eventType == EventTypes::main) {
      foreach ($this->event->getEvents() as $o) {
        if ($o->isMainEvent) $this->mainEventIds[] = $o->eventId;
        if ($o->requiresCoupon) $this->couponRequired[] = $o->eventId;
      }
    }

    $this->mainEventIds = array_unique($this->mainEventIds);
    sort($this->mainEventIds);
    $this->couponRequired = array_unique($this->couponRequired);


    foreach (Aliases::overlapCategories() as $v) {
      $this->overlapEventCategories[] = self::$categoryIds[$v]->id;
    }

    foreach (Aliases::shiftCategories() as $v) {
      $this->shiftCategoryIds[] = self::$categoryIds[$v]->id;
    }
  }

  public function getEvent()
  {
    return $this->event;
  }

  /**
   * @param string $key ClawEvent property to search under
   * @param string $value Value to find
   * @param bool $mainOnly Main events only (by default) IFF clawEvent
   * @return null|object Event object (ClawEvent)
   */
  public function getEventByKey(string $key, mixed $value, bool $mainOnly = true): ?\ClawCorpLib\Lib\ClawEvent
  {
    $result = null;
    $found = 0;

    foreach ($this->event->getEvents() as $e) {
      if (!property_exists($e, $key)) die(__FILE__ . ': Unknown key requested: ' . $key);

      if ($mainOnly && !$e->isMainEvent) continue;
      if ( $e->couponOnly ) continue;

      if ($e->$key == $value) {
        $result = $e;
        $found++;
      }
    }

    if ($found > 1) {
      var_dump($result);
      die('Duplicate results found. Did you load multiple events?');
    }
    return $result;
  }

  /**
   * Returns the clawEvent for a given coupon prefix (e.g., A = attendee event )
   * @param string $couponCode Coupon Prefix Letter
   * @return null|clawEvent 
   */
  public function getEventByCouponCode(string $couponCode, bool $quiet=false): ?ClawEvent
  {
    $result = null;
    $found = 0;
    foreach ($this->event->getEvents() as $e) {
      if ($e->couponKey == $couponCode) {
        $result = $e;
        $found++;
      }
    }

    if ( !$quiet) {
      if ($found > 1) die('Duplicate coupon codes loaded. Did you load multiple events?');
      if (0 == $found) die('Unknown coupon code requested: ' . $couponCode);
    }

    return $result;
  }

  /**
   * Returns the ClawEvent for a given event package type; the target event must be a main event
   * @param EventPackageTypes $packageType 
   * @return ClawEvent 
   */
  public function getMainEventByPackageType(EventPackageTypes $packageType): ClawEvent
  {
    $result = null;
    $found = 0;
    /** @var \ClawCorpLib\Lib\ClawEvent */
    foreach ($this->event->getEvents() as $e) {
      if ($e->eventPackageType == $packageType && $e->isMainEvent) {
        $result = $e;
        $found++;
      }
    }

    if ($found > 1) die('Duplicate package types loaded. Did you load multiple events?');
    if (0 == $found) {
      die('Unconfigured package type requested: ' . $packageType->name);
    }

    return $result;
  }

  /**
   * Unlike getMainEventByPackageType, this returns all events for a given package type,
   * regardless of whether it is a main event
   * @param EventPackageTypes $packageType 
   * @return array ClawEvent[]
   */
  public function getEventsByPackageType(EventPackageTypes $packageType): array
  {
    $result = [];

    /** @var \ClawCorpLib\Lib\ClawEvent */
    foreach ($this->event->getEvents() as $e) {
      if ($e->eventPackageType == $packageType) {
        $result[] = $e;
      }
    }

    return $result;
  }

  /**
   * Returns an array of all the enrolled events in this class when initialized
   * @return array List of event IDs
   */
  public function getEventIds(bool $mainOnly = false): array
  {
    if ( !$mainOnly) return $this->event->getEventIds();

    $result = [];
    foreach ($this->event->getEvents() as $e) {
      if ($e->isMainEvent) $result[] = $e->eventId;
    }
    return $result;
  }

  /**
   * Returns the loaded event info object
   * @return EventInfo 
   */
  public function getClawEventInfo(): EventInfo
  {
    return $this->event->getInfo();
  }

  /**
   * Returns list of loaded events
   */
  public function getEvents(): array
  {
    return $this->event->getEvents();
  }

  /**
   * Provides mapping of event alias to event id
   * @return array Alias to event id mapping
   */
  // public static function getEventIds(): array {
  //     if ( self::$eventIds == null ) self::mapEventAliases();
  //     return self::$eventIds;
  // }

  /**
   * Converts event alias to its id
   * @param string $eventAlias Event alias in Event Booking
   * @param bool $quiet Quietly return 0 if alias does not exist
   * @return int Event ID
   * @deprecated Use AbstractEvent->getEventId() instead
   */
  public static function getEventId(string $eventAlias, bool $quiet = false): int
  {
    $eventAlias = strtolower(trim($eventAlias));

    if ('' == $eventAlias) die(__FILE__ . ': event alias cannot be blank');

    if (null == self::$eventIds) self::mapEventAliases();

    if (array_key_exists($eventAlias, self::$eventIds)) {
      return intval(self::$eventIds[$eventAlias]->id);
    } else {
      if ($quiet) return 0;
      throw new UnexpectedValueException(__FILE__ . ': Unknown eventAlias: ' . $eventAlias);
    }
  }

  /**
   * Converts event alias to its id
   * @param string $eventAlias Event alias in Event Booking
   * @param bool $quiet Quietly return 0 if alias does not exist
   * @return int Event ID
   */
  public static function getEventIdByAlias(string $eventAlias, bool $quiet = false): int
  {
    $eventAlias = strtolower(trim($eventAlias));

    if ('' == $eventAlias) die(__FILE__ . ': event alias cannot be blank');

    if (null == self::$eventIds) self::mapEventAliases();

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
    if (self::$categoryIds == null) self::mapCategoryAliases();
    if (!array_key_exists($categoryAlias, self::$categoryIds)) {
      die(__FILE__ . ": Unknown category $categoryAlias");
    }

    return self::$categoryIds[$categoryAlias]->id;
  }

  /**
   * Given a list of category aliases, returns array of their ids
   * @param array $categoryAliases Optional list of specific category ids to return
   * @return array Array of category ids
   */
  public static function getCategoryIds(array $categoryAliases, bool $associative = false): array
  {
    if (self::$categoryIds == null) self::mapCategoryAliases();

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


  /** Returns list of event raw rows AND "total_registrants" for each event
   * @param array $categoryIds Array of category ids
   * @param string $orderBy Any valid database column for eb_events, default "title"
   * @return array Array of objects for "id" and "title" of all events sorted by title
   */
  public static function getEventsByCategoryId(array $categoryIds, EventInfo $clawEventInfo, string $orderBy = 'title'): array
  {
    $db = Factory::getContainer()->get('DatabaseDriver');

    $startDate = $clawEventInfo->start_date;
    $endDate = $clawEventInfo->end_date;

    $qCategoryIds = implode(',', $db->q($categoryIds));

    $query = <<<SQL
        SELECT e.*,
        ( SELECT COUNT(*) FROM `#__eb_registrants` WHERE event_id = e.id AND published=1 ) AS `total_registrants`
        FROM #__eb_events e
        WHERE main_category_id IN ($qCategoryIds)
SQL;

    if ($clawEventInfo->mainAllowed == true) {
      $query .= ' AND `event_date` > ' . $db->q($startDate);
      $query .= ' AND `event_end_date` < ' . $db->q($endDate);
      $query .= ' AND `published`=1';
    }

    $query .= ' ORDER BY ' . $db->qn($orderBy);

    $db->setQuery($query);
    $rows = $db->loadObjectList();

    return $rows;
  }

  public static function getCategoryNames(array $categoryAliases): ?array
  {
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);
    $query->select('*')
      ->from($db->qn('#__eb_categories'))
      ->where($db->qn('alias') . ' IN (' . implode(',', $db->q($categoryAliases)) . ')');
    $db->setQuery($query);
    $rows = $db->loadObjectList('alias');

    return $rows;
  }

  /**
   * Returns fields ids for array of array aliases
   * @param array $fieldNames Array of field aliases
   * @return array Corresponding field ids
   */
  public static function getFieldIds(array $fieldNames): array
  {
    if (count($fieldNames) == 0) die(__FILE__ . ': field name array cannot be blank');

    $results = [];

    foreach ($fieldNames as $f) {
      $results[] = self::getFieldId($f);
    }

    return $results;
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

    if (null == self::$fieldIds) self::mapFieldIds();

    if (array_key_exists($fieldName, self::$fieldIds)) {
      return intval(self::$fieldIds[$fieldName]->id);
    } else {
      die(__FILE__ . ': field name unknown: ' . $fieldName);
    }
  }

  /**
   * Returns the raw database row for an event
   * @param int $event_id The event row ID
   * @return object Database row as object or null on error
   */
  public static function loadEventRow(int $event_id): ?object
  {
    $db = Factory::getContainer()->get('DatabaseDriver');

    $q = $db->getQuery(true);

    $q->select('*')
      ->from('#__eb_events')
      ->where($db->qn('id') . '=' . $db->q($event_id));
    $db->setQuery($q);
    return $db->loadObject();
  }

  public static function getEventIdMapping(int $eventId): bool|string
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
    return $result;
  }

  /**
   * Given an event ID, returns the alias that includes that event, except if mainAllowed is false,
   * which does not make sense in this context in order to return specific event
   * @param int $eventId The event ID
   * @return string Event Aliases or false if not found
   */
  public static function eventIdToClawEventAlias(int $eventId): string|bool
  {
    $alias = self::getEventIdMapping($eventId);

    // Rebuild and try again
    if ( $alias === false ) {
      Ebmgmt::rebuildEventIdMapping();
      $alias = self::getEventIdMapping($eventId);
    } else {
      return $alias;
    }

    if ( $alias !== false ) return $alias;

    // TODO: Brute force search - still needed?
  
    $activeAliases = Config::getActiveEventAliases(mainOnly: true);

    foreach ($activeAliases AS $alias) {
      $classname = "\\ClawCorpLib\\Events\\$alias";
      /** @var \ClawCorpLib\Events\AbstractEvent */
      $eventlib = new $classname($alias);
      $info = $eventlib->getInfo();

      // Specific -- failover to date (might not need this loop)
      foreach ($eventlib->getEvents() as $e) {
        if ($e->eventId == $eventId) return $alias;
      }

      // Now try to match on date
      $event = self::loadEventRow($eventId);
      if ($event->event_date >= $info->start_date  && $event->event_end_date <= $info->end_date) {
        return $alias;
      }
    }

    return false;
  }

  /**
   * This is the "raw" search for event aliases that parses filenames in the Events library directory
   * @return array 
   * @throws ReflectionException 
   */
  public static function GetEventList(): array
  {
    if ( count(self::$_EventList) > 0 ) return self::$_EventList;

    $EventList = [];

    // Load directory of event classes
    $dir = JPATH_LIBRARIES . '/claw/Events';
    $files = scandir($dir);
    if ($files === false) return [];

    $files = preg_grep('/^([^.])/', $files);
    $files = preg_grep('/\.php$/', $files);

    foreach ( $files AS $file ) {
      // Basename of file is the class
      $class = basename($file, '.php');

      // Ignore known AbstractEvent
      if ( $class == 'AbstractEvent' ) continue;

      $classname = "\\ClawCorpLib\\Events\\$class";

      $reflection = new ReflectionClass($classname);
      /** @var \ClawCorpLib\Event\AbstractEvent */
      $instance = $reflection->newInstanceWithoutConstructor();
      $info = $instance->PopulateInfo();
      if ( !$info->active ) continue;

      $EventList[$class] = $info;
    }

    self::$_EventList = $EventList;
    return $EventList;
  }

  /**
   * Validates if the event alias is from a valid and active event
   * @param string $alias 
   * @return bool 
   * @throws ReflectionException 
   */
  public static function isValidEventAlias(string $alias): bool
  {
    $EventList = self::GetEventList();
    return array_key_exists($alias, $EventList);
  }

  /**
   * This is a special case, used only for refunds, to identify all events that
   * pulls in all "active" events and merges them into a meta event
   */
  private function defineHistoricEventMapping(): void
  {
    $classname = "\\ClawCorpLib\\Events\\refunds";
    $this->event = new $classname('refunds');

    foreach(Aliases::active() AS $alias ) {
      if ( $alias == 'refunds' ) continue;

      $classname = "\\ClawCorpLib\\Events\\$alias";
      $tmp = new $classname($alias);
      $this->event->mergeEvents($tmp->getEvents());
    }
  }

  private static function mapEventAliases(): void
  {
    if (self::$eventIds != null) return;

    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = 'SELECT alias,id FROM #__eb_events WHERE published=1 ORDER BY id';
    $db->setQuery($query);
    self::$eventIds = $db->loadObjectList('alias');

    if (self::$eventIds == null) die('Event alias db error.');
  }

  private static function mapCategoryAliases(): void
  {
    if (self::$categoryIds != null) return;
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = 'SELECT alias,id FROM #__eb_categories WHERE published=1 ORDER BY id';
    $db->setQuery($query);
    self::$categoryIds = $db->loadObjectList('alias');

    if (self::$categoryIds == null) die('Category alias db error.');
  }

  private static function mapFieldIds(): void
  {
    if (self::$fieldIds != null) return;
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = 'SELECT `name`,`id` FROM #__eb_fields WHERE published=1 ORDER BY id';
    $db->setQuery($query);
    self::$fieldIds = $db->loadObjectList('name');

    if (self::$fieldIds == null) die('Field IDs db error.');
  }

  public static function eventAliasToTitle(string $eventAlias): string
  {
    $titleList = Config::getTitleMapping();
    return array_key_exists($eventAlias, $titleList) ? $titleList[$eventAlias] : $eventAlias;
  }

  /**
   * Converts a location alias to the location id
   * @param string $locationAlias Location alias
   * @return int Location ID
   */
  public static function getLocationId(string $locationAlias): int
  {
    $db = Factory::getContainer()->get('DatabaseDriver');
    $query = 'SELECT `id` FROM #__eb_locations WHERE alias = ' . $db->q($locationAlias);
    $db->setQuery($query);
    $result = $db->loadResult();
    return (int)$result;
  }

  public function dump(): void
  {
    echo "<pre>*** FIELD IDS\n";
    foreach (self::$fieldIds as $x) {
      echo $x->name . ',', $x->id . "\n";
    }
    echo "*** EVENTS IDS\n";
    foreach (self::$eventIds as $x) {
      echo $x->alias . ',', $x->id . "\n";
    }
    echo '</pre>';
  }
}
