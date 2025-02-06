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

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\Ebregistrant;
use ClawCorpLib\Lib\Registrant;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Methods to handle public class listing.
 */
class RollcallModel extends BaseDatabaseModel
{
  public function volunteerSearch(string $regid): array
  {
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

  /**
   * Updates the checkin/checkout status for a registration record
   * @param int $recordId Registration record id 
   * @param bool $isChecking Updates checkin value then true, otherwise checkout value
   * @param bool $action True = set, False = unset
   * @return bool False on error
   */
  public function volunteerUpdate(int $recordId, bool $isCheckin, bool $action): bool
  {
    $record = Registrant::loadRegistrantRow($recordId);

    if ($record == null) {
      return false;
    }

    $registrant = new registrant(Aliases::current(true), $record->user_id, [$record->event_id]);
    $registrant->loadCurrentEvents();

    $records = $registrant->records();
    $record = reset($records);
    if (false === $record) return false;

    $update = [];

    if ($isCheckin) {
      if ($action) {
        $update = [
          'Z_SHIFT_CHECKIN' => 1
        ];
      } else {
        $update = [
          'Z_SHIFT_CHECKIN' => 0,
          'Z_SHIFT_CHECKOUT' => 0
        ];
      }
    } else {
      if ($action) {
        $update = [
          'Z_SHIFT_CHECKOUT' => 1
        ];
      } else {
        $update = [
          'Z_SHIFT_CHECKOUT' => 0
        ];
      }
    }

    $registrant->updateFieldValues($recordId, $update);
    return true;
  }

  public function volunteerAddShift(int $uid, int $eventid): bool
  {
    $registration = new Ebregistrant($eventid, $uid);

    $registrant = new Registrant(Aliases::current(true), $uid);
    $registrant->loadCurrentEvents();

    /** @var \ClawCorpLib\Lib\RegistrantRecord */
    $mainEventId = $registrant->getMainEvent();

    if (null == $mainEventId) {
      return 'User Does Not Have a Main Event';
    }

    $registration->copyFrom($mainEventId->registrant->id);
    $id = $registration->insert();
    return $id ? true : false;
  }
}
