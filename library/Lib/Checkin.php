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

  public function __construct(string $registration_code)
  {
    $this->uid = Registrant::getUserIdFromInvoice($registration_code);
    $this->isValid = false;

    if ( $this->uid != 0 ) {
      $this->isValid = $this->loadRecord();
    }
  }

  private function loadRecord(): bool
  {
    // Relay messages
    $errors = [];
    $info = [];

    $this->r = new CheckinRecord($this->uid);

    $fieldValues = [
      'BADGE', 'Z_BADGE_SPECIAL', 'Z_BADGE_ISSUED', 'Z_BADGE_PRINTED', 'Dinner',
      'CONDUCT_AGREEMENT', 'PHOTO_PERMISSION', 'TSHIRT', 'TSHIRT_VOL', 'Z_TICKET_SCANNED',
      'STAFF_TYPE_STAFF','STAFF_TYPE_TALENT','STAFF_TYPE_EVENT',
      'PRONOUNS'
    ];

    $registrant = new Registrant(Aliases::current(), $this->uid);
    $registrant->loadCurrentEvents();
    $mainEvent = null;

    if ( sizeof($registrant->records()) > 0 ) {
      $registrant->mergeFieldValues($fieldValues);
      $mainEvent = $registrant->getMainEvent();
    }

    // Error for no main event
    if ($mainEvent == null) {
      $this->r->error = 'User does not have a registration package.';
      return false;
    }

    $events = new ClawEvents(Aliases::current());
    $eventInfo = $events->getClawEventInfo();
    $prefix = strtolower($eventInfo->prefix).'-';

    $event = $events->getEventByPackageType($mainEvent->registrant->eventPackageType);

    $this->r->package_eventId = $mainEvent->event->eventId;
    $this->r->id = $mainEvent->registrant->id;


    $this->r->legalName = mb_convert_case($mainEvent->registrant->first_name . ' ' . $mainEvent->registrant->last_name, MB_CASE_TITLE);
    $this->r->city = mb_convert_case($mainEvent->registrant->city, MB_CASE_TITLE);
    $this->r->address = $mainEvent->registrant->address;
    $this->r->address2 = $mainEvent->registrant->address2;
    $this->r->state = $mainEvent->registrant->state;
    $this->r->zip = $mainEvent->registrant->zip;
    $this->r->country = $mainEvent->registrant->country;

    $this->r->email = mb_convert_case($mainEvent->registrant->email, MB_CASE_LOWER_SIMPLE);
    $this->r->badge = $mainEvent->fieldValue->BADGE;

    $x = json_decode($mainEvent->fieldValue->PRONOUNS);
    if ( $x == null || in_array('Leave Blank', $x)) {
      $this->r->pronouns = '';
    } else {
      $this->r->pronouns = implode('|', $x);
    }

    $this->r->overridePackage = $mainEvent->fieldValue->Z_BADGE_SPECIAL;
    $this->r->clawPackage = $mainEvent->registrant->eventPackageType->value;
    $this->r->badgeId = $registrant->badgeId;
    $this->r->registration_code = $mainEvent->registrant->registration_code;

    if ( $this->r->overridePackage == '' ) {
      $tmpOverride='';

      switch ($this->r->clawPackage) {
        case EventPackageTypes::claw_staff:
          $tmpOverride = $mainEvent->fieldValue->STAFF_TYPE_STAFF;
          break;
        case EventPackageTypes::event_staff:
          $tmpOverride = $mainEvent->fieldValue->STAFF_TYPE_EVENT;
          break;
        case EventPackageTypes::event_talent:
          $tmpOverride = $mainEvent->fieldValue->STAFF_TYPE_TALENT;
          break;
      }

      if ( $tmpOverride != '' ) $this->r->overridePackage = $tmpOverride;
    }

    $shiftCatIds = ClawEvents::getCategoryIds(Aliases::shiftCategories());
    $dinnerCatIds = ClawEvents::getCategoryIds(['dinner']);
    $brunchCatIds = ClawEvents::getCategoryIds(['buffet-breakfast']);
    $buffetCatIds = ClawEvents::getCategoryIds(['buffet']);
    $leatherHeartCatIds = ClawEvents::getCategoryIds(['donations-leather-heart']);

    $this->r->shifts = '';
    $shiftCount = 0;
    $this->r->dinners[EventPackageTypes::dinner->value] = 'None';

    // Combo meals events
    $allMealsEventId = false;
    // $allMealsEventId = ClawEvents::getEventId($prefix.'meals-combo-all');
    //$allDinnersEventId = ClawEvents::getEventId($prefix.'meals-combo-dinners');
    $vipEventId = ClawEvents::getEventId($prefix.'vip');
    
    foreach ($registrant->records() as $r) {
      //$r = $registrant->castRecord($r);

      $scannedEvents = $this->explodeTicketScanned($r->fieldValue->Z_TICKET_SCANNED);

      if ( sizeof($scannedEvents) > 0 ) {
        $this->r->issuedMealTickets = array_merge($this->r->issuedMealTickets, $scannedEvents );
      }

      // if ( $r->event->eventId == $vipEventId ) {
      //   $this->r->brunches[EventPackageTypes::brunch_fri] = 'Fri';
      //   $this->r->brunches[EventPackageTypes::brunch_sat] = 'Sat';
      //   $this->r->brunches[EventPackageTypes::brunch_sun] = 'Sun';
  
      //   //$this->r->buffets[EventPackageTypes::buffet_wed] = 'Wed';
      //   $this->r->buffets[EventPackageTypes::buffet_thu] = 'Thu';
      //   $this->r->buffets[EventPackageTypes::buffet_fri] = 'Fri';
      //   $this->r->buffets[EventPackageTypes::buffet_sun] = 'Sun';
  
      //   $this->r->dinners[EventPackageTypes::dinner] = $r->fieldValue->Dinner;
        
      //   $this->r->mealIssueMapping = [
      //     ClawEvents::getEventId($prefix.'fri-breakfast') => $vipEventId,
      //     ClawEvents::getEventId($prefix.'sat-breakfast') => $vipEventId,
      //     ClawEvents::getEventId($prefix.'brunch') => $vipEventId,
      //     //ClawEvents::getEventId($prefix.'wed-buffet') => $vipEventId,
      //     ClawEvents::getEventId($prefix.'thu-buffet') => $vipEventId,
      //     ClawEvents::getEventId($prefix.'fri-buffet') => $vipEventId,
      //     ClawEvents::getEventId($prefix.'sun-buffet') => $vipEventId,
      //     ClawEvents::getEventId($prefix.'dinner') => $vipEventId,
      //   ];
      // }

      // if ( $allMealsEventId !== false && $r->event->eventId == $allMealsEventId ) {
      //   $this->r->brunches[EventPackageTypes::brunch_fri] = 'Fri';
      //   $this->r->brunches[EventPackageTypes::brunch_sat] = 'Sat';
      //   $this->r->brunches[EventPackageTypes::brunch_sun] = 'Sun';
  
      //   //$this->r->buffets[EventPackageTypes::buffet_wed] = 'Wed';
      //   $this->r->buffets[EventPackageTypes::buffet_thu] = 'Thu';
      //   $this->r->buffets[EventPackageTypes::buffet_fri] = 'Fri';
      //   $this->r->buffets[EventPackageTypes::buffet_sun] = 'Sun';
  
      //   $this->r->dinners[EventPackageTypes::dinner] = $r->fieldValue->Dinner;

      //   $this->r->mealIssueMapping = [
      //     ClawEvents::getEventId($prefix.'fri-breakfast') => $allMealsEventId,
      //     ClawEvents::getEventId($prefix.'sat-breakfast') => $allMealsEventId,
      //     ClawEvents::getEventId($prefix.'brunch') => $allMealsEventId,
      //     //ClawEvents::getEventId($prefix.'wed-buffet') => $allMealsEventId,
      //     ClawEvents::getEventId($prefix.'thu-buffet') => $allMealsEventId,
      //     ClawEvents::getEventId($prefix.'fri-buffet') => $allMealsEventId,
      //     ClawEvents::getEventId($prefix.'sun-buffet') => $allMealsEventId,
      //     ClawEvents::getEventId($prefix.'dinner') => $allMealsEventId,
      //   ];

      //   continue;
      // }

      // if ( $r->event->eventId == $allDinnersEventId ) {
      //   $this->r->buffets[EventPackageTypes::buffet_thu] = 'Thu';
      //   $this->r->buffets[EventPackageTypes::buffet_fri] = 'Fri';
      //   $this->r->buffets[EventPackageTypes::buffet_sun] = 'Sun';
  
      //   $this->r->dinners[EventPackageTypes::dinner] = $r->fieldValue->Dinner;

      //   $this->r->mealIssueMapping = [
      //     ClawEvents::getEventId($prefix.'thu-buffet') => $allDinnersEventId,
      //     ClawEvents::getEventId($prefix.'fri-buffet') => $allDinnersEventId,
      //     ClawEvents::getEventId($prefix.'sun-buffet') => $allDinnersEventId,
      //     ClawEvents::getEventId($prefix.'dinner') => $allDinnersEventId,
      //   ];

      //   continue;
      // }
      
      if (in_array($r->category->category_id, $shiftCatIds)) {
        $this->r->shifts .= $r->event->title . "\n";
        $shiftCount++;
        continue;
      }

      if (in_array($r->category->category_id, $dinnerCatIds)) {
        $this->r->dinners[EventPackageTypes::dinner->value] = $r->fieldValue->Dinner;
        continue;
      }

      if (in_array($r->category->category_id, $brunchCatIds)) {
        if ($r->event->eventId == ClawEvents::getEventId($prefix.'fri-breakfast')) $this->r->brunches[EventPackageTypes::brunch_fri->value] = 'Fri';
        if ($r->event->eventId == ClawEvents::getEventId($prefix.'sat-breakfast')) $this->r->brunches[EventPackageTypes::brunch_sat->value] = 'Sat';
        if ($r->event->eventId == ClawEvents::getEventId($prefix.'brunch')) $this->r->brunches[EventPackageTypes::brunch_sun->value] = 'Sun';
        continue;
      }

      if (in_array($r->category->category_id, $buffetCatIds)) {
        //if ($r->event->eventId == ClawEvents::getEventId($prefix.'wed-buffet')) $this->r->buffets[EventPackageTypes::buffet_wed] = 'Wed';
        // if ($r->event->eventId == ClawEvents::getEventId($prefix.'thu-buffet')) $this->r->buffets[EventPackageTypes::buffet_thu] = 'Thu';
        if ($r->event->eventId == ClawEvents::getEventId($prefix.'fri-buffet')) $this->r->buffets[EventPackageTypes::buffet_fri->value] = 'Fri';
        // if ($r->event->eventId == ClawEvents::getEventId($prefix.'sun-buffet')) $this->r->buffets[EventPackageTypes::buffet_sun] = 'Sun';
        continue;
      }

      if ( in_array($r->category->category_id, $leatherHeartCatIds)) {
        $this->r->leatherHeartSupport = true;
        continue;
      }
    }


    if ( $shiftCount < $event->minShifts ) {
      $errors[] = 'Minimum shifts not met.';
    }

    // ISSUED & PRINTED
    $this->r->issued = (int)$mainEvent->fieldValue->Z_BADGE_ISSUED != 0 ? true : false;
    $this->r->printed = (int)$mainEvent->fieldValue->Z_BADGE_PRINTED != 0 ? true : false;

    // Code of conduct
    $this->r->cocSigned = $mainEvent->fieldValue->CONDUCT_AGREEMENT == '' ? false : true;
    if ($this->r->cocSigned == false ) {
      $errors[] = 'Code of Conduct not signed.';
    }

    // Photo agreement
    $this->r->photoAllowed = strtolower($mainEvent->fieldValue->PHOTO_PERMISSION) == 'yes' ? true : false;

    // T-Shirt Size
    $this->r->shirtSize = $mainEvent->fieldValue->TSHIRT . $mainEvent->fieldValue->TSHIRT_VOL;
    if ( $this->r->shirtSize == '' ) $this->r->shirtSize = 'None';

    $this->r->dayPassDay = '';

    switch($this->r->package_eventId) {
      case ClawEvents::getEventId($prefix.'daypass-fri'):
        $this->r->dayPassDay = 'Fri';
        break;
      case ClawEvents::getEventId($prefix.'daypass-sat'):
        $this->r->dayPassDay = 'Sat';
        break;
      case ClawEvents::getEventId($prefix.'daypass-sun'):
        $this->r->dayPassDay = 'Sun';
        break;
    }

    if ( sizeof($info) != 0 ) {
      array_unshift($info, 'Action needed on badge:');
      $info[] = 'Please direct to Guest Services';
      $this->r->info = implode("\n", $info);
    }

    if ( sizeof($errors) != 0 ) {
      array_unshift($errors, 'Do not give out the badge:');
      $errors[] = 'Please direct to Guest Services';
      $this->r->error = implode("\n", $errors);
      return false;
    }

    return true;
  }

  public function doCheckin() {
    $registrant = new registrant(Aliases::current(), $this->r->uid);
    $registrant->loadCurrentEvents();
    $mainEvent = $registrant->getMainEvent();

    $registrant->updateFieldValues($mainEvent->registrant->id, ['Z_BADGE_ISSUED' => 1]);
  }

  public function doMarkPrinted()
  {
    $registrant = new Registrant(Aliases::current(), $this->r->uid);
    $registrant->loadCurrentEvents();
    $mainEvent = $registrant->getMainEvent();

    $registrant->updateFieldValues($mainEvent->registrant->id, ['Z_BADGE_PRINTED' => Helpers::mtime()]);
  }

  public function doMealCheckin(int $eventId): string
  {
    if ( $eventId <= 0 ) return $this->htmlMsg('Event selection error', 'btn-dark');
    if ( $this->uid == 0 ) return $this->htmlMsg('Unknown badge number', 'btn-dark');
    if ( $this->r->error != '' ) return $this->htmlMsg($this->r->error, 'btn-dark');

    if ($this->r->issued != true) {
      return $this->htmlMsg('Badge Not Issued','btn-warning');
    }

    // Does this badge have this meal?
    $events = new ClawEvents(Aliases::current());

    $e = $events->getEventByKey('eventId',$eventId, false);
    if (null == $e) {
      return $this->htmlMsg('Unknown event id '.$eventId.' in '.Aliases::current(), 'btn-dark');
    }

    $ticketEventId = $eventId;
    if ( array_key_exists($eventId, $this->r->mealIssueMapping) ) $ticketEventId = $this->r->mealIssueMapping[$eventId];

    if ( array_search($eventId, $this->r->issuedMealTickets) !== false ) {
      if ( $e->clawPackageType == EventPackageTypes::dinner) {
        return $this->htmlMsg($e->description . ' ticket already issued: '. $this->r->dinners[EventPackageTypes::dinner], 'btn-dark');
      } else {
        return $this->htmlMsg($e->description . ' ticket already issued', 'btn-dark');
      }
    }

    switch ($e->clawPackageType) {
      case EventPackageTypes::dinner:
        $meal = strtolower(substr($this->r->dinners[EventPackageTypes::dinner], 0, 4));

        if ( $meal == '') {
          return $this->htmlMsg('Dinner not assigned to this badge','btn-dark');
        }

        switch ($meal) {
          case 'beef':
            $description = 'Beef';
            $class = 'meal-beef';
            break;
          case 'fish':
            $description = 'Fish';
            $class = 'meal-fish';
            break;
          case 'chic':
            $description = 'Chicken';
            $class = 'meal-chicken';
            break;
          case 'vege':
            $description = 'Vegetarian';
            $class = 'meal-vegan';
            break;
          default:
            return $this->htmlMsg('Unknown meal selection', 'btn-dark');
            break;
        }

        $this->issueMealTicket($eventId,$ticketEventId);
        return $this->htmlMsg($description, $class);
        break;

      case EventPackageTypes::brunch_fri:
      case EventPackageTypes::brunch_sat:
      case EventPackageTypes::brunch_sun:
        if ($this->r->brunches[$e->clawPackageType] == '') {
          return $this->htmlMsg($e->description.' not assigned to this badge', 'btn-dark');
        }

        $this->issueMealTicket($eventId,$ticketEventId);
        return $this->htmlMsg($e->description.' ticket issued for: '.$this->r->badgeId, 'btn-info');
        break;

      case EventPackageTypes::buffet_wed:
      case EventPackageTypes::buffet_thu:
      case EventPackageTypes::buffet_fri:
      case EventPackageTypes::buffet_sun:
        if ($this->r->buffets[$e->clawPackageType] == '') {
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

  private function htmlMsg(string $msg, string $classes): string
  {
    $msg = <<< HTML
    <div class="d-grid gap-2">
  <button class="btn btn-lg $classes" type="button">$msg</button>
</div>
HTML;

    $b = property_exists($this, 'r') ? $this->r->badgeId : 'error';

    $result = [
      'badge' => $b,
      'msg' => $msg
    ];

    return json_encode($result);
  }

  private function issueMealTicket(int $mealEventId, int $ticketEventId)
  {
    $registrant = new registrant(Aliases::current(), $this->r->uid, [$ticketEventId]);
    $registrant->loadCurrentEvents();
    $registrant->mergeFieldValues(['Z_TICKET_SCANNED']);

    $record = ($registrant->records(true))[0];
    //$record = $registrant->castRecord($record);

    $rowId = $record->registrant->id;

    $values = $this->explodeTicketScanned($record->fieldValue->Z_TICKET_SCANNED);
    $values[] = $mealEventId;

    $values = array_unique($values);
    sort($values);

    $fieldValues = ['Z_TICKET_SCANNED' => implode(',',$values)];
    $registrant->updateFieldValues($rowId, $fieldValues, true);
  }

  static function search(string $search, string $page): array
  {
    $results = [];
    $byName = false;

    Helpers::sessionSet('eventAlias','');
    $e = new ClawEvents(Aliases::current());
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

    if ( $page == 'badge-print') {
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
    $result = [];
    $field = trim($field);

    if ( $field == 0 ) return $result;

    return explode(',',$field);
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

    $db = Factory::getDbo();

    $events = new clawEvents(Aliases::current());
    //$prefix = strtolower(Aliases::defaultPrefix).'-';

    $mainEvents = $events->mainEventIds;

    // if ( ($key = array_search($events->getEventId($prefix.'staff-coordinator'), $mainEvents)) != false ) {
    //   unset($mainEvents[$key]);
    // }
    // if ( ($key = array_search($events->getEventId($prefix.'staff-onsite'), $mainEvents)) != false ) {
    //   unset($mainEvents[$key]);
    // }

    $mainEventIds = implode(',',$mainEvents);

    $query = $db->getQuery(true);
    $query->select('r.id, r.registration_code')
      ->from('#__eb_registrants r')
      ->leftJoin('#__eb_field_values v ON v.registrant_id=r.id AND v.field_id='.$badgeFieldId)
      ->where('published = '.$published)
      ->where('event_id IN ('.$mainEventIds.')')
      ->where('(r.ts_modified > v.field_value OR v.id IS NULL )')
      ->order('r.invoice_number');

    if ( $limit > 0 ) {
      $query .= " LIMIT $limit";
    }

    $db->setQuery($query);
    $rows = $db->loadAssocList('id','registration_code');
    return $rows;
  }
}
