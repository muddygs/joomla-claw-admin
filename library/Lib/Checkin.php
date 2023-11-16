<?php
namespace ClawCorpLib\Lib;

use Joomla\CMS\Factory;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Lib\Registrant;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\CheckinRecord;

class Checkin
{
  public CheckinRecord $r;
  private $uid;
  public bool $isValid;

  public function __construct(
    public string $registration_code, 
    public bool $errorReporting = true)
  {
    $this->uid = Registrant::getUserIdFromInvoice($registration_code);
    $this->isValid = false;

    if ( $this->uid != 0 ) {
      $this->isValid = $this->loadRecord();
    }
  }

  public function getUid()
  {
    return $this->uid;
  }

  private function loadRecord(): bool
  {
    // Relay messages
    $errors = [];
    $info = [];
    
    $fieldValues = [
      'BADGE', 'Z_BADGE_SPECIAL', 'Z_BADGE_ISSUED', 'Z_BADGE_PRINTED', 'Dinner',
      'CONDUCT_AGREEMENT', 'PHOTO_PERMISSION', 'TSHIRT', 'TSHIRT_VOL', 'Z_TICKET_SCANNED',
      'STAFF_TYPE_STAFF','STAFF_TYPE_TALENT','STAFF_TYPE_EVENT',
      'PRONOUNS'
    ];
    
    $alias = Aliases::current(true);
    $registrant = new Registrant($alias, $this->uid);
    $registrant->loadCurrentEvents();
    
    /** @var \ClawCorpLib\Lib\RegistrantRecord */
    $mainEventRegistrantRecord = null;
    $records = $registrant->records();
    
    if ( sizeof($records) ) {
      $registrant->mergeFieldValues($fieldValues);
      /** @var \ClawCorpLib\Lib\RegistrantRecord */
      $mainEventRegistrantRecord = $registrant->getMainEvent();
    }
    
    $events = new ClawEvents($alias);
    $abstractEvent = $events->getEvent();

    // Cache meal labels
    $badgeValues = [];
    /** @var \ClawCorpLib\Lib\ClawEvent */
    foreach ( $events->getEvents() AS $e ) {
      if ( $e->badgeValue != '' ) {
        $badgeValues[$e->eventId] = $e->badgeValue;
      }
    }
    
    $this->r = new CheckinRecord($events->getEvent(), $this->uid);
    // Error for no main event
    if ($mainEventRegistrantRecord == null) {
      $this->r->error = 'User does not have a registration package.';
      return false;
    }

    $event = $events->getMainEventByPackageType($mainEventRegistrantRecord->registrant->eventPackageType);

    $this->r->package_eventId = $mainEventRegistrantRecord->event->eventId;
    $this->r->id = $mainEventRegistrantRecord->registrant->id;

    $this->r->legalName = mb_convert_case($mainEventRegistrantRecord->registrant->first_name . ' ' . $mainEventRegistrantRecord->registrant->last_name, MB_CASE_TITLE);
    $this->r->city = mb_convert_case($mainEventRegistrantRecord->registrant->city, MB_CASE_TITLE);
    $this->r->address = $mainEventRegistrantRecord->registrant->address;
    $this->r->address2 = $mainEventRegistrantRecord->registrant->address2;
    $this->r->state = $mainEventRegistrantRecord->registrant->state;
    $this->r->zip = $mainEventRegistrantRecord->registrant->zip;
    $this->r->country = $mainEventRegistrantRecord->registrant->country;

    $this->r->email = mb_convert_case($mainEventRegistrantRecord->registrant->email, MB_CASE_LOWER_SIMPLE);
    $this->r->badge = $mainEventRegistrantRecord->fieldValue->BADGE;

    $x = json_decode($mainEventRegistrantRecord->fieldValue->PRONOUNS);
    if ( $x == null || in_array('Leave Blank', $x)) {
      $this->r->pronouns = '';
    } else {
      $this->r->pronouns = implode('|', $x);
    }

    $this->r->overridePackage = $mainEventRegistrantRecord->fieldValue->Z_BADGE_SPECIAL;
    $this->r->eventPackageType = $mainEventRegistrantRecord->registrant->eventPackageType;
    $this->r->badgeId = $registrant->badgeId;
    $this->r->registration_code = $mainEventRegistrantRecord->registrant->registration_code;

    if ( $this->r->overridePackage == '' ) {
      $tmpOverride = match ($this->r->eventPackageType) {
        EventPackageTypes::claw_staff => $mainEventRegistrantRecord->fieldValue->STAFF_TYPE_STAFF,
        EventPackageTypes::event_staff => $mainEventRegistrantRecord->fieldValue->STAFF_TYPE_EVENT,
        EventPackageTypes::event_talent => $mainEventRegistrantRecord->fieldValue->STAFF_TYPE_TALENT,
        default => '',
      };

      if ( $tmpOverride != '' ) $this->r->overridePackage = $tmpOverride;
    }

    $shiftCatIds = ClawEvents::getCategoryIds(Aliases::shiftCategories());
    $leatherHeartCatId = ClawEvents::getCategoryId('donations-leather-heart');

    $this->r->shifts = '';
    $shiftCount = 0;

    // Combo meals events
    $comboMeals = [];
    foreach ( [EventPackageTypes::combo_meal_1, EventPackageTypes::combo_meal_2, EventPackageTypes::combo_meal_3, EventPackageTypes::combo_meal_4] AS $comboMeal ) {
      if ( is_null($abstractEvent->getClawEvent($comboMeal)) ) continue;
      $comboMeals[] = $abstractEvent->getClawEvent($comboMeal);
    }

    /** @var \ClawCorpLib\Lib\RegistrantRecord */
    foreach ($records as $r) {
      $scannedEvents = $this->explodeTicketScanned($r->fieldValue->Z_TICKET_SCANNED);

      if ( count($scannedEvents) ) {
        $this->r->issuedMealTickets = array_merge($this->r->issuedMealTickets, $scannedEvents );
      }

      $comboCount = 0;

      foreach ( $comboMeals AS $comboMeal ) {
        if ( $r->event->eventId == $comboMeal->eventId ) {
          $comboCount++;
          
          foreach ( $comboMeal->meta AS $mealEventId ) {
            $this->r->mealIssueMapping[$mealEventId] = $comboMeal->eventId;

            if ( array_key_exists($mealEventId, $this->r->dinners) ) {
              $this->r->dinners[$mealEventId] = $r->fieldValue->Dinner;
              continue;
            }
            
            if ( array_key_exists($mealEventId, $this->r->brunches) ) {
              $this->r->brunches[$mealEventId] = $badgeValues[$mealEventId];
              continue;
            }
            
            if ( array_key_exists($mealEventId, $this->r->buffets) ) {
              $this->r->buffets[$mealEventId] = $badgeValues[$mealEventId];
              continue;
            }
          }
        }
      } // end combo meals

      // Shifts
      if (in_array($r->category->category_id, $shiftCatIds)) {
        $this->r->shifts .= $r->event->title . "\n";
        $shiftCount++;
        continue;
      }

      // Standard Meals
      if ( array_key_exists($r->event->eventId, $this->r->dinners) ) {
        $this->r->dinners[$r->event->eventId] = $r->fieldValue->Dinner;
        continue;
      }
      
      if ( array_key_exists($r->event->eventId, $this->r->brunches) ) {
        $this->r->brunches[$r->event->eventId] = $badgeValues[$r->event->eventId];
        continue;
      }
      
      if ( array_key_exists($r->event->eventId, $this->r->buffets) ) {
        $this->r->buffets[$r->event->eventId] = $badgeValues[$r->event->eventId];
        continue;
      }

      // Leather Heart Sponsorships
      if ( $r->category->category_id == $leatherHeartCatId) {
        $this->r->leatherHeartSupport = true;
        continue;
      }
    } // end foreach record

    if ( $comboCount > 1 ) {
      $error[] = 'Multiple combo meals found. This is not allowed.';
    }

    if ( $shiftCount < $event->minShifts ) {
      $errors[] = 'Minimum shifts not met.';
    }

    // ISSUED & PRINTED
    $this->r->issued = (int)$mainEventRegistrantRecord->fieldValue->Z_BADGE_ISSUED != 0 ? true : false;
    $this->r->printed = (int)$mainEventRegistrantRecord->fieldValue->Z_BADGE_PRINTED != 0 ? true : false;

    // Code of conduct
    $this->r->cocSigned = $mainEventRegistrantRecord->fieldValue->CONDUCT_AGREEMENT == '' ? false : true;
    if ($this->r->cocSigned == false ) {
      $errors[] = 'Code of Conduct not signed.';
    }

    // Photo agreement
    $this->r->photoAllowed = strcasecmp($mainEventRegistrantRecord->fieldValue->PHOTO_PERMISSION, 'yes') == 0 ? true : false;

    // T-Shirt Size
    switch ( $mainEventRegistrantRecord->registrant->eventPackageType ) {
      case EventPackageTypes::claw_staff:
      case EventPackageTypes::event_staff:
      case EventPackageTypes::event_talent:
      case EventPackageTypes::volunteer1:
      case EventPackageTypes::volunteer2:
      case EventPackageTypes::volunteer3:
      case EventPackageTypes::volunteersuper:
      case EventPackageTypes::educator:
        $this->r->shirtSize = $mainEventRegistrantRecord->fieldValue->TSHIRT_VOL;
        break;
      case EventPackageTypes::attendee:
      case EventPackageTypes::vendor_crew:
      case EventPackageTypes::vendor_crew_extra:
      case EventPackageTypes::vip:
      case EventPackageTypes::vip2:
        $this->r->shirtSize = $mainEventRegistrantRecord->fieldValue->TSHIRT;
        break;
      default:
        $this->r->shirtSize = '???';
        break;
    }
    if ( $this->r->shirtSize == '' ) $this->r->shirtSize = 'None';

    $this->r->dayPassDay = '';

    switch($this->r->package_eventId) {
      case $abstractEvent->getClawEvent(EventPackageTypes::day_pass_fri)->eventId:
        $this->r->dayPassDay = 'Fri';
        break;
      case $abstractEvent->getClawEvent(EventPackageTypes::day_pass_sat)->eventId:
        $this->r->dayPassDay = 'Sat';
        break;
      case $abstractEvent->getClawEvent(EventPackageTypes::day_pass_sun)->eventId:
        $this->r->dayPassDay = 'Sun';
        break;
    }

    if ( sizeof($info) != 0 ) {
      array_unshift($info, 'Action needed on badge:');
      $info[] = 'Please direct to Guest Services';
      $this->r->info = implode("\n", $info);
    }

    if ( !$this->r->printed) {
      $errors[] = 'Badge not printed.';
    }

    if ( sizeof($errors) != 0 && $this->errorReporting) {
      array_unshift($errors, 'Do not issue badge:');
      $errors[] = 'Please direct to Guest Services';
      $this->r->error = implode("\n", $errors);
      return false;
    } else {
      $this->r->error = implode("\n", $errors);
    }

    return true;
  }

