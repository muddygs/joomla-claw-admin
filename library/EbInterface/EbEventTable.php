<?php

namespace ClawCorpLib\EbInterface;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use ClawCorpLib\Enums\EbPublishedState;

\defined('_JEXEC') or die;

final class EbEventTable implements \IteratorAggregate
{
  public const ZERO_DATETIME = '0000-00-00 00:00:00';

  private \stdClass $row;
  private DatabaseDriver $db;

  public function __construct(int $gidPublic, int $gidRegistered)
  {
    $this->db = Factory::getContainer()->get('DatabaseDriver');

    $this->row = (object)self::defaults($gidPublic, $gidRegistered);
    $this->validateTableColumns();
  }

  public static function load(int $eventId): self
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->createQuery();
    $query->select('*')->from('#__eb_events')->where('id = :id')->bind(':id', $eventId);
    $db->setQuery($query);

    $row = $db->loadObject();

    if (is_null($row)) {
      throw (new \Exception("Unable to find EB Event ID: $eventId"));
    }

    $result = new EbEventTable(0, 0);

    foreach ($result as $key => $value) {
      $result->$key = match (gettype($result->$key)) {
        'integer' => (int)$row->$key,
        'double' => (float)$row->$key, // floats typed as doubles
        default => $row->$key
      };
    }

