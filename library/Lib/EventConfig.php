<?php

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use Exception;
use Joomla\CMS\Factory;

class EventConfig
{
  public EventInfo $eventInfo;
  public PackageInfos $packageInfos;

  /**
   * @param string $alias Event alias (required)
   * @param array $filter By default, only primary registration events are included
   * @return void 
   * @throws KeyNotFoundException 
   */
  public function __construct(
    public string $alias,
    public array $filter = [
      PackageInfoTypes::main,
      PackageInfoTypes::daypass,
      PackageInfoTypes::addon,
      PackageInfoTypes::passes
    ]
  )
  {
    if ( !EventInfo::isValidEventAlias($this->alias) ) throw(new Exception("Invalid event alias: $this->alias"));

    // TODO: Error handling
    $this->eventInfo = new EventInfo($alias);
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
      $this->loadPackageInfos([$this->eventInfo->alias]);
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

  public function getMainRequiredEventIds(): array
  {
    $packageInfoTypes = [
      PackageInfoTypes::addon,
      PackageInfoTypes::combomeal,
      PackageInfoTypes::equipment,
      PackageInfoTypes::speeddating, // Last entry
    ];

    if ( !empty($this->filter) &&
      count(array_intersect($this->filter, $packageInfoTypes)) != count($packageInfoTypes)
    ) {
      throw(new Exception('getMainEventIds() requires main and daypass in filter'));
    }

    // TODO: Speeddating is a damned mess. Need to process all the events by roles
    array_pop($packageInfoTypes);

    $result = [];
    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->packageInfos as $e) {
      if ( in_array($e->packageInfoType, $packageInfoTypes) ) {
        if ( $e->eventId ) $result[] = $e->eventId;
      } elseif ( $e->packageInfoType == PackageInfoTypes::speeddating ) {
        foreach ( $e->meta AS $meta ) {
          if ( $meta->eventId ) $result[] = $meta->eventId;
        }
      }
    }

    $result = array_unique($result);
    sort($result);

    return $result;
  }

  public function getEventByCouponCode(string $couponCode): ?PackageInfo
  {
    $result = null;
    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->packageInfos as $e) {
      if ($e->couponKey == $couponCode) {
        $result = $e;
        break;
      }
    }

    return $result;
  }

  /**
   * @param string $key ClawEvent property to search under
   * @param string $value Value to find
   * @param bool $mainOnly Main events only (by default) IFF clawEvent
   * @return null|object Event object (ClawEvent)
   */
  public function getPackageInfoByProperty(string $property, mixed $value, bool $mainOnly = true): ?PackageInfo
  {
    $result = null;
    $found = 0;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->packageInfos as $e) {
      if (!property_exists($e, $property)) die(__FILE__ . ': Unknown key requested: ' . $property);

      if ( $mainOnly && !($e->packageInfoType == PackageInfoTypes::main || $e->packageInfoType == PackageInfoTypes::daypass) ) continue;
      if ( $e->couponOnly ) continue;

      if ($e->$property == $value) {
        $result = $e;
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

    $query = <<<SQL
        SELECT e.*,
        ( SELECT COUNT(*) FROM `#__eb_registrants` WHERE event_id = e.id AND published=1 ) AS `total_registrants`
        FROM #__eb_events e
        WHERE main_category_id IN ($qCategoryIds)
SQL;

    if ($this->eventInfo->eventType == EventTypes::main) {
      $query .= ' AND `event_date` >= ' . $db->q($startDate->toSql());
      $query .= ' AND `event_end_date` <= ' . $db->q($endDate->toSql());
      $query .= ' AND `published`=1';
    }

    $query .= ' ORDER BY ' . $db->qn($orderBy);

    $db->setQuery($query);
    $rows = $db->loadObjectList();

    return $rows;
  }
}