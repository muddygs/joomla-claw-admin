<?php

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use Exception;
use InvalidArgumentException;
use Joomla\CMS\Factory;
use Joomla\Database\Exception\UnsupportedAdapterException;
use Joomla\Database\Exception\QueryTypeAlreadyDefinedException;
use RuntimeException;
use Joomla\DI\Exception\KeyNotFoundException;
use UnexpectedValueException;

class EventConfig
{
  public EventInfo $eventInfo;
  public PackageInfos $packageInfos;

  // Cache of config values
  private static array $_titles = [];
  private static string $_current = '';
  private static array $_EventInfoCache = [];
  private static array $_PackageInfosCache = [];

  public const DEFAULT_FILTERS = [
    PackageInfoTypes::main,
    PackageInfoTypes::daypass,
    PackageInfoTypes::addon,
    PackageInfoTypes::passes
  ];
  
  /**
   * @param string $alias Event alias (required)
   * @param array $filter By default, only primary registration events are included
   * @return void 
   * @throws KeyNotFoundException 
   */
  public function __construct(
    public string $alias,
    public array $filter = self::DEFAULT_FILTERS
  )
  {
    if ( !EventInfo::isValidEventAlias($this->alias) ) throw(new Exception("Invalid event alias: $this->alias"));

    // TODO: Error handling
    if ( !array_key_exists($alias, self::$_EventInfoCache) ) {
      self::$_EventInfoCache[$alias] = new EventInfo($alias);
    }

    // $this->eventInfo = new EventInfo($alias);
    $this->eventInfo = self::$_EventInfoCache[$alias];

    $this->packageInfos = new PackageInfos();

    // For refunds, we need access to all active EventConfigs and their PackageInfos
    if ( $this->eventInfo->eventType == EventTypes::refunds ) {
      $this->filter = [
        PackageInfoTypes::main,
        PackageInfoTypes::daypass,
      ];

      $eventInfos = EventInfo::getEventInfos();
      $this->loadPackageInfos(array_keys($eventInfos));
    } else {
      if ( !array_key_exists($this->eventInfo->alias, self::$_PackageInfosCache) ) {
        $this->loadPackageInfos([$this->eventInfo->alias]);
        self::$_PackageInfosCache[$this->eventInfo->alias] = $this->packageInfos;
      } 

      $this->packageInfos = self::$_PackageInfosCache[$this->eventInfo->alias];
    }

  }

  private function loadPackageInfos(array $aliases = [])
  {
    $db = Factory::getContainer()->get('DatabaseDriver');
    $aliases = implode(',', (array)($db->q($aliases)));

    $query = $db->getQuery(true);

    $query->select('*')
      ->from('#__claw_packages')
      ->where('eventAlias IN ('. $aliases .')');

    if ( !empty($this->filter) ) {
      $packageInfoTypesFilter = implode(',' , array_map(fn($e) => $e->value, $this->filter));
      $query->where('packageInfoType IN (' . $packageInfoTypesFilter . ')');
    }

    $query->order('start ASC');

    $db->setQuery($query);

    $rows = $db->loadObjectList();

    if ( is_null($rows) ) return;

    foreach ( $rows as $row ) {
      $this->packageInfos[$row->id] = new PackageInfo($this->eventInfo, $row->id);
    }
  }

  /**
   * Returns the PackageInfo for a given event package type; the target event must be a main event
   * @param EventPackageTypes $packageType 
   * @return PackageInfo 
   */
  public function getMainEventByPackageType(EventPackageTypes $packageType): PackageInfo
  {
    $result = null;
    $found = 0;
    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->packageInfos as $e) {
      if ($e->eventPackageType == $packageType && ($e->packageInfoType == PackageInfoTypes::main || $e->packageInfoType == PackageInfoTypes::daypass)) {
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
   * Returns the PackageInfo object for a given EventPackageTypes enum
   * @param EventPackageTypes $packageType Event alias in Event Booking
   * @return PackageInfo PackageInfo (or null if not found)
   */
  public function getPackageInfo(EventPackageTypes $packageType): ?PackageInfo
  {
    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->packageInfos as $e) {
      if ($e->eventPackageType == $packageType ) {
        return $e;
      }
    }

    return null;
  }

  public function getMainEventIds(): array
  {
    if ( !empty($this->filter) && 
      !in_array(PackageInfoTypes::main, $this->filter) && 
      !in_array(PackageInfoTypes::daypass, $this->filter)
    ) {
      throw(new Exception('getMainEventIds() requires main and daypass in filter'));
    }

    $result = [];
    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->packageInfos as $e) {
      if ($e->packageInfoType == PackageInfoTypes::main || $e->packageInfoType == PackageInfoTypes::daypass) {
        $result[] = $e->eventId;
      }
    }

    $result = array_unique($result);
    sort($result);

    return $result;
  }