  public function doCheckin() {
    $registrant = new Registrant(Aliases::current(true), $this->r->uid);
    $mainEvent = $registrant->getMainEvent();

    $registrant->updateFieldValues($mainEvent->registrant->id, ['Z_BADGE_ISSUED' => 1]);
  }

  public function doMarkPrinted()
  {
    $registrant = new Registrant(Aliases::current(true), $this->r->uid);
    /** @var \ClawCorpLib\Lib\RegistrantRecord */
    $mainEvent = $registrant->getMainEvent();

    $registrant->updateFieldValues($mainEvent->registrant->id, ['Z_BADGE_PRINTED' => Helpers::mtime()]);
  }

  public function doMealCheckin(int $eventId): array
  {
    if ( $eventId <= 0 ) return $this->htmlMsg('Event selection error', 'btn-dark');
    if ( $this->uid == 0 ) return $this->htmlMsg('Unknown badge number', 'btn-dark');
    if ( $this->r->error != '' ) return $this->htmlMsg($this->r->error, 'btn-dark');

    if ($this->r->issued != true) {
      return $this->htmlMsg('Badge Not Issued','btn-warning');
    }

    // Does this badge have this meal?
    $events = new ClawEvents(Aliases::current(true));

    /** @var \ClawCorpLib\Lib\ClawEvent */
    $e = $events->getEventByKey('eventId',$eventId, false);
    if (null == $e) {
      return $this->htmlMsg('Unknown event id '.$eventId.' in '.Aliases::current(true), 'btn-dark');
    }

    $ticketEventId = $eventId;
    if ( array_key_exists($eventId, $this->r->mealIssueMapping) ) $ticketEventId = $this->r->mealIssueMapping[$eventId];

    if ( array_search($eventId, $this->r->issuedMealTickets) !== false ) {
      if ( $e->eventPackageType == EventPackageTypes::dinner) {
        return $this->htmlMsg($e->description . ' ticket already issued: '. $this->r->dinners[$e->eventId], 'btn-dark');
      } else {
        return $this->htmlMsg($e->description . ' ticket already issued', 'btn-dark');
      }
    }

    switch ($e->eventPackageType) {
      case EventPackageTypes::dinner:
        $meal = strtolower($this->r->dinners[$e->eventId]);

        if ( $meal == '') {
          return $this->htmlMsg('Dinner not assigned to this badge','btn-dark');
        }

        $class = $description = '';

        $mealTypes = [
          'beef' => [
            'phrases' => ['beef'],
            'class' => 'meal-beef',
            'description' => 'Beef'
          ],
          'chicken' => [
            'phrases' => ['chicken'],
            'class' => 'meal-chicken',
            'description' => 'Chicken'
          ],
          'fish' => [
            'phrases' => ['fish', 'sea bass'],
            'class' => 'meal-fish',
            'description' => 'Fish'
          ],
          'vega' => [
            'phrases' => ['vege', 'vegan', 'ravioli'],
            'class' => 'meal-vegan',
            'description' => 'Vegetarian'
          ]
        ];

        foreach ( $mealTypes AS $info ) {
          foreach ( $info['phrases'] AS $phrase ) {
            if ( str_contains($meal, $phrase) ) {
              $description = $info['description'];
              $class = $info['class'];
              break 2;
            }
          }
        }

        if ( $description == '' ) {
            return $this->htmlMsg('Unknown meal selection: '. $meal, 'btn-dark');
        }

        $this->issueMealTicket($eventId,$ticketEventId);
        return $this->htmlMsg($description, $class);
        break;

      case EventPackageTypes::brunch_fri:
      case EventPackageTypes::brunch_sat:
      case EventPackageTypes::brunch_sun:
        if ($this->r->brunches[$e->eventId] == '') {
          return $this->htmlMsg($e->description.' not assigned to this badge', 'btn-dark');
        }

        $this->issueMealTicket($eventId,$ticketEventId);
        return $this->htmlMsg($e->description.' ticket issued for: '.$this->r->badgeId, 'btn-info');
        break;

      case EventPackageTypes::buffet_wed:
      case EventPackageTypes::buffet_thu:
      case EventPackageTypes::buffet_fri:
      case EventPackageTypes::buffet_sun:
        if ($this->r->buffets[$e->eventId] == '') {
          return $this->htmlMsg($e->description.' not assigned to this badge', 'btn-dark');
        }

        $this->issueMealTicket($eventId, $ticketEventId);
        return $this->htmlMsg($e->description.' ticket issued for: '.$this->r->badgeId, 'btn-info');
        break;

      default:
        return $this->htmlMsg(__FILE__. ': Unhandled CLAW package','btn-danger');
        break;
    }
  }

