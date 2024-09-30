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
  public ?CheckinRecord $r;
  private $uid = 0;
  public bool $isValid = false;
  public static string $alias = '';

  private static array $comboMealsCache = [];
  private static ?EventConfig $eventConfig = null;

  public function __construct(
    public string $registration_code,
    public bool $errorReporting = true
  ) {
    if (self::$alias == '') self::$alias = Aliases::current(true);

    $this->uid = Registrant::getUserIdFromInvoice($registration_code);
    $this->isValid = false;
    $this->r = null;

    if (self::$eventConfig == null) {
      self::$eventConfig = new EventConfig(self::$alias, []);
    }

    // Combo meals events
    if (sizeof(self::$comboMealsCache) == 0) {
      foreach ([EventPackageTypes::combo_meal_1, EventPackageTypes::combo_meal_2, EventPackageTypes::combo_meal_3, EventPackageTypes::combo_meal_4] as $comboMeal) {
        $combo = self::$eventConfig->getPackageInfo($comboMeal);
        if (is_null($combo)) continue;
        self::$comboMealsCache[] = $combo;
      }
    }

    if ($this->uid != 0) {
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

    // TODO: move this to Config class database
    $fieldValues = [
      'BADGE',
      'Z_BADGE_SPECIAL',
      'Z_BADGE_ISSUED',
      'Z_BADGE_PRINTED',
      'Dinner',
      'DinnerCle',
      'CONDUCT_AGREEMENT',
      'PHOTO_PERMISSION',
      'TSHIRT',
      'TSHIRT_VOL',
      'Z_TICKET_SCANNED',
      'STAFF_TYPE_STAFF',
      'STAFF_TYPE_TALENT',
      'STAFF_TYPE_EVENT',
      'PRONOUNS'
    ];

    $registrant = new Registrant(self::$alias, $this->uid);
    $registrant->loadCurrentEvents();

    /** @var \ClawCorpLib\Lib\RegistrantRecord */
    $mainEventRegistrantRecord = null;
    $records = $registrant->records();

    if (count($records)) {
      $registrant->mergeFieldValues($fieldValues);
      /** @var \ClawCorpLib\Lib\RegistrantRecord */
      $mainEventRegistrantRecord = $registrant->getMainEvent();
    }

    // Cache meal labels
    $badgeValues = [];
    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach (self::$eventConfig->packageInfos as $e) {
      if ($e->badgeValue != '') {
        $badgeValues[$e->eventId] = $e->badgeValue;
      }
    }

    $this->r = new CheckinRecord(self::$eventConfig, $this->uid);
    // Error for no main event
    if ($mainEventRegistrantRecord == null) {
      $this->r->error = 'User does not have a registration package.';
      return false;
    }

    $event = self::$eventConfig->getMainEventByPackageType($mainEventRegistrantRecord->registrant->eventPackageType);

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
    $this->r->pronouns =  (is_null($x) || in_array('Leave Blank', $x)) ? '' : implode('|', $x);

    $this->r->eventPackageType = $mainEventRegistrantRecord->registrant->eventPackageType;
    $this->r->badgeId = $registrant->badgeId;
    $this->r->registration_code = $mainEventRegistrantRecord->registrant->registration_code;

    $shiftCatIds = array_merge($registrant->eventConfig->eventInfo->eb_cat_shifts, $registrant->eventConfig->eventInfo->eb_cat_supershifts);
    $leatherHeartCatId = ClawEvents::getCategoryId('donations-leather-heart');

    $this->r->shifts = '';
    $shiftCount = 0;

    /** @var \ClawCorpLib\Lib\RegistrantRecord */
    foreach ($records as $r) {
      $scannedEvents = $this->explodeTicketScanned($r->fieldValue->Z_TICKET_SCANNED);

      if (count($scannedEvents)) {
        $this->r->issuedMealTickets = array_merge($this->r->issuedMealTickets, $scannedEvents);
      }

      $comboCount = 0;

      /** @var \ClawCorpLib\Lib\PackageInfo */
      foreach (self::$comboMealsCache as $comboMeal) {
        if ($r->event->eventId == $comboMeal->eventId) {
          $comboCount++;

          foreach ($comboMeal->meta as $mealEventId) {
            $this->r->mealIssueMapping[$mealEventId] = $comboMeal->eventId;

            if (array_key_exists($mealEventId, $this->r->dinners)) {
              $this->r->dinners[$mealEventId] = $r->fieldValue->Dinner . $r->fieldValue->DinnerCle;
              continue;
            }

            if (array_key_exists($mealEventId, $this->r->brunches)) {
              $this->r->brunches[$mealEventId] = $badgeValues[$mealEventId];
              continue;
            }

            if (array_key_exists($mealEventId, $this->r->buffets)) {
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
      if (array_key_exists($r->event->eventId, $this->r->dinners)) {
        $this->r->dinners[$r->event->eventId] = $r->fieldValue->Dinner . $r->fieldValue->DinnerCle;
        continue;
      }

      if (array_key_exists($r->event->eventId, $this->r->brunches)) {
        $this->r->brunches[$r->event->eventId] = $badgeValues[$r->event->eventId];
        continue;
      }

      if (array_key_exists($r->event->eventId, $this->r->buffets)) {
        $this->r->buffets[$r->event->eventId] = $badgeValues[$r->event->eventId];
        continue;
      }

      // Leather Heart Sponsorships
      if ($r->category->category_id == $leatherHeartCatId) {
        $this->r->leatherHeartSupport = true;
        continue;
      }
    } // end foreach record

    if ($comboCount > 1) {
      $error[] = 'Multiple combo meals found. This is not allowed.';
    }

    if ($shiftCount < $event->minShifts) {
      $errors[] = 'Minimum shifts not met.';
    }

    // ISSUED & PRINTED
    $this->r->issued = (bool)$mainEventRegistrantRecord->fieldValue->Z_BADGE_ISSUED;
    $this->r->printed = (bool)$mainEventRegistrantRecord->fieldValue->Z_BADGE_PRINTED;

    // Code of conduct
    $this->r->cocSigned = trim($mainEventRegistrantRecord->fieldValue->CONDUCT_AGREEMENT) != '';
    if ($this->r->cocSigned == false) {
      $errors[] = 'Code of Conduct not signed.';
    }

    // Photo agreement
    $this->r->photoAllowed = strcasecmp($mainEventRegistrantRecord->fieldValue->PHOTO_PERMISSION, 'yes') == 0;

    // T-Shirt Size
    switch ($mainEventRegistrantRecord->registrant->eventPackageType) {
      case EventPackageTypes::claw_staff:
      case EventPackageTypes::claw_board:
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
    if ($this->r->shirtSize == '') $this->r->shirtSize = 'None';

    $this->r->dayPassDay = '';

    switch ($this->r->package_eventId) {
      case self::$eventConfig->getPackageInfo(EventPackageTypes::day_pass_fri)->eventId:
        $this->r->dayPassDay = 'Fri';
        break;
      case self::$eventConfig->getPackageInfo(EventPackageTypes::day_pass_sat)->eventId:
        $this->r->dayPassDay = 'Sat';
        break;
      case self::$eventConfig->getPackageInfo(EventPackageTypes::day_pass_sun)->eventId:
        $this->r->dayPassDay = 'Sun';
        break;
    }

    if (sizeof($info) != 0) {
      array_unshift($info, 'Action needed on badge:');
      $info[] = 'Please direct to Guest Services';
      $this->r->info = implode("\n", $info);
    }

    if (!$this->r->printed) {
      $errors[] = 'Badge not printed.';
    }

    if (sizeof($errors) != 0 && $this->errorReporting) {
      array_unshift($errors, 'Do not issue badge:');
      $errors[] = 'Please direct to Guest Services';
      $this->r->error = implode("\n", $errors);
      return false;
    } else {
      $this->r->error = implode("\n", $errors);
    }

    return true;
  }

  public function doCheckin()
  {
    $registrant = new Registrant(self::$alias, $this->r->uid);
    $mainEvent = $registrant->getMainEvent();

    $registrant->updateFieldValues($mainEvent->registrant->id, ['Z_BADGE_ISSUED' => 1]);
  }

  public function doMarkPrinted()
  {
    $registrant = new Registrant(self::$alias, $this->r->uid);
    /** @var \ClawCorpLib\Lib\RegistrantRecord */
    $mainEvent = $registrant->getMainEvent();

    $registrant->updateFieldValues($mainEvent->registrant->id, ['Z_BADGE_PRINTED' => Helpers::mtime()]);
  }

  public function doMealCheckin(int $eventId): array
  {
    if ($eventId <= 0) return $this->htmlMsg('Event selection error', 'btn-dark');
    if ($this->uid == 0) return $this->htmlMsg('Unknown badge number', 'btn-dark');
    if ($this->r->error != '') return $this->htmlMsg($this->r->error, 'btn-dark');

    if ($this->r->issued != true) {
      return $this->htmlMsg('Badge Not Issued', 'btn-warning');
    }

    // Does this badge have this meal?
    $eventConfig = new EventConfig(self::$alias);

    /** @var \ClawCorpLib\Lib\PackageInfo */
    $packageInfo = $eventConfig->getPackageInfoByProperty('eventId', $eventId, false);
    if (null == $packageInfo) {
      return $this->htmlMsg('Unknown event id ' . $eventId . ' in ' . self::$alias, 'btn-dark');
    }

    $ticketEventId = $eventId;
    if (array_key_exists($eventId, $this->r->mealIssueMapping)) $ticketEventId = $this->r->mealIssueMapping[$eventId];

    if (array_search($eventId, $this->r->issuedMealTickets) !== false) {
      if ($packageInfo->eventPackageType == EventPackageTypes::dinner) {
        return $this->htmlMsg($packageInfo->title . ' ticket already issued: ' . $this->r->dinners[$packageInfo->eventId], 'btn-dark');
      } else {
        return $this->htmlMsg($packageInfo->title . ' ticket already issued', 'btn-dark');
      }
    }

    switch ($packageInfo->eventPackageType) {
      case EventPackageTypes::dinner:
        $meal = strtolower($this->r->dinners[$packageInfo->eventId]);

        if ($meal == '') {
          return $this->htmlMsg('Dinner not assigned to this badge', 'btn-dark');
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

        foreach ($mealTypes as $info) {
          foreach ($info['phrases'] as $phrase) {
            if (str_contains($meal, $phrase)) {
              $description = $info['description'];
              $class = $info['class'];
              break 2;
            }
          }
        }

        if ($description == '') {
          return $this->htmlMsg('Unknown meal selection: ' . $meal, 'btn-dark');
        }

        $this->issueMealTicket($eventId, $ticketEventId);
        return $this->htmlMsg($description, $class);
        break;

      case EventPackageTypes::brunch_fri:
      case EventPackageTypes::brunch_sat:
      case EventPackageTypes::brunch_sun:
        if ($this->r->brunches[$packageInfo->eventId] == '') {
          return $this->htmlMsg($packageInfo->title . ' not assigned to this badge', 'btn-dark');
        }

        $this->issueMealTicket($eventId, $ticketEventId);
        return $this->htmlMsg($packageInfo->title . ' ticket issued for: ' . $this->r->badgeId, 'btn-info');
        break;

      case EventPackageTypes::buffet_wed:
      case EventPackageTypes::buffet_thu:
      case EventPackageTypes::buffet_fri:
      case EventPackageTypes::buffet_bluf:
      case EventPackageTypes::buffet_sun:
        if ($this->r->buffets[$packageInfo->eventId] == '') {
          return $this->htmlMsg($packageInfo->title . ' not assigned to this badge', 'btn-dark');
        }

        $this->issueMealTicket($eventId, $ticketEventId);
        return $this->htmlMsg($packageInfo->title . ' ticket issued for: ' . $this->r->badgeId, 'btn-info');
        break;

      default:
        return $this->htmlMsg(__FILE__ . ': Unhandled CLAW package', 'btn-danger');
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
    $registrant = new Registrant(self::$alias, $this->r->uid, [$ticketEventId]);
    $registrant->loadCurrentEvents();
    $registrant->mergeFieldValues(['Z_TICKET_SCANNED']);

    $record = ($registrant->records(true))[0];

    $rowId = $record->registrant->id;
    $fieldValues = ['Z_TICKET_SCANNED' => $mealEventId];
    $registrant->updateFieldValues($rowId, $fieldValues, true);
  }

  private function explodeTicketScanned(string $field): array
  {
    $field = trim($field);
    return (0 == $field || '' == $field) ? [] : explode(',', $field);
  }

  static function getUnprintedBadgeCount(int $eventId): int
  {
    if ($eventId == 0) return count(Checkin::getUnprintedBadges([]));
    return count(Checkin::getUnprintedBadges([$eventId]));
  }

  /**
   * Gets an array (indexed by reg row id) of registration_code for unprinted badges
   * TODO: also check on addons for badge changes
   * @param int $limit Maximum entries to return (default is all)
   * @return array registration_codes array
   */
  static function getUnprintedBadges(array $mainEventIds, int $limit = 0): array
  {
    $badgeFieldId = ClawEvents::getFieldId('Z_BADGE_PRINTED');
    $published = EbPublishedState::published->value;

    $db = Factory::getContainer()->get('DatabaseDriver');

    if (count($mainEventIds) == 0) {
      $eventConfig = new EventConfig(Aliases::current(true));
      $mainEventIds = $eventConfig->getMainEventIds();
    }

    $query = $db->getQuery(true);
    $query->select('r.id, r.registration_code')
      ->from('#__eb_registrants r')
      ->leftJoin('#__eb_field_values v ON v.registrant_id=r.id AND v.field_id=' . $badgeFieldId)
      ->where('published = ' . $published)
      ->where('event_id IN (' . implode(',', array_map(fn($n) => $db->q($n), $mainEventIds)) . ')')
      ->where('(r.ts_modified > v.field_value OR v.id IS NULL )')
      ->order('r.invoice_number');

    if ($limit) {
      $query .= " LIMIT $limit";
    }

    $db->setQuery($query);
    $rows = $db->loadAssocList('id', 'registration_code');
    return $rows;
  }
}