  /**
   * For the current Event, finds all non-main event ids that should require
   * a main package for registration (addons, meals, shifts, etc.)
   * @return array Event IDs
   * @throws Exception 
   * @throws UnsupportedAdapterException 
   * @throws QueryTypeAlreadyDefinedException 
   * @throws RuntimeException 
   * @throws InvalidArgumentException 
   * @throws KeyNotFoundException 
   * @throws UnexpectedValueException 
   */
  public function getMainRequiredEventIds(): array
  {
    $packageInfoTypes = [
      PackageInfoTypes::addon,
      PackageInfoTypes::combomeal,
      PackageInfoTypes::equipment,
    ];

    if ( !empty($this->filter) ) {
      throw(new Exception('EventConfig() should be constructed with [] event filter.'));
    }

    $result = [];
    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->packageInfos as $e) {
      if ( in_array($e->packageInfoType, $packageInfoTypes) ) {
        if ( $e->eventId ) $result[] = $e->eventId;
      } 
      // Speeddating is a damned mess. Need to process all the events by roles
      elseif ( $e->packageInfoType == PackageInfoTypes::speeddating ) {
        foreach ( $e->meta AS $meta ) {
          if ( $meta->eventId ) $result[] = $meta->eventId;
        }
      }
    }

    $shiftCategoryIds = [...$this->eventInfo->eb_cat_shifts, ...$this->eventInfo->eb_cat_supershifts];
    $shiftEvents = $this->getEventsByCategoryId($shiftCategoryIds, 'id');

    $eventIds = array_column($shiftEvents, 'id');
    $result = array_unique(array_merge($result, $eventIds));
    sort($result);

    return $result;
  }

  /**
   * @param string $key PackageInfo property to search under
   * @param string $value Value to find
   * @param bool $mainOnly Main events only (by default) IFF clawEvent
   * @return null|PackageInfo Event object
   */
  public function getPackageInfoByProperty(string $property, mixed $value, bool $mainOnly = true): ?PackageInfo
  {
    $result = null;
    $found = 0;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->packageInfos as $packageInfo) {
      if (!property_exists($packageInfo, $property)) die(__FILE__ . ': Unknown key requested: ' . $property);

      if ( $mainOnly && !($packageInfo->packageInfoType == PackageInfoTypes::main || $packageInfo->packageInfoType == PackageInfoTypes::daypass) ) continue;
      if ( $mainOnly && $packageInfo->couponOnly ) continue;

      if ($packageInfo->$property == $value) {
        $result = $packageInfo;
        $found++;
      }
    }

    if ($found > 1) {
      var_dump($property);
      var_dump($value);
      var_dump($this->packageInfos);
      var_dump($result);
      die('Duplicate results found. Did you load multiple events?');
    }
    return $result;
  }

  /** Returns list of event raw rows AND "total_registrants" for each event
   * @param array $categoryIds Array of category ids
   * @param string $orderBy Any valid database column for eb_events, default "title"
   * @return array Array of objects for "id" and "title" of all events sorted by (default) title
   */
  public function getEventsByCategoryId(array $categoryIds, string $orderBy = 'title'): array
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $startDate = $this->eventInfo->start_date;
    $endDate = $this->eventInfo->end_date;

    $qCategoryIds = implode(',', (array)($db->q($categoryIds)));

    $query = $db->getQuery(true);
    $query->select([
        'e.*',
        '( SELECT COUNT(*) FROM `#__eb_registrants` WHERE event_id = e.id AND published=1 ) AS `total_registrants`'
      ])
      ->from('#__eb_events e')
      ->where('main_category_id IN (' . $qCategoryIds . ')')
      ->order($db->qn($orderBy));

    if ($this->eventInfo->eventType == EventTypes::main) {
      $query->where('`event_date` >= ' . $db->q($startDate->toSql()))
        ->where('`event_end_date` <= ' . $db->q($endDate->toSql()))
        ->where('`published`=1');
    }

    $db->setQuery($query);
    return $db->loadObjectList() ?? [];
  }

#region Event Alias Initialization

public static function getTitleMapping(): array
  {
    if ( count(self::$_titles)) return self::$_titles;

    $eventList = EventInfo::getEventInfos();
    $titles = [];

    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ( $eventList AS $alias => $eventInfo ) {
      if ( $eventInfo->eventType != EventTypes::main ) continue;
      $titles[$alias] = $eventInfo->description;
    }

    self::$_titles = $titles;
    return $titles;
  }

  public static function getCurrentEventAlias(): string
  {
    if ( self::$_current != '' ) return self::$_current;

    $eventList = EventInfo::getEventInfos();

    if ( count($eventList) == 0 ) {
      die('No events found in Config::getCurrentEvent().');
    };

    $endDates = [];

    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ( $eventList AS $alias => $eventInfo ) {
      if ( $eventInfo->eventType != EventTypes::main ) continue;
      
      $endDates[$eventInfo->end_date->toSql()] = $alias;
    }

    // Find earliest event that has not ended
    
    ksort($endDates);

    $now = Factory::getDate()->toSql();

    foreach ( array_keys($endDates) AS $endDate ) {
      if ( $endDate > $now ) {
        self::$_current = $endDates[$endDate];
        break;
      }
    }

    if ( self::$_current == '' ) {
      // Failsafe-ish: Get last item in array
      self::$_current = array_pop($endDates);
    }

    if ( self::$_current == '' ) {
      die('No current event found in EventConfig::getCurrentEvent().');
    }

    return self::$_current;
  }

  public static function getActiveEventAliases(bool $mainOnly = false): array
  {
    $eventList = EventInfo::getEventInfos();
    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ( $eventList AS $alias => $eventInfo ) {
      if ( $mainOnly && $eventInfo->eventType != EventTypes::main ) {
        unset($eventList[$alias]);
      }
    }
    return array_keys($eventList);
  }



#endregion
}