  private function htmlMsg(string $msg, string $classes): array
  {
    $msg = <<< HTML
    <div class="d-grid gap-2">
  <button class="btn btn-lg $classes" type="button">$msg</button>
</div>
HTML;

    $b = property_exists($this, 'r') ? $this->r->badgeId : 'error';

    $result = [
      'state' => 'ok',
      'badge' => $b,
      'message' => $msg
    ];

    return $result;
  }

  private function issueMealTicket(int $mealEventId, int $ticketEventId)
  {
    $registrant = new Registrant(Aliases::current(true), $this->r->uid, [$ticketEventId]);
    $registrant->loadCurrentEvents();
    $registrant->mergeFieldValues(['Z_TICKET_SCANNED']);

    $record = ($registrant->records(true))[0];

    $rowId = $record->registrant->id;
    $fieldValues = ['Z_TICKET_SCANNED' => $mealEventId];
    $registrant->updateFieldValues($rowId, $fieldValues, true);
  }

  static function search(string $search, string $page): array
  {
    $results = [];
    $byName = false;

    $e = new ClawEvents(Aliases::current(true));
    $inMainEventIds = implode(',',$e->mainEventIds);
    $prefix = $e->getClawEventInfo()->prefix;

    $issued = ClawEvents::getFieldId('Z_BADGE_ISSUED');
    $search = strtoupper($search);
    
    $db = Factory::getContainer()->get('DatabaseDriver');
  
    if ( substr($search,0,3) == $prefix ) {
      $search = substr($search,1);
    }

    $search = $db->q('%' . $search . '%');

    $query = $db->getQuery(true);
    $query->select(['r.user_id','r.registration_code','r.first_name','r.last_name','r.city','r.invoice_number'], [null, null, null, null, null, 'badgeId'])
      ->from($db->qn('#__eb_registrants', 'r'))
      ->join('LEFT OUTER', $db->qn('#__eb_field_values', 'v'). ' ON '. 
        $db->qn('v.registrant_id') .' = '. $db->qn('r.id'). ' AND ' . $db->qn('v.field_id'). '=' . $db->q($issued))
      ->where('r.published = 1')
      ->where('(r.invoice_number LIKE '.$search. ' OR r.last_name LIKE '.$search.')')
      ->where('r.event_id IN ('.$inMainEventIds.')')
      ->order('r.first_name')
      ->setLimit(20);

    if ( 'badge-print' != $page ) {
      $query->where('(v.field_value IS NULL OR v.field_value != 1)');
    }

    $db->setQuery($query);
    $rows = $db->loadObjectList();

    foreach ( $rows AS $r )
    {
      $badge = $prefix . '-' . str_pad($r->user_id, 5, '0', STR_PAD_LEFT);

      $name = mb_convert_case($r->first_name . ' ' . $r->last_name . ' (' . $r->city . ')', MB_CASE_TITLE);
      $description = $byName ? $name.' - '.$badge : $badge.' '.$name;
      $results[] = [
        'id' => $r->registration_code,
        'name' => $description
      ];
    }

    return $results;
  }

