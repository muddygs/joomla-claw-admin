<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Lib\CheckinRecord;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\Checkin;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\Jwtwrapper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Methods to handle public class listing.
 */
class CheckinModel extends BaseDatabaseModel
{
  public CheckinRecord $record;

  public function JwtCheckin(string $registration_code)
  {
    $checkinRecord = new Checkin($registration_code);
    $checkinRecord->doCheckin();
  }

  public function GetCount(): array
  {
    $eventConfig = new EventConfig(Aliases::current(true));

    try {
      $attendee = $eventConfig->getMainEventByPackageType(EventPackageTypes::attendee);
      $attendeeCount = ($attendee->eventId > 0 && $attendee->published == EbPublishedState::published) ?
        Checkin::getUnprintedBadgeCount($attendee->eventId) :
        0;
    } catch (\Exception) {
      $attendee = 0;
    }

    $volunteerCount = 0;
    // Handle all the volunteer categories
    $vol = [
      EventPackageTypes::volunteer1,
      EventPackageTypes::volunteer2,
      EventPackageTypes::volunteer3,
      EventPackageTypes::volunteersuper,
      EventPackageTypes::event_talent,
    ];

    foreach ($vol as $v) {
      try {
        $packageInfo = $eventConfig->getMainEventByPackageType($v);
        if ($packageInfo->eventId > 0 && $packageInfo->published == EbPublishedState::published) {
          $volunteerCount += Checkin::getUnprintedBadgeCount($eventConfig->getMainEventByPackageType($v)->eventId);
        }
      } catch (\Exception) {
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

  /**
   * Using the search parameter, find the registrants that have the invoice # or last name
   * @param string $search substring to search
   * @param string $page If not 'badge-print', then only return those that have not been issued 
   * @return array eb_registrant:id => description 
   */
  public function search(string $search, string $page): array
  {
    $results = [];

    if (empty(trim($search))) {
      return $results;
    };

    $byName = false;

    $eventConfig = new EventConfig(Aliases::current(true));
    $inMainEventIds = implode(',', $eventConfig->getMainEventIds());
    $prefix = $eventConfig->eventInfo->prefix;

    // TODO: configuration to select the custom field from EventBooking
    $issued = ClawEvents::getFieldId('Z_BADGE_ISSUED');

    /** @var \Joomla\Database\DatabaseDriver */
    $db = $this->getDatabase();


    // TODO: what the hell is this trying to accomplish?
    $search = strtoupper($search);
    if (str_starts_with($search, $prefix . '-')) {
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
