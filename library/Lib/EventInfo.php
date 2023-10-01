<?php

namespace ClawCorpLib\Lib;

use Joomla\CMS\Factory;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Helpers\Locations;

class EventInfo
{
  const startdayofweek = 1; // Monday
  /**
   * Event info object with simple date validation if main events are allowed
   * @param object $info 
   * @param int $startdayofweek 1 (default for Monday)
   * @return void 
   */
  public function __construct(
    public string $description,
    public string $location,
    public string $locationAlias,
    public string $start_date,
    public string $end_date,
    public string $prefix,
    public string $shiftPrefix,
    public bool $mainAllowed,
    public string $cancelBy,
    public string $timezone,
    public bool $active,
    public EventTypes $eventType,
    public bool $onsiteActive,
    public int $termsArticleId
  )
  {
    // Data validation

    // start_date must be a Monday, only if main event process is enabled
    // this allows refund and virtualclaw to exist in their odd separate way

    if ($this->mainAllowed) {
      $date = Factory::getDate($this->start_date);
      if ($date->dayofweek != EventInfo::startdayofweek) {
        var_dump($this);
        die("Event Start Date Must Be: " . EventInfo::startdayofweek . '. Got: ' . $date->dayofweek);
      }

      $enddate = $date->modify($this->end_date);
      $enddate->setTime(23, 59, 59);
      $this->end_date = $enddate->toSql();

      // Validate location exists in eventbooking
      if ( !Locations::ValidateLocationAlias($this->locationAlias) ) {
        var_dump($this);
        die("Location alias not found in eventbooking locations: " . $this->locationAlias);
      }
    }
  }

  /**
   * Mimics Date object functionality, returning SQL-formatted result relative to event start date
   * @return string Modified date in SQL format
   */
  public function modify(string $modifier, bool $validate = true): string|bool
  {
    $date = Factory::getDate($this->start_date);

    try {
      $modifier = $date->modify($modifier);
    } catch (\Exception $e) {
      if ($validate == false) return false;
      throw $e;
    }

    if (!is_bool($modifier))
      return $date->modify($modifier)->toSql();

    if ($validate == false) return false;
  }

  /**
   * Get the Joomla Date object of the event start date
   * @return Date 
   */
  public function getDate(): \Joomla\CMS\Date\Date
  {
    return Factory::getDate($this->start_date);
  }
}
