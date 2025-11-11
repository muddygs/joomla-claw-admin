<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace ClawCorpLib\Deploy;

use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;


final class EbSyncItem
{
  public function __construct(
    private EventInfo $eventInfo,
    public int $id,
    public int $published,
    public int $main_category_id,
    public string $alias,
    public string $title,
    public string $description,
    public int $article_id,
    public ?Date $cancel_before_date,
    public ?Date $cut_off_date,
    public Date $event_date,
    public Date $event_end_date,
    public ?Date $publish_down,
    public float $individual_price,
    public Date $registration_start_date,
    public int $registration_access,
    public string $price_text = '',
    public string $user_email_body = '',
    public string $payment_methods = '2',
    public int $enable_cancel_registration = 1,
    public int $event_capacity = 0,
    public string $notification_emails = '',
    public int $created_by = 0,
    public int $location_id = 0,
    public string $first_reminder_frequency = 'd',
    public string $second_reminder_frequency = 'd',
    public string $third_reminder_frequency = 'd',
    public int $send_first_reminder = 0,
    public int $send_second_reminder = 0,
    public int $send_third_reminder = 0,
  ) {}

  /* public static function fromSql(object $obj): self */
  /* { */
  /*   $db = Factory::getContainer()->get('DatabaseDriver'); */
  /*   $nullDate = $db->getNullDate(); */
  /**/
  /*   $get = static function (object $o, string $prop): mixed { */
  /*     if (!property_exists($o, $prop)) { */
  /*       throw new \Exception("Property/column mismatch for $prop"); */
  /*     } */
  /*     return $o->{$prop}; */
  /*   }; */
  /**/
  /*   $asDt = static function (mixed $v): ?Date { */
  /*     if ($v === null) return null; */
  /*     if (is_string($v)) return $v == '0000-00-00 00:00:00' ? null : new Date($v); */
  /*     throw new \InvalidArgumentException('Invalid date value; expected Date|string|null'); */
  /*   }; */
  /**/
  /*   return new self( */
  /*     id: (int) $get($obj, 'id'), */
  /*     main_category_id: (int) $get($obj, 'main_category_id'), */
  /*     alias: (string) $get($obj, 'alias'), */
  /*     title: (string) $get($obj, 'title'), */
  /*     description: (string) $get($obj, 'description'), */
  /*     article_id: (int) $get($obj, 'article_id'), */
  /*     cancel_before_date: $asDt($get($obj, 'cancel_before_date')), */
  /*     cut_off_date: $asDt($get($obj, 'cut_off_date')), */
  /*     event_date: $asDt($get($obj, 'event_date')) ?? throw new \InvalidArgumentException('event_date is required'), */
  /*     event_end_date: $asDt($get($obj, 'event_end_date')) ?? throw new \InvalidArgumentException('event_end_date is required'), */
  /*     publish_down: $asDt($get($obj, 'publish_down'), $nullDate), */
  /*     individual_price: (float) $get($obj, 'individual_price'), */
  /*     registration_start_date: $asDt($get($obj, 'registration_start_date')) ?? throw new \InvalidArgumentException('registration_start_date is required'), */
  /*     registration_access: (int) $get($obj, 'registration_access'), */
  /*     price_text: (string) $get($obj, 'price_text'), */
  /*     user_email_body: (string) $get($obj, 'user_email_body'), */
  /*     payment_methods: (string) $get($obj, 'payment_methods'), */
  /*     enable_cancel_registration: (int) $get($obj, 'enable_cancel_registration'), */
  /*     event_capacity: (int) $get($obj, 'event_capacity'), */
  /*     notification_emails: (string) $get($obj, 'notification_emails'), */
  /*     created_by: (int) $get($obj, 'created_by'), */
  /*   ); */
  /* } */
  /**/
  public function toObject(): object
  {
    return (object) [
      'id' => $this->id,
      'published' => $this->published,
      'main_category_id' => $this->main_category_id,
      'alias' => $this->alias,
      'title' => $this->title,
      'description' => $this->description,
      'article_id' => $this->article_id,
      'cancel_before_date' => $this->cancel_before_date,
      'cut_off_date' => $this->cut_off_date,
      'event_date' => $this->event_date,
      'event_end_date' => $this->event_end_date,
      'publish_down' => $this->publish_down,
      'individual_price' => $this->individual_price,
      'registration_start_date' => $this->registration_start_date,
      'registration_access' => $this->registration_access,
      'price_text' => $this->price_text,
      'user_email_body' => $this->user_email_body,
      'payment_methods' => $this->payment_methods,
      'enable_cancel_registration' => $this->enable_cancel_registration,
      'event_capacity' => $this->event_capacity,
      'notification_emails' => $this->notification_emails,
      'created_by' => $this->created_by,
      'location_id' => $this->location_id,
      'first_reminder_frequency' => $this->first_reminder_frequency,
      'second_reminder_frequency' => $this->second_reminder_frequency,
      'third_reminder_frequency' => $this->third_reminder_frequency,
      'send_first_reminder' => $this->send_first_reminder,
      'send_second_reminder' => $this->send_second_reminder,
      'send_third_reminder' => $this->send_third_reminder,
    ];
  }