  private function explodeTicketScanned(string $field): array
  {
    $field = trim($field);
    return 0 == $field ? [] : explode(',',$field);
  }

  static function getUnprintedBadgeCount(): int 
  {
    return count(Checkin::getUnprintedBadges());
  }

  /**
   * Gets an array (indexed by reg row id) of registration_code for unprinted badges
   * TODO: also check on addons for badge changes
   * @param int $limit Maximum entries to return (default is all)
   * @return array registration_codes array
   */
  static function getUnprintedBadges(int $limit = 0 ): array
  {
    $badgeFieldId = ClawEvents::getFieldId('Z_BADGE_PRINTED');
    $published = EbPublishedState::published->value;

    $db = Factory::getContainer()->get('DatabaseDriver');

    $events = new clawEvents(Aliases::current(true));

    $mainEvents = $events->mainEventIds;

    $mainEventIds = implode(',',$mainEvents);

    $query = $db->getQuery(true);
    $query->select('r.id, r.registration_code')
      ->from('#__eb_registrants r')
      ->leftJoin('#__eb_field_values v ON v.registrant_id=r.id AND v.field_id='.$badgeFieldId)
      ->where('published = '.$published)
      ->where('event_id IN ('.$mainEventIds.')')
      ->where('(r.ts_modified > v.field_value OR v.id IS NULL )')
      ->order('r.invoice_number');

    if ( $limit ) {
      $query .= " LIMIT $limit";
    }

    $db->setQuery($query);
    $rows = $db->loadAssocList('id','registration_code');
    return $rows;
  }
}
