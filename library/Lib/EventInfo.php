<?php

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Enums\EbPublishedState;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Helpers\Config;

class EventInfo
{
  private static $_EventList = [];

  const startdayofweek = 1; // Monday
  
  public string $shiftPrefix = '';
  public string $description;
  public int $ebLocationId;
  public Date $start_date;
  public Date $end_date;
  public string $prefix;
  public Date $cancelBy;
  public string $timezone;
  public bool $active;
  public EventTypes $eventType;
  public bool $onsiteActive;
  public int $termsArticleId;

  public array $eb_cat_shifts;
  public array $eb_cat_supershifts;
  public array $eb_cat_speeddating;
  public array $eb_cat_equipment;
  public array $eb_cat_sponsorship;
  public array $eb_cat_sponsorships;
  public int $eb_cat_dinners;
  public int $eb_cat_brunches;
  public int $eb_cat_buffets;
  public int $eb_cat_combomeals;
  public array $eb_cat_invoicables;


  /**
   * Event info object with simple date validation if main events are allowed
   * @param object $info 
   * @param int $startdayofweek 1 (default for Monday)
   * @return void 
   */
  public function __construct(
    public readonly string $alias
  )
  {
    $config = new Config($this->alias);
    $timezone = $config->getConfigText(ConfigFieldNames::CONFIG_TIMEZONE, 'server');
    $info = $this->loadRawEventInfo($alias);

    $this->description = $info->description;
    $this->ebLocationId = $info->ebLocationId;
    $this->start_date = Factory::getDate($info->start_date, $timezone);
    $this->end_date = Factory::getDate($info->end_date, $timezone);
    $this->prefix = strtoupper($info->prefix);
    $this->cancelBy = Factory::getDate($info->cancelBy, $timezone);
    $this->timezone = $info->timezone;
    $this->active = $info->active;
    $this->eventType = EventTypes::FindValue($info->eventType);
    $this->onsiteActive = $info->onsiteActive;
    $this->termsArticleId = $info->termsArticleId;

    $this->eb_cat_shifts = json_decode($info->eb_cat_shifts ?? '[]');
    $this->eb_cat_supershifts = json_decode($info->eb_cat_supershifts ?? '[]');
    $this->eb_cat_speeddating = json_decode($info->eb_cat_speeddating ?? '[]');
    $this->eb_cat_equipment = json_decode($info->eb_cat_equipment ?? '[]');
    $this->eb_cat_sponsorship = json_decode($info->eb_cat_sponsorship ?? '[]');
    $this->eb_cat_sponsorships = json_decode($info->eb_cat_sponsorships ?? '[]');
    $this->eb_cat_dinners = $info->eb_cat_dinners ?? 0;
    $this->eb_cat_brunches = $info->eb_cat_brunches ?? 0;
    $this->eb_cat_buffets = $info->eb_cat_buffets ?? 0;
    $this->eb_cat_combomeals = $info->eb_cat_combomeals ?? 0;
    $this->eb_cat_invoicables = json_decode($info->eb_cat_invoicables ?? '[]');
  

    // Data validation

    // start_date must be a Monday, only if eventType is main
    // this allows refund and virtualclaw to exist in their odd separate way

    if ( EventTypes::main == $this->eventType ) {
      $this->shiftPrefix = strtolower($this->prefix) . '-shift-';

      if ($this->start_date->dayofweek != EventInfo::startdayofweek) {
        var_dump($this);
        die("Event Start Date Must Be: " . EventInfo::startdayofweek . '. Got: ' . $this->start_date->dayofweek);
      }

      $this->end_date->setTime(23, 59, 59);

      // Validate location exists in eventbooking
      // if ( !Locations::ValidateLocationAlias($this->ebLocationId) ) {
      //   var_dump($this);
      //   die("Location alias not found in eventbooking locations: " . $this->ebLocationId);
      // }
    }
  }

  private function loadRawEventInfo(string $alias): object
  {
    if ( empty($alias) ) throw new \Exception(__FILE__ . ': Event alias cannot be empty');
    
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');
    $alias = strtolower($alias);

    $query = $db->getQuery(true);
    $query->select('*')
      ->from('#__claw_eventinfos')
      ->where('alias = :alias')
      ->bind(':alias', $alias);
    $db->setQuery($query);
    return $db->loadObject();
  }

  /**
   * Mimics Date object functionality, returning event start date modified by the modifier
   * @param string $modifier
   * @return Date|bool Modified date 
   */
  public function modify(string $modifier ): Date|bool
  {
    // Clone because modify changes the original Date object
    $date = clone $this->start_date;

    try {
      $result = $date->modify($modifier);
    } catch (\Exception $e) {
      throw $e;
    }

    if ( $result === false  ) return false;

    // If we're not supposed to validate, then return the start date
    return $date;
  }

  /**
   * Returns array, indexed by event alias, with "active" EventInfo objects
   * @return array 
   */
  public static function getEventInfos(): array
  {
    if ( count(self::$_EventList) > 0 ) return self::$_EventList;

    $EventList = [];

    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);
    $query->select(['alias', 'description'])
      ->from('#__claw_eventinfos')
      ->where('active='.EbPublishedState::published->value)
      ->order('end_date DESC');
      
    $db->setQuery($query);
    $rows = $db->loadObjectList();

    foreach ($rows as $row) {
      $EventList[strtolower($row->alias)] = new EventInfo($row->alias);
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
    // If cached, no db lookup, return what we know
    if ( count(self::$_EventList) != 0 ) {
      return array_key_exists(strtolower($alias), self::$_EventList);
    }

    $alias = strtolower($alias);
    
    // If not cached, do db lookup
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');
    $query = $db->getQuery(true);
    $query->select(['alias', 'description'])
      ->from('#__claw_eventinfos')
      ->where('active='.EbPublishedState::published->value)
      ->where('alias = :alias')
      ->bind(':alias', $alias);

    $db->setQuery($query);
    return $db->loadResult() != null;
  }


}