    return $result;
  }

  private function validateTableColumns()
  {
    // Check defaults and database columns match keys
    static $ebEventColumns = null;
    if (null === $ebEventColumns) $ebEventColumns = array_keys($this->db->getTableColumns('#__eb_events'));

    $defaultKeys = array_keys((array)$this->row);
    sort($defaultKeys);
    sort($ebEventColumns);

    if ($defaultKeys != $ebEventColumns) {
?>
      <table>
        <tr>
          <td style="vertical-align:top;">
            <pre><?php print_r($defaultKeys) ?></pre>
          </td>
          <td style="vertical-align:top;">
            <pre><?php print_r($ebEventColumns) ?></pre>
          </td>
        </tr>
      </table>
<?php
      die('Database schema out of sync with default event column values.');
    }
  }

  // Keep object-like access, like the old code, so magic method...
  // Arrow-access to fields: $evt->title, $evt->event_date, etc.
  public function __get(string $name): mixed
  {
    if (property_exists($this->row, $name)) {
      return $this->row->$name;
    }

    throw new \Exception("Unknown column `$name` in __get in EbEventTable.");
  }

  public function __set(string $name, mixed $value): void
  {
    // Only allow keys we know about
    static $allowed = null;
    if ($allowed === null) {
      $allowed = array_flip(array_keys(self::defaults(0, 0)));
    }
    if (!isset($allowed[$name])) {
      throw new \InvalidArgumentException("Unknown EbEventTable key: {$name}");
    }

    // Normalize dates to SQL strings
    if ($value instanceof \Joomla\CMS\Date\Date) {
      $value = $value->toSql();
    } elseif ($value instanceof \DateTimeInterface) {
      $value = (new \Joomla\CMS\Date\Date($value))->toSql();
    }


    if (gettype($this->row->$name) !== gettype($value) && $name != 'params') {
      throw new \Exception("Type mismatch setting column name ($name as $value): " . gettype($this->row->$name) . ' != ' . gettype($value));
    }

    $this->row->$name = $value;
  }

  public function __isset(string $name): bool
  {
    return property_exists($this->row, $name);
  }

  // Instead of removing the property, reset to default to keep a stable shape
  public function __unset(string $name): void
  {
    if (property_exists($this->row, $name)) {
      $defaults = self::defaults(0, 0);
      $this->row->$name = $defaults[$name] ?? null;
    }
  }

  // For foreach ($evt as $k => $v) ...
  public function getIterator(): \Traversable
  {
    return new \ArrayIterator((array) $this->row);
  }

  // Pass this directly to $db->insertObject() / updateObject()
  public function toObject(): \stdClass
  {
    return $this->row;
  }

  // May be handy
  public function toArray(): array
  {
    return (array) $this->row;
  }

  // Fluent (->chained) overlay of changes (object or array)
  public function with(array|object $changes): self
  {
    $clone = clone $this;
    $arr = is_object($changes) ? (array) $changes : $changes;

    static $allowed = null; // cache
    if ($allowed === null) {
      $allowed = array_flip(array_keys(self::defaults(0, 0)));
    }

    foreach ($arr as $k => $v) {
      if (isset($allowed[$k])) {
        $clone->row->$k = $v;
      }
    }
    return $clone;
  }

  public function update()
  {
    if ($this->row->id == 0) {
      throw (new \Exception("Cannot update EB Event id 0"));
    }

    if (!$this->recordExists()) {
      throw (new \Exception("Cannot update non-existent EB Event record."));
    }

    $this->db->updateObject('#__eb_events', $this->row, 'id');
  }

  public function insert(): int
  {
    $query = $this->db->createQuery();
    $query->select('id')
      ->from('#__eb_events')
      ->where('alias = :alias')
      ->bind(':alias', $this->row->alias);
    $this->db->setQuery($query);
    $row = $this->db->loadResult();

    if ($row != null) {
      $this->row->id = $row;
      return 0;
    }

    // Highly unlikely to happen as it's set in the constructor, but...
    if (!$this->row->created_by) {
      throw new \Exception("Cannot insert with unset ownership");
    }

    $this->db->insertObject('#__eb_events', $this->row, 'id');

    $eventCategory = (object)[
      'id' => 0,
      'event_id' => $this->row->id,
      'category_id' => $this->row->main_category_id,
      'main_category' => 1
    ];

    $this->db->insertObject('#__eb_event_categories', $eventCategory, 'id');

    /* so far, no need to handle this, but the code is here if needed later
    foreach ($this->additionalCategoryIds as $categoryId) {
      $eventCategory = (object)[
        'id' => 0,
        'event_id' => $this->defaults->id,
        'category_id' => $categoryId,
        'main_category' => 0
      ];

      $this->db->insertObject('#__eb_event_categories', $eventCategory, 'id');
    }
     */

    return $this->row->id;
  }

  public static function updatePublishedState(int $eventId, EbPublishedState $state): string
  {
    if ($eventId == 0) return "Ignoring state change on 0";

    try {
      $row = EbEventTable::load($eventId);
    } catch (\Exception) {
      return "Failed to load event id $eventId to unpublish";
    }

    $log = "Unpublished event id $eventId";
    $row->published = $state->value;

    try {
      $row->update();
    } catch (\Exception $e) {
      $log = "Failed to unpublished event id $eventId {$e->getMessage()}";
    }

    return $log;
  }


  private function recordExists(): bool
  {
    $query = $this->db->createQuery();

    // Does this entry already exist?
    $query->select('id')
      ->from('#__eb_events')
      ->where('id = :id')
      ->bind(':id', $this->row->id);
    $this->db->setQuery($query);

    return is_null($this->db->loadResult()) ? false : true;
  }

  // Centralized defaults (require the gid parameters)
  private static function defaults(int $gidPublic, int $gidRegistered): array
  {
    return [
      'access' => $gidPublic,
      'activate_certificate_feature' => 0,
      'activate_tickets_pdf' => 0,
      'activate_waiting_list' => 0,
      'admin_email_body' => '',
      'alias' => '',
      'api_login' => '',
      'article_id' => 0,
      'attachment' => '',
      'cancel_before_date' => self::ZERO_DATETIME,
      'category_id' => 0,
      'certificate_bg_height' => 0,
      'certificate_bg_image' => null,
      'certificate_bg_left' => 0,
      'certificate_bg_top' => 0,
      'certificate_bg_width' => 0,
      'certificate_layout' => null,
      'collect_member_information' => '',
      'created_by' => 0,
      'created_date' => self::ZERO_DATETIME,
      'created_language' => '*',
      'currency_code' => '',
      'currency_symbol' => '',
      'custom_field_ids' => null,
      'custom_fields' => null,
      'cut_off_date' => self::ZERO_DATETIME,
      'deposit_amount' => '0.00',
      'deposit_type' => 0,
      'deposit_until_date' => self::ZERO_DATETIME,
      'description' => '',
      'discount_amounts' => '',
      'discount_groups' => '',
      'discount_type' => 1,
      'discount' => 0.00,
      'early_bird_discount_amount' => 0.00,
      'early_bird_discount_date' => self::ZERO_DATETIME,
      'early_bird_discount_type' => 1,
      'enable_auto_reminder' => 0,
      'enable_cancel_registration' => 1,
      'enable_coupon' => 0,
      'enable_sms_reminder' => 0,
      'enable_terms_and_conditions' => 2,
      'event_capacity' => 0,
      'event_date' => self::ZERO_DATETIME,
      'event_end_date' => self::ZERO_DATETIME,
      'event_detail_url' => '',
      'event_password' => '',
      'event_type' => 0,
      'featured' => 0,
      'first_reminder_frequency' => 'd',
      'fixed_daylight_saving_time' => 0,
      'fixed_group_price' => '0.00',
      'free_event_registration_status' => 1,
      'from_email' => '',
      'from_name' => '',
      'group_member_email_body' => '',
      'has_multiple_ticket_types' => 0,
      'hidden' => 0,
      'hits' => 0,
      'id' => 0,
      'image' => '',
      'image_alt' => '',
      'individual_price' => 0.00,
      'invoice_format' => '',
      'is_additional_date' => 0,
      'language' => '*',
      'late_fee_amount' => 0.00,
      'late_fee_date' => self::ZERO_DATETIME,
      'late_fee_type' => 1,
      'location_id' => 0,
      'main_category_id' => 0,
      'max_end_date' => self::ZERO_DATETIME,
      'max_group_number' => 0,
      'members_discount_apply_for' => 0,
      'meta_description' => '',
      'meta_keywords' => '',
      'min_group_number' => 0,
      'monthdays' => '',
      'notification_emails' => '',
      'offline_payment_registration_complete_url' => '',
      'ordering' => 0,
      'page_heading' => '',
      'page_title' => '',
      'params' => null,
      'parent_id' => 0,
      'payment_methods' => '',
      'paypal_email' => '',
      'prevent_duplicate_registration' => '',
      'price_text' => '',
      'private_booking_count' => 0,
      'publish_down' => self::ZERO_DATETIME,
      'publish_up' => self::ZERO_DATETIME,
      'published' => 1,
      'recurring_end_date' => self::ZERO_DATETIME,
      'recurring_frequency' => null,
      'recurring_occurrencies' => 0,
      'recurring_type' => null,
      'registrant_edit_close_date' => self::ZERO_DATETIME,
      'registrants_emailed' => 0,
      'registration_access' => $gidRegistered,
      'registration_approved_email_body' => '',
      'registration_complete_url' => '',
      'registration_form_message_group' => '',
      'registration_form_message' => '',
      'registration_handle_url' => '',
      'registration_start_date' => self::ZERO_DATETIME,
      'registration_type' => 1,
      'remind_before_x_days' => 0,
      'reminder_email_body' => '',
      'reminder_email_subject' => '',
      'reply_to_email' => '',
      'second_reminder_email_body' => '',
      'second_reminder_email_subject' => '',
      'second_reminder_frequency' => 'd',
      'send_emails' => -1,
      'send_first_reminder' => 0,
      'send_second_reminder' => 0,
      'send_third_reminder' => 0,
      'short_description' => '',
      'tax_rate' => 0.0,
      'thanks_message_offline' => '',
      'thanks_message' => '',
      'third_reminder_email_body' => '',
      'third_reminder_email_subject' => '',
      'third_reminder_frequency' => 'd',
      'thumb' => '',
      'ticket_bg_height' => 0,
      'ticket_bg_image' => null,
      'ticket_bg_left' => 0,
      'ticket_bg_top' => 0,
      'ticket_bg_width' => 0,
      'ticket_layout' => null,
      'ticket_prefix' => null,
      'ticket_start_number' => 1,
      'title' => '',
      'transaction_key' => '',
      'user_email_body_offline' => '',
      'user_email_body' => '',
      'user_email_subject' => '',
      'waiting_list_capacity' => 0,
      'weekdays' => null,
    ];
  }
}
