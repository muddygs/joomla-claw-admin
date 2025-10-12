<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EventPackageTypes;
use stdClass;

class RegistrantRecordEvent
{
  public int $eventId = 0;
  public string $alias = '';
  public string $title = '';
  public string $event_date = '';
  public string $event_end_date = '';
  public string $clawEventAlias = '';
}

class RegistrantRecordCategory
{
  public int $category_id = 0;
}

class RegistrantRecordRegistrant
{
  public int $id = 0;
  public int $published = 0;
  public EventPackageTypes $eventPackageType = EventPackageTypes::none;
  public int $user_id = 0;
  public string $first_name = '';
  public string $last_name = '';
  public string $invoice_number = '';
  public string $email = '';
  public string $address = '';
  public string $address2 = '';
  public string $city = '';
  public string $state = '';
  public string $zip = '';
  public string $country = '';
  public string $ts_modified = '';
  public string $register_date = '';
  public int $payment_status = -1;
  public float $total_amount = 0.0;
  public float $deposit_amount = 0.0;
  public float $payment_amount = 0.0;
  public float $discount_amount = 0.0;
  public float $amount = 0.0;
  public string $payment_method = '';
  public string $transaction_id = '';
  public string $deposit_payment_method = '';
  public string $deposit_payment_transaction_id = '';
  public string $registration_code = '';
  public string $badgeId = '';
}

class RegistrantRecord
{
  public registrantRecordEvent $event;
  public registrantRecordCategory $category;
  public registrantRecordRegistrant $registrant;
  public stdClass $fieldValue;

  function __construct(string $clawEventAlias, object $r)
  {
    $this->event = new RegistrantRecordEvent();
    $this->category = new RegistrantRecordCategory();
    $this->registrant = new RegistrantRecordRegistrant();
    $this->fieldValue = (object)[];

    $this->event->clawEventAlias = $clawEventAlias;

    foreach (array_keys(get_class_vars('ClawCorpLib\Lib\RegistrantRecordEvent')) as $k) {
      if (property_exists($r, $k)) {
        $this->event->$k = $r->$k;
      }
    }

    $this->category->category_id = $r->category_id;

    foreach (array_keys(get_class_vars('ClawCorpLib\Lib\RegistrantRecordRegistrant')) as $k) {
      if (property_exists($r, $k) && $r->$k !== null) {
        $this->registrant->$k = $r->$k;
      }
    }
  }
}
