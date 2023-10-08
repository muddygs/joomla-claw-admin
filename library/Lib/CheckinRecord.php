<?php

namespace ClawCorpLib\Lib;

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
  public int $clawPackage = 0;
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
  public string $overridePackage = '';
  public string $pronouns = '';
  public string $registration_code = '';
  public string $shifts = '';
  public string $shirtSize = '';
  public string $state = '';
  public string $zip = '';

  // Fpr combo meals, contains a CVS of the non-combo meal event ids
  // E.g., At CLAW 22, the VIP included 8 separate meal events, so the ending value
  // could look like 1,2,3,4,5,6,7,8 within the registrant record
  public array $issuedMealTickets = [];

  // For combo meals, this maps event id of the checkin meal to the event id of the combo meal
  // e.g., event_id(dinner) => event_id(vip)
  public array $mealIssueMapping = [];

  public function __construct(
    public int $uid, // *** Only required parameter *** 
  )
  {
    // Initialize key ordering
    // Keeping separate since we need to separate these out for badge printing

    $dinners = [ 
      EventPackageTypes::dinner->value
    ];

    foreach ( $dinners AS $b ) {
      if ( !array_key_exists($b, $this->dinners)) $this->dinners[$b] = '';
    }

    $brunchTypes = [
      EventPackageTypes::brunch_fri->value,
      EventPackageTypes::brunch_sat->value,
      EventPackageTypes::brunch_sun->value
    ];

    foreach ( $brunchTypes as $b ) {
      if ( !array_key_exists($b, $this->brunches)) $this->brunches[$b] = '';
    }

    $buffets = [
      EventPackageTypes::buffet_wed->value,
      EventPackageTypes::buffet_thu->value,
      EventPackageTypes::buffet_fri->value,
      EventPackageTypes::buffet_sun->value
    ];

    foreach ( $buffets AS $b ) {
      if ( !array_key_exists($b, $this->buffets)) $this->buffets[$b] = '';
    }
  }

  public function getDinnerString(): string
  {
    $result = trim(implode(' ', $this->dinners));
    return $result != '' ? $result : 'None';
  }

  public function getBuffetString(): string
  {
    $result = trim(implode(' ', $this->buffets));
    return $result != '' ? $result : 'None';
  }

  public function getBrunchString(): string
  {
    $result = trim(implode(' ', $this->brunches));
    return $result != '' ? $result : 'None';
  }

  /**
   * Object expected by checkin_events.ts
   */
  public function toObject(): object
  {
    $result = (object)[];

    foreach (get_object_vars($this) as $key => $value) {
      $result->$key = $value;
    }

    $result->buffets = $this->getBuffetString();
    $result->brunch = $this->getBrunchString();
    $result->dinner = $this->getDinnerString();

    $result->issued = $this->issued ? 'Issued' : 'New';
    $result->printed = $this->printed ? 'Printed' : 'Need to Print';

    $package = EventPackageTypes::FindValue($this->clawPackage);

    $result->clawPackage = $this->overridePackage == '' ? $package->toString() : $this->overridePackage;
    if ( $this->dayPassDay != '' ) $result->clawPackage .= ' ('.$this->dayPassDay.')';

    return $result;
  }
}
