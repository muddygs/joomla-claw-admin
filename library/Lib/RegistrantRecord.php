<?php

namespace ClawCorpLib\Lib;

class registrantRecordEvent
{
  var int $eventId = 0;
  var string $alias = '';
  var string $title = '';
  var string $event_date = '';
  var string $event_end_date = '';
}

class registrantRecordCategory
{
  var int $category_id = 0;
}

class registrantRecordRegistrant
{
  var int $id = 0;
  var int $published = 0;
  var int $clawPackageType = 0;
  var int $user_id = 0;
  var string $first_name = '';
  var string $last_name = '';
  var string $invoice_number = '';
  var string $email = '';
  var string $address = '';
  var string $address2 = '';
  var string $city = '';
  var string $state = '';
  var string $zip = '';
  var string $country = '';
  var string $ts_modified = '';
  var string $register_date = '';
  var int $payment_status = -1;
  var float $total_amount = 0.0;
  var float $deposit_amount = 0.0;
  var float $payment_amount = 0.0;
  var float $discount_amount = 0.0;
  var float $amount = 0.0;
  var string $payment_method = '';
  var string $transaction_id = '';
  var string $deposit_payment_method = '';
  var string $deposit_payment_transaction_id = '';
  var string $registration_code = ''; 
  var string $badgeId = '';
}

class RegistrantRecord
{
  var registrantRecordEvent $event;
  var registrantRecordCategory $category;
  var registrantRecordRegistrant $registrant;
  var $fieldValue;

  function __construct(object $r)
  {
    $this->event = new registrantRecordEvent();
    $this->category = new registrantRecordCategory();
    $this->registrant = new registrantRecordRegistrant();
    $this->fieldValue = (object)[];

    foreach (get_class_vars('registrantRecordEvent') AS $k => $v) {
      $this->event->$k = property_exists($r,$k) ? $r->$k : '';
      if (property_exists($r, $k) && $r->$k == null) unset($r->$k);
      $default = gettype($this->event->$k) == 'string' ? '' : 0;
    }

    $this->category->category_id = $r->category_id;

    foreach (get_class_vars('registrantRecordRegistrant') as $k => $v) {
      if ( property_exists($r, $k) && $r->$k == null ) unset($r->$k);
      $default = gettype($this->registrant->$k) == 'string' ? '' : 0;
      $this->registrant->$k = property_exists($r, $k) ? $r->$k : $default;
    }
  }
}