<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Lib\EventConfig;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

\defined('_JEXEC') or die;

class Ebmgmt
{
  public $ebEventColumns = [];
  private $defaults;
  private $additionalCategoryIds = [];
  /** @var \Joomla\Database\DatabaseDriver */
  private $db;

  #TODO: pass in EventInfo instead of an alias string
  function __construct(
    public string $eventAlias,
    public int $mainCategoryId,
    public string $itemAlias,
    public string $title,
    public string $description = ''
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');

    $this->setDefaults();
    $this->setParameters();
  }

  private function setParameters()
  {
    $this->set('alias', $this->itemAlias);
    $this->set('description', $this->description);
    $this->set('short_description', $this->description);
    $this->set('main_category_id', $this->mainCategoryId);
    $this->set('ordering', $this->getOrdering());
    $this->set('title', $this->title);
  }

  public function load(int $id)
  {
    $query = $this->db->getQuery(true);
    $query->select('*')->from('#__eb_events')->where('id = :id')->bind(':id', $id);
    $this->db->setQuery($query);

    $result = $this->db->loadObject();

    if (is_null($result)) {
      throw (new \Exception("Unable to find EB Event ID: $id"));
    }

    $this->defaults = clone $result;
    $oldOrdering = $this->defaults->ordering;
    $this->setParameters();
    $this->defaults->ordering = $oldOrdering;
  }

  public function update()
  {
    if ($this->defaults->id == 0) {
      throw (new \Exception("Cannot update EB Event id 0"));
    }

    if (!$this->recordExists()) {
      throw (new \Exception("Cannot update non-existent EB Event record."));
    }

    $this->db->updateObject('#__eb_events', $this->defaults, 'id');
  }

  public function insert(bool $force = false): int
  {
    if (false === $force) {
      $query = $this->db->getQuery(true);
      $query->select('id')
        ->from('#__eb_events')
        ->where('alias = :alias')
        ->bind(':alias', $this->defaults->alias);
      $this->db->setQuery($query);
      $row = $this->db->loadResult();

      if ($row != null) return 0;
    }

    $this->db->insertObject('#__eb_events', $this->defaults, 'id');

    $eventCategory = (object)[
      'id' => 0,
      'event_id' => $this->defaults->id,
      'category_id' => $this->defaults->main_category_id,
      'main_category' => 1
    ];

    $this->db->insertObject('#__eb_event_categories', $eventCategory, 'id');

    foreach ($this->additionalCategoryIds as $categoryId) {
      $eventCategory = (object)[
        'id' => 0,
        'event_id' => $this->defaults->id,
        'category_id' => $categoryId,
        'main_category' => 0
      ];

      $this->db->insertObject('#__eb_event_categories', $eventCategory, 'id');
    }

    $this->updateMapping();

    return $this->defaults->id;
  }

  private function recordExists(): bool
  {
    $query = $this->db->getQuery(true);

    // Does this entry already exist?
    $query->select('id')
      ->from('#__eb_events')
      ->where('id = :id')
      ->bind(':id', $this->defaults->id);
    $this->db->setQuery($query);

    return is_null($this->db->loadResult()) ? false : true;
  }

  private function updateMapping(): void
  {
    $query = $this->db->getQuery(true);

    // Does this entry already exist?
    $query->select('eventid')
      ->from('#__claw_eventid_mapping')
      ->where('eventid = :eventid')
      ->bind(':eventid', $this->defaults->id);
    $this->db->setQuery($query);
    $result = $this->db->loadResult();

    if ($result != null) return;

    $query = $this->db->getQuery(true);
    $query
      ->insert($this->db->quoteName('#__claw_eventid_mapping'))
      ->columns($this->db->quoteName(['eventid', 'alias']))
      ->values(implode(',', (array)$this->db->quote([$this->defaults->id, $this->eventAlias])));
    $this->db->setQuery($query);
    $this->db->execute();
  }


  /**
   * Sets a database column value, defaults to quoting value
   * @param $key Column name
   * @param $value Value to set
   * @param $quoted (optional) Default: true
   */
  public function set(string $key, $value): void
  {
    if (!property_exists($this->defaults, $key)) {
      die('Unknown column name: ' . $key);
    }

    $this->defaults->$key = $value;
  }

  public function addAdditionalCategoryId(int $categoryId)
  {
    $this->additionalCategoryIds[] = $categoryId;
  }

  /**
   * Gets a database column value
   * @param $key Column name
   * @return mixed Column Value
   */
  public function get(string $key): mixed
  {
    if (!property_exists($this->defaults, $key)) {
      die('Unknown column name: ' . $key);
    }

    return $this->defaults->$key;
  }

  private function getOrdering(): int
  {
    $query = "SELECT MAX(ordering) FROM `#__eb_events` WHERE 1";
    $this->db->setQuery($query);
    return $this->db->loadResult() + 1;
  }

