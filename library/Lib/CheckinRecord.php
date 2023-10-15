<?php

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Events\AbstractEvent;

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
  public string $overridePackage = '';
  public string $pronouns = '';
  public string $registration_code = '';
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
    public AbstractEvent $abstractEvent,
    public int $uid, 
  )
  {
    // Initialize key ordering based on the implemented AbstractEvent ordering
    // Keeping separate since we need to separate these out for badge printing

    $dinnerCatId = ClawEvents::getCategoryId('dinner');
    $brunchCatId = ClawEvents::getCategoryId('buffet-breakfast');
    $buffetCatId = ClawEvents::getCategoryId('buffet');

    /** @var \ClawCorpLib\Lib\ClawEvent  */
    foreach ( $abstractEvent->getEvents() AS $clawEvent) {
      switch ($clawEvent->category) {
        case $dinnerCatId:
          $this->dinners[$clawEvent->eventId] = '';
          break;
        case $brunchCatId:
          $this->brunches[$clawEvent->eventId] = '';
          break;
        case $buffetCatId:
          $this->buffets[$clawEvent->eventId] = '';
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
   * Object expected by checkin_events.ts
   */
  public function toObject(): object
  {
    $result = (object)[];

    foreach (get_object_vars($this) as $key => $value) {
      $result->$key = $value;
    }

    $result->buffets = $this->getMealString($this->buffets);
    $result->brunch = $this->getMealString($this->brunches);
    $result->dinner = $this->getMealString($this->dinners);

    $result->issued = $this->issued ? 'Issued' : 'New';
    $result->printed = $this->printed ? 'Printed' : 'Need to Print';

    $result->clawPackage = $this->overridePackage == '' ? $this->eventPackageType->toString() : $this->overridePackage;
    if ( $this->dayPassDay != '' ) $result->clawPackage .= ' ('.$this->dayPassDay.')';

    return $result;
  }
}
