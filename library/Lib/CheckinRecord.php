<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

use ClawCorpLib\Checkin\Record;
use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EventPackageTypes;

class CheckinRecord
{
  public array $brunches = [];
  public array $buffets = [];
  public array $dinners = [];
  public bool $cocSigned = false;
  public bool $issued = false;
  public bool $leatherHeartSupport = false;
  public bool $photoAllowed = false;
  public bool $printed = false;
  public EventPackageTypes $eventPackageType = EventPackageTypes::none;
  public int $id = 0;
  public int $package_eventId = 0;
  public string $address = '';
  public string $address2 = '';
  public string $badge = '';  // Badge Name
  public string $badgeId = '';
  public string $city = '';
  public string $country = '';
  public string $dayPassDay = '';
  public string $email = '';
  public string $error = '';
  public string $info = '';
  public string $legalName = '';
  public string $pronouns = '';
  public string $registration_code = '';
  public string $staff_type = '';
  public string $shifts = '';
  public string $shirtSize = '';
  public string $state = '';
  public string $zip = '';

  // For combo meals, contains a CVS of the non-combo meal event ids
  // E.g., A combo meal might include 8 separate meal events, so the ending value
  // could look like 1,2,3,4,5,6,7,8 within the registrant record
  public array $issuedMealTickets = [];

  // For combo meals, this maps event id of the checkin meal to the event id of the combo meal
  // e.g., event_id(dinner) => event_id(combo_meals_all)
  public array $mealIssueMapping = [];

  public function __construct(
    public EventConfig $eventConfig,
    public int $uid,
  ) {
    // Keeping separate since we need to separate these out for badge printing

    /** @var \ClawCorpLib\Lib\PackageInfo  */
    foreach ($eventConfig->packageInfos as $packageInfo) {
      if ($packageInfo->published != EbPublishedState::published || $packageInfo->eventId == 0)
        continue;

      switch ($packageInfo->category) {
        case $eventConfig->eventInfo->eb_cat_dinners:
          $this->dinners[$packageInfo->eventId] = '';
          break;
        case $eventConfig->eventInfo->eb_cat_brunches:
          $this->brunches[$packageInfo->eventId] = '';
          break;
        case $eventConfig->eventInfo->eb_cat_buffets:
          $this->buffets[$packageInfo->eventId] = '';
          break;
      }
    }
  }

  public function getMealString(array $meals): string
  {
    $result = trim(implode(' ', $meals));
    return $result != '' ? $result : 'None';
  }

  /**
   * Record that is displayed upon search in the checkin and badge print interfaces
   * This is "prepared" to be all strings
   * @return Record Values from 
   */
  public function toRecord(): Record
  {
    $result = new Record();

    foreach (get_object_vars($this) as $key => $value) {
      if (property_exists($result, $key) && (is_string($this->$key) || is_bool($this->$key))) {
        $result->$key = $value;
      }
    }

    $result->buffets = $this->getMealString($this->buffets);
    $result->brunch = $this->getMealString($this->brunches);
    $result->dinner = $this->getMealString($this->dinners);

    if ($this->eventConfig->eventInfo->badgePrintingOverride) {
      $result->printed = true;
    }

    $result->clawPackage = $this->eventPackageType->toString();
    if ($this->dayPassDay != '') $result->clawPackage .= ' (' . $this->dayPassDay . ')';

    $result->shifts = '<pre>' . $result->shifts . '</pre>';

    return $result;
  }
}
