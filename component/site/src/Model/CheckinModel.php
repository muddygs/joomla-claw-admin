<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\JwtStates;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\Checkin;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Ebregistrant;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\Jwtwrapper;
use ClawCorpLib\Lib\Registrant;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Methods to handle public class listing.
 */
class CheckinModel extends BaseDatabaseModel
{
  public function JwtstateInit($email, $subject)
  {
    $nonce = Jwtwrapper::getNonce();
    $email = trim($email);

    $jsonValues = [
      'state' => 'error',
      'token' => ''
    ];

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && array_key_exists($subject, Jwtwrapper::jwt_token_pages)) {
      $jwt = new Jwtwrapper($nonce);
      $result = $jwt->initTokenRequest($email, $nonce, $subject);
      $jsonValues['state'] = $result ? JwtStates::init->value : JwtStates::error->value;
    }

    return json_encode($jsonValues);
  }

  public function JwtstateState($subject): array
  {
    $jsonValues = [];
    $nonce = Jwtwrapper::getNonce();
    $jwt = new Jwtwrapper($nonce);
    list($state, $token) = $jwt->getDatabaseState($nonce, $subject);
    $jsonValues['state'] = $state;
    $jsonValues['token'] = $token;

    return $jsonValues;
  }

  public function JwtConfirm($token): string
  {
    $jsonValues = [
      'state' => 'error',
      'token' => ''
    ];

    $nonce = Jwtwrapper::getNonce();
    $jwt = new Jwtwrapper($nonce);
    $payload = $jwt->confirmToken($token, JwtStates::confirm);

    if ($payload != null) {
      if ($payload->iat + 310 > time()) {
        $jwt->updateDatabaseState($payload, JwtStates::issued);
        $jsonValues['state'] = $payload->state;
      }
    }
    $jwt->closeWindow();
    return json_encode($jsonValues);
  }

  public function JwtRevoke($token): string
  {
    $jsonValues = [
      'state' => 'error',
      'token' => ''
    ];

    $nonce = Jwtwrapper::getNonce();
    $jwt = new Jwtwrapper($nonce);
    $payload = $jwt->confirmToken($token, JwtStates::revoked);

    if ($payload != null) {
      $jwt->updateDatabaseState($payload, JwtStates::revoked);
      $jsonValues['state'] = $payload->state;
    }
    $jwt->closeWindow();
    return json_encode($jsonValues);
  }

  public function JwtmonValidate(string $token): array
  {
    $result = [
      'time_remaining' => 0,
      'state' => JwtStates::error->value
    ];

    $payload = Jwtwrapper::confirmToken($token, JwtStates::issued);
    if ($payload != null) {
      $exp = intval($payload->exp);
      $remaining = max(0, $exp - time());
      $result['state'] = $payload->state;
      $result['time_remaining'] = $remaining;
    }

    return $result;
  }

  public function JwtSearch(string $token, string $search, string $page)
  {
    Jwtwrapper::redirectOnInvalidToken(page: $page, token: $token);

    $searchResults = $this->search($search, $page);
    header('Content-Type: application/json');
    return $searchResults;
  }

  public function JwtValue(string $token, string $registration_code, string $page)
  {
    Jwtwrapper::redirectOnInvalidToken(page: $page, token: $token);

    $checkinRecord = new Checkin($registration_code);
    $r = $checkinRecord->r->toObject();
    return $r;
  }

  public function JwtCheckin(string $token, string $registration_code, string $page)
  {
    Jwtwrapper::redirectOnInvalidToken(page: $page, token: $token);

    $checkinRecord = new Checkin($registration_code);
    $checkinRecord->doCheckin();

    $r = ['result' => '1'];
    return $r;
  }

  public function JwtGetCount(string $token): array
  {
    Jwtwrapper::redirectOnInvalidToken(page: 'badge-print', token: $token);

    $eventConfig = new EventConfig(Aliases::current(true));
    $attendee = $eventConfig->getMainEventByPackageType(EventPackageTypes::attendee);
    $attendeeCount = ($attendee->eventId > 0 && $attendee->published == EbPublishedState::published) ?
      Checkin::getUnprintedBadgeCount($attendee->eventId) :
      0;

    $volunteerCount = 0;
    // Handle all the volunteer categories
    $vol = [
      EventPackageTypes::volunteer2,
      EventPackageTypes::volunteer3,
      EventPackageTypes::volunteersuper,
      EventPackageTypes::event_talent,
    ];

    foreach ($vol as $v) {
      $packageInfo = $eventConfig->getMainEventByPackageType($v);
      if ($packageInfo->eventId > 0 && $packageInfo->published == EbPublishedState::published) {
        $volunteerCount += Checkin::getUnprintedBadgeCount($eventConfig->getMainEventByPackageType($v)->eventId);
      }
    }

    $all = Checkin::getUnprintedBadgeCount(0);
    $remainder = $all - $attendeeCount - $volunteerCount;

    $result = [
      'all' => $all,
      'attendee' => $attendeeCount,
      'volunteer' => $volunteerCount,
      'remainder' => $remainder
    ];

    return $result;
  }

  public function JwtMealCheckin(string $token, string $registration_code, string $meal)
  {
    Jwtwrapper::redirectOnInvalidToken(page: 'meals-checkin', token: $token);

    $checkinRecord = new Checkin($registration_code);

    if (!$checkinRecord->isValid) {
      $errors = ['Record error or invalid badge #/code.'];
      // TODO: r may not be initialized
      if (!is_null($checkinRecord->r)) {
        $errors = explode("\n", $checkinRecord->r->error);
        array_shift($errors);
      }

      return [
        'state' => 'error',
        'message' => '<p class="text-center">' . implode("</p><p class=\"text-center\">", $errors) . '</p>'
      ];
    }

    $msg = $checkinRecord->doMealCheckin($meal);
    return $msg;
  }

  public function volunteerSearch(string $token, string $regid)
  {
    Jwtwrapper::redirectOnInvalidToken(page: 'volunteer-roll-call', token: $token);

    $customFields = ['BADGE', 'Z_BADGE_ISSUED', 'Z_SHIFT_CHECKIN', 'Z_SHIFT_CHECKOUT'];

    $uid = Registrant::getUserIdFromInvoice($regid);

    $result = [
      'valid' => false,
      'message' => 'User Not Found',
      'name' => '',
      'uid' => $uid,
      'shifts' => []
    ];

    if ($uid == 0) {
      return $result;
    }

    $registrant = new Registrant(Aliases::current(true), $uid);
    $registrant->loadCurrentEvents();
    $registrant->mergeFieldValues($customFields);

    /** @var \ClawCorpLib\Lib\RegistrantRecord */
    $mainEvent = $registrant->getMainEvent();

    if (null == $mainEvent) {
      $result['message'] = 'User Does Not Have a Main Event';
      return $result;
    }

    $result['valid'] = true;
    $result['message'] = 'ok';
    $result['name'] = ucwords($mainEvent->registrant->first_name . ' ' . $mainEvent->registrant->last_name);
    $result['uid'] = $mainEvent->registrant->user_id;

    $shiftCatIds = array_merge($registrant->eventConfig->eventInfo->eb_cat_shifts, $registrant->eventConfig->eventInfo->eb_cat_supershifts);

    $shifts = [];

    /** @var \ClawCorpLib\Lib\RegistrantRecord */
    foreach ($registrant->records() as $record) {
      if (in_array($record->category->category_id, $shiftCatIds)) {
        $shifts[] = [
          'regid' => $record->registrant->id,
          'title' => $record->event->title,
          'checkin' => (int)($record->fieldValue->Z_SHIFT_CHECKIN) == 0 ? false : true,
          'checkout' => (int)($record->fieldValue->Z_SHIFT_CHECKOUT) == 0 ? false : true,
          'time' => $record->event->event_date
        ];
      }
    }

    // Sort shifts by time
    usort($shifts, function ($a, $b) {
      return strcmp($a['time'], $b['time']);
    });

    $result['shifts'] = $shifts;

    return $result;
  }

  public function volunteerUpdate(string $token, int $regid, string $action, bool $currentValue): string
  {
    Jwtwrapper::redirectOnInvalidToken(page: 'volunteer-roll-call', token: $token);

    $record = Registrant::loadRegistrantRow($regid);

    if ($record == null) {
      return 'error';
    }

    $registrant = new registrant(Aliases::current(true), $record->user_id, [$record->event_id]);
    $registrant->loadCurrentEvents();

    $records = $registrant->records();
    $record = reset($records);
    if (false === $record) return 'error';

    $update = [];

    if ($action == 'checkin') {
      if (true == $currentValue) {
        $update = [
          'Z_SHIFT_CHECKIN' => 0,
          'Z_SHIFT_CHECKOUT' => 0
        ];
      } else {
        $update = [
          'Z_SHIFT_CHECKIN' => 1
        ];
      }
    }

    if ($action == 'checkout') {
      if (true == $currentValue) {
        $update = [
          'Z_SHIFT_CHECKOUT' => 0
        ];
      } else {
        $update = [
          'Z_SHIFT_CHECKOUT' => 1
        ];
      }
    }

    $registrant->updateFieldValues($regid, $update);
    return 'ok';
  }

  public function volunteerAddShift(string $token, int $uid, int $shift): string
  {
    Jwtwrapper::redirectOnInvalidToken(page: 'volunteer-roll-call', token: $token);

    $registration = new Ebregistrant($shift, $uid);

    $registrant = new Registrant(Aliases::current(true), $uid);
    $registrant->loadCurrentEvents();

    /** @var \ClawCorpLib\Lib\RegistrantRecord */
    $mainEventId = $registrant->getMainEvent();

    if (null == $mainEventId) {
      return 'User Does Not Have a Main Event';
    }

    $registration->copyFrom($mainEventId->registrant->id);
    $id = $registration->insert();
    return $id ? 'ok' : 'error';
  }

  /**
   * Using the search parameter, find the registrants that have the invoice # or last name
   * @param string $search substring to search
   * @param string $page If not 'badge-print', then only return those that have not been issued 
   * @return array 
   */
  private function search(string $search, string $page): array
  {
    $results = [];
    $byName = false;

    $eventConfig = new EventConfig(Aliases::current(true));
    $inMainEventIds = implode(',', $eventConfig->getMainEventIds());
    $prefix = $eventConfig->eventInfo->prefix;

    $issued = ClawEvents::getFieldId('Z_BADGE_ISSUED');
    $search = strtoupper($search);

    /** @var \Joomla\Database\DatabaseDriver */
    $db = $this->getDatabase();

    if (substr($search, 0, 3) == $prefix) {
      $search = substr($search, 1);
    }

    $search = $db->q('%' . $search . '%');

    $query = $db->getQuery(true);
    $query->select(['r.user_id', 'r.registration_code', 'r.first_name', 'r.last_name', 'r.city', 'r.invoice_number'], [null, null, null, null, null, 'badgeId'])
      ->from($db->qn('#__eb_registrants', 'r'))
      ->join('LEFT OUTER', $db->qn('#__eb_field_values', 'v') . ' ON ' .
        $db->qn('v.registrant_id') . ' = ' . $db->qn('r.id') . ' AND ' . $db->qn('v.field_id') . '=' . $db->q($issued))
      ->where('r.published = 1')
      ->where('(r.invoice_number LIKE ' . $search . ' OR r.last_name LIKE ' . $search . ')')
      ->where('r.event_id IN (' . $inMainEventIds . ')')
      ->order('r.first_name')
      ->setLimit(20);

    if ('badge-print' != $page) {
      $query->where('(v.field_value IS NULL OR v.field_value != 1)');
    }

    $db->setQuery($query);
    $rows = $db->loadObjectList();

    foreach ($rows as $r) {
      $badge = $prefix . '-' . str_pad($r->user_id, 5, '0', STR_PAD_LEFT);

      $name = mb_convert_case($r->first_name . ' ' . $r->last_name . ' (' . $r->city . ')', MB_CASE_TITLE);
      $description = $byName ? $name . ' - ' . $badge : $badge . ' ' . $name;
      $results[] = [
        'id' => $r->registration_code,
        'name' => $description
      ];
    }

    return $results;
  }
}

