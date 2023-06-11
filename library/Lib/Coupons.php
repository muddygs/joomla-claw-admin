<?php
namespace ClawCorpLib\Lib;

use \Joomla\CMS\Factory;
use ClawCorpLib\Enums\EbCouponTypes;
use ClawCorpLib\Enums\EbCouponAssignments;

/** @package ClawCorpLib\Lib\Coupons */
class Coupons
{
  private array $couponEventsIds = []; // converted from alias; #__eb_coupon_events
  private float $discount = 0.0;
  private EbCouponTypes $clawCouponType = EbCouponTypes::voucher;
  private EbCouponAssignments $clawCouponAssignment = EbCouponAssignments::all_events;
  private int $times = 0;
  private int $max_usage_per_user = 0; // non-0 special handling in helper/override/registration.php
  private string $note = '';
  private int $access = 14; // always "registered" until further notice
  private $prefix = '';
  private int $userId = 0;

  /**
   * Minimum needed to create a default coupon is the discount amount. Defaults to
   * coupon as voucher for all events with maximum usage of 1
   * @param float $discount Amount or percent of the coupon
   * @param string $prefix The event prefix
   * @param string $note Always provide a note
   * @param string $tracker (optional) is appended to the note in JSON format (separated by :) for advanced queries
   */
  public function __construct(float $discount, string $prefix, string $note, string $tracker = '' )
  {
    $this->discount = $discount;
    $this->note = preg_replace("/[^A-Za-z0-9_]/", '', $note);

    if ( strlen(trim($note)) == 0)
    {
      die('A note is required for coupon generation.');
    }

    $tracker = trim($tracker);

    // If the tracker is an email address, is there an account associated with that email?
    if ( filter_var($tracker, FILTER_VALIDATE_EMAIL) )
    {
      $db = Factory::getDbo();
      $query = $db->getQuery(true)
          ->select($db->quoteName('id'))
          ->from($db->quoteName('#__users'))
          ->where($db->quoteName('email') . ' = ' . $db->quote($tracker));
      $db->setQuery($query);
      
      if ($id = $db->loadResult())
      {
        $this->userId = $id;
      }
    }
    
    if ( strlen($tracker) > 0 ) 
    {
      $this->note .= ':' . $tracker;
    }

    $this->prefix = $prefix;
  }

  /**
   * Stores the coupon to the database
   * @return bool True is successfully stored
   */
  public function storeCoupon(): bool {
    // validate conflict between non-empty $couponEventIds and $clawCouponAssignment

    return false;
  }

  public function setUserId(int $userId): void
  {
    $this->userId = $userId;
  }

  public function getUserId(): int
  {
    return $this->userId;
  }

  public function setCouponEventIds(array $eventIds): void
  {
    $this->couponEventsIds = $eventIds;
    $this->clawCouponAssignment = EbCouponAssignments::selected_events;
  }

  public function setCouponType(EbCouponTypes $type): void
  {
    $this->clawCouponType = $type;
  }

  public function setCouponAssignment(int $assignment): void
  {
    $this->clawCouponAssignment = $assignment;
  }

  public function setNote( string $note ): void
  {
    $this->note = $note;
  }

  public function getNote(): string
  {
    return $this->note;
  }

  /**
   * Inserts new coupon into Event Booking
   * @return string Coupon code
   */
  function insertCoupon(): string
  {
    $newcode = $this->getCoupon();

    if ( $this->clawCouponAssignment == EbCouponAssignments::selected_events &&
      count($this->couponEventsIds) == 0 ) {
        die('Cannot create per-event coupon without event assignment(s).');
      }

    $db = Factory::getDbo();
    
    $insert = [
      'id' => '0',
      'code' => $newcode,
      'coupon_type' => $this->clawCouponType->value,
      'discount' => $this->discount,
      'event_id' => $this->clawCouponAssignment->value,
      'times' => $this->times,
      'used' => 0,
      'valid_from' => '0000-00-00 00:00:00',
      'valid_to' => '0000-00-00 00:00:00',
      'published' => 1,
      'max_usage_per_user' => $this->max_usage_per_user,
      'category_id' => -1,
      'user_id' => $this->userId,
      'apply_to' => 0,
      'max_number_registrants' => 0,
      'min_number_registrants' => 0,
      'note' => $this->note,
      'enable_for' => 0,
      'access' => $this->access,
      'used_amount' => '0.00'
    ];

    $query = $db->getQuery(true);
    $query
      ->insert($db->qn('#__eb_coupons'))
      ->columns($db->qn(array_keys($insert)))
      ->values(implode(',', $db->q(array_values($insert))));
    $db->setQuery($query);
    $db->execute();	

    $id = $db->insertid();
    
    if ( $this->clawCouponAssignment == EbCouponAssignments::selected_events ) {
      foreach ( $this->couponEventsIds as $e ) {
        $query = "INSERT INTO `#__eb_coupon_events` (coupon_id, event_id) VALUES ($id,$e)";
        $db->setQuery($query);
        $db->execute();
      }
    }

    return $newcode;
  }

  /**
   * Generates the random character sequence for a coupon
   * @return string Coupon sequence including prefix and dashes for readability
   */
  private function getCoupon(): string
  {
    while (true) {
      $code = '';

      for ($i = 0; $i < 8; $i++) {
        $c = random_int(0, 25);

        // No oh's
        if (14 == $c) $c = 15;

        $code .= chr($c + 65);
      }

      $newcode = $this->prefix.'-'.substr($code, 0, 4) . '-' . substr($code, 4, 4);
      if ($this->verifyUniqueCoupon($newcode))
        break;
    }

    return $newcode;
  }

  /**
   * Queries database to determine if a coupon sequence has already been used
   * @return bool True if coupon sequence is unique
   */
  private function verifyUniqueCoupon(string $coupon): bool
  {
    $db = Factory::getDbo();

    $query = 'SELECT code FROM #__eb_coupons WHERE code = '.$db->q($coupon);
    $db->setQuery($query);
    $rows = $db->loadResult();

    return $rows == null ? true : false;
  }

  /**
   * Find a coupon for a signed in user that falls within the event ids
   * @param int $uid User ID
   * @param array $eventIds Array of event ids
   * @return object null or coupon info (code and event_id)
   */

  public static function getAssignedCoupon( int $uid = 0, array $eventIds = [] ): ?object
  {
    if ( 0 == $uid || count($eventIds) == 0 ) return null;

    $db = Factory::getDbo();
    $events = join(',',$eventIds);

    #TODO: Convert to prepared statement
    $query = <<< SQL
    SELECT c.code, e.event_id
    FROM #__eb_coupons c
    LEFT OUTER JOIN #__eb_coupon_events e ON e.coupon_id = c.id
    WHERE c.user_id = $uid AND c.published = 1 AND e.event_id IN ($events)
SQL;

    $db->setQuery($query);
    $result = $db->loadObject();

    return $result;
  }

}