  /**
   * Establishes default values for a new event row. Will die if schema for #__eb_events
   * is not met. This is to protect against future updates to the events schema. Call
   * set() to provide values prior to insert().
   */
  private function setDefaults(): void
  {
    $this->ebEventColumns = array_keys($this->db->getTableColumns('#__eb_events'));

    // Load from global config, defaults to clean Joomla group install
    $componentParams = ComponentHelper::getParams('com_claw');
    // These params are actually ACL ids, not group ids
    $gid_public = $componentParams->get('packaginfo_public_group', 1);
    $gid_registered = $componentParams->get('packaginfo_registered_group', 14);

    $this->defaults = (object)[
      'access' => $gid_public,
      'activate_certificate_feature' => 0,
      'activate_tickets_pdf' => 0,
      'activate_waiting_list' => 0,
      'admin_email_body' => '',
      'alias' => '',
      'api_login' => '',
      'article_id' => 0,
      'attachment' => '',
      'cancel_before_date' => '0000-00-00 00:00:00',
      'category_id' => 0,
      'certificate_bg_height' => 0,
      'certificate_bg_image' => null,
      'certificate_bg_left' => 0,
      'certificate_bg_top' => 0,
      'certificate_bg_width' => 0,
      'certificate_layout' => null,
      'collect_member_information' => '',
      'created_by' => 224,
      'created_date' => '0000-00-00 00:00:00',
      'created_language' => '*',
      'currency_code' => '',
      'currency_symbol' => '',
      'custom_field_ids' => null,
      'custom_fields' => null,
      'cut_off_date' => '0000-00-00 00:00:00',
      'deposit_amount' => '0.00',
      'deposit_type' => 0,
      'deposit_until_date' => '0000-00-00 00:00:00',
      'description' => '',
      'discount_amounts' => '',
      'discount_groups' => '',
      'discount_type' => 1,
      'discount' => '0.00',
      'early_bird_discount_amount' => '0.00',
      'early_bird_discount_date' => '0000-00-00 00:00:00',
      'early_bird_discount_type' => 1,
      'enable_auto_reminder' => null,
      'enable_cancel_registration' => 1,
      'enable_coupon' => 0,
      'enable_sms_reminder' => 0,
      'enable_terms_and_conditions' => 2,
      'event_capacity' => 0,
      'event_date' => '0000-00-00 00:00:00',
      'event_end_date' => '0000-00-00 00:00:00',
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
      'individual_price' => '0.00',
      'invoice_format' => '',
      'is_additional_date' => 0,
      'language' => '*',
      'late_fee_amount' => '0.00',
      'late_fee_date' => '0000-00-00 00:00:00',
      'late_fee_type' => 1,
      'location_id' => 0,
      'main_category_id' => 0,
      'max_end_date' => '0000-00-00 00:00:00',
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
      'publish_down' => '0000-00-00 00:00:00',
      'publish_up' => '0000-00-00 00:00:00',
      'published' => 1,
      'recurring_end_date' => '0000-00-00 00:00:00',
      'recurring_frequency' => null,
      'recurring_occurrencies' => 0,
      'recurring_type' => null,
      'registrant_edit_close_date' => '0000-00-00 00:00:00',
      'registrants_emailed' => 0,
      'registration_access' => $gid_registered,
      'registration_approved_email_body' => '',
      'registration_complete_url' => '',
      'registration_form_message_group' => '',
      'registration_form_message' => '',
      'registration_handle_url' => '',
      'registration_start_date' => '0000-00-00 00:00:00',
      'registration_type' => 1,
      'remind_before_x_days' => null,
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

    // Check defaults and database columns match keys
    $defaultKeys = array_keys((array)$this->defaults);
    sort($defaultKeys);
    sort($this->ebEventColumns);

    if ($defaultKeys != $this->ebEventColumns) {
?>
      <table>
        <tr>
          <td style="vertical-align:top;">
            <pre><?php print_r($defaultKeys) ?></pre>
          </td>
          <td style="vertical-align:top;">
            <pre><?php print_r($this->ebEventColumns) ?></pre>
          </td>
        </tr>
      </table>
<?php
      die('Database schema out of sync with default event column values.');
    }
  }

  public static function rebuildEventIdMapping()
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $aliases = EventConfig::getActiveEventAliases();
    $dates = [];

    foreach ($aliases as $alias) {
      $info = new EventInfo($alias);

      if (EventTypes::refunds == $info->eventType) continue;

      $dates[$alias] = (object)[
        'start' => $info->start_date->toSql(),
        'end' => $info->end_date->toSql()
      ];
    }

    // Load all event rows
    $query = $db->getQuery(true);
    $query->select(['id', 'event_date', 'event_end_date'])
      ->from('#__eb_events')
      ->where('published = 1')
      ->order('id');
    $db->setQuery($query);
    $ebEvents = $db->loadObjectList('id');

    try {
      $db->transactionStart(true);

      $db->truncateTable('#__claw_eventid_mapping');

      foreach ($ebEvents as $e) {
        if ($e->event_date == '0000-00-00 00:00:00') continue;

        foreach ($dates as $alias => $date) {
          if ($e->event_date >= $date->start && $e->event_date <= $date->end) {
            $query = $db->getQuery(true);
            $query
              ->insert($db->quoteName('#__claw_eventid_mapping'))
              ->columns($db->quoteName(['eventid', 'alias']))
              ->values(implode(',', (array)$db->quote([$e->id, $alias])));
            $db->setQuery($query);
            $db->execute();

            break;
          }
        }
      }

      $db->transactionCommit();
    } catch (\Exception $e) {
      $db->transactionRollback();
      throw $e;
    }
  }
}