  public function toSql(): object
  {
    return (object) [
      'id' => $this->id,
      'published' => $this->published,
      'main_category_id' => $this->main_category_id,
      'alias' => $this->alias,
      'title' => $this->title,
      'description' => $this->description,
      'article_id' => $this->article_id,
      'cancel_before_date' => $this->dateOrNull($this->cancel_before_date),
      'cut_off_date' => $this->dateOrNull($this->cut_off_date, true),
      'event_date' => $this->dateRequired($this->event_date),
      'event_end_date' => $this->dateRequired($this->event_end_date),
      'publish_down' => $this->dateOrNull($this->publish_down),
      'individual_price' => $this->individual_price,
      'registration_start_date' => $this->dateRequired($this->registration_start_date, true),
      'registration_access' => $this->registration_access,
      'price_text' => $this->price_text,
      'user_email_body' => $this->user_email_body,
      'payment_methods' => $this->payment_methods,
      'enable_cancel_registration' => $this->enable_cancel_registration,
      'event_capacity' => $this->event_capacity,
      'notification_emails' => $this->notification_emails,
      'created_by' => $this->created_by,
      'location_id' => $this->location_id,
      'first_reminder_frequency' => $this->first_reminder_frequency,
      'second_reminder_frequency' => $this->second_reminder_frequency,
      'third_reminder_frequency' => $this->third_reminder_frequency,
      'send_first_reminder' => $this->send_first_reminder,
      'send_second_reminder' => $this->send_second_reminder,
      'send_third_reminder' => $this->send_third_reminder,
    ];
  }

  private function dateRequired(Date $dt, bool $startOfDay = false): string
  {
    return $this->useLocalTimeAsUtcSql($dt, $startOfDay);
  }

  private function dateOrNull(?Date $dt, bool $startOfDay = false): string
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');
    $nullDate = $db->getNullDate();
    if ($dt === null) {
      return $nullDate;
    }
    return ($this->useLocalTimeAsUtcSql($dt, $startOfDay));
  }

  /**
   * Interpret $date as a local wall-clock in timezone within EventInfo, optionally snapped to local midnight,
   * and return an SQL string in UTC (YYYY-mm-dd HH:ii:ss).
   *
   * Examples:
   *  - Event in America/Los_Angeles with local start “2026-04-02 00:00:00” -> “2026-04-02 07:00:00” UTC (PDT).
   *  - Event in America/New_York with local start “2026-04-02 00:00:00” -> “2026-04-02 04:00:00” UTC (EDT).
   */
  private function useLocalTimeAsUtcSql(Date $date, bool $startOfDay = false): string
  {
    $tz = new \DateTimeZone($this->eventInfo->timezone);

    // Build a local datetime string that ignores the server/app timezone
    // and is interpreted in the event’s timezone.
    if ($startOfDay) {
      // Snap to 00:00:00 in the event’s timezone, DST-safe.
      $local = new Date($date->format('Y-m-d 00:00:00'), $tz);
      //var_dump([$startOfDay, $local->toSql()]);
      return $local->toSql();
    }

    // Keep the provided clock time but interpret it in the event's timezone.
    $local = new Date($date->format('Y-m-d H:i:s'), $tz);

    // toSql(true) emits UTC
    //var_dump([$startOfDay, $local->toSql()]);
    return $local->toSql(true);
  }

  /*   private function useLocalTimeAsUtcSql(Date $date, bool $startOfDay = false): string */
  /*   { */
  /*     $d = clone $date; */
  /**/
  /*     if ($startOfDay) { */
  /*       $timestamp = $d->getTimestamp(); */
  /*       $timestamp -= $timestamp % 3600; */
  /*       $d->setTimezone(new \DateTimeZone($this->eventInfo->timezone))->setTimestamp($timestamp); */
  /*     } */
  /**/
  /*     return $d->toSql(true); */
  /*   } */
  /* } */
}
