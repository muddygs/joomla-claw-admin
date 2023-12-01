<?php

namespace ClawCorpLib\Lib;

use ClawCorpLib\Helpers\Config;
use Joomla\CMS\Factory;
use ClawCorpLib\Lib\Aliases;

\defined('_JEXEC') or die;

class Ebmgmt
{
  public $ebEventColumns = [];
  private $defaults;
  private $additionalCategoryIds = [];
  /** @var \Joomla\Database\DatabaseDriver */
  private $db;

  function __construct(
    public string $eventAlias, 
    public int $mainCategoryId, 
    public string $itemAlias, 
    public string $title, 
    public string $description = "''")
  {
    $this->db = Factory::getContainer()->get('DatabaseDriver');

    $this->setDefaults();

    $this->set('alias', $itemAlias);
    $this->set('description', $description);
    $this->set('short_description', $description);
    $this->set('main_category_id', $mainCategoryId, false);
    $this->set('ordering', $this->getOrdering());
    $this->set('title', $title);
  }

  public function insert(bool $force = false): int
  {
    if ( $force == false )
    {
      $query = 'SELECT id FROM #__eb_events WHERE alias = '.$this->get('alias');
      $this->db->setQuery($query);
      $row = $this->db->loadResult();

      if ( $row != null ) return 0;
    }

    $query = $this->db->getQuery(true);
    $columns = array_keys($this->defaults);
    $values = array_values($this->defaults);
    $query
        ->insert($this->db->quoteName('#__eb_events'))
        ->columns($this->db->quoteName($columns))
        ->values(implode(',', $values));
    $this->db->setQuery($query);
    //echo '<pre>'.$query->__toString().'</pre>'; return 0;
    $this->db->execute();
    $eventId = $this->db->insertid();

    $eventCategory = [
      'id' => 0,
      'event_id' => $eventId,
      'category_id' => $this->defaults['main_category_id'],
      'main_category' => 1
    ];
    $query = $this->db->getQuery(true);
    $columns = array_keys($eventCategory);
    $values = array_values($eventCategory);
    $query
        ->insert($this->db->quoteName('#__eb_event_categories'))
        ->columns($this->db->quoteName($columns))
        ->values(implode(',', $values));
    $this->db->setQuery($query);
    $this->db->execute();

    foreach ( $this->additionalCategoryIds AS $categoryId )
    {
      $eventCategory = [
        'id' => 0,
        'event_id' => $eventId,
        'category_id' => $categoryId,
        'main_category' => 0
      ];
      $query = $this->db->getQuery(true);
      $columns = array_keys($eventCategory);
      $values = array_values($eventCategory);
      $query
          ->insert($this->db->quoteName('#__eb_event_categories'))
          ->columns($this->db->quoteName($columns))
          ->values(implode(',', $values));
      $this->db->setQuery($query);
      $this->db->execute();
    }

    $this->updateMapping($eventId);

    return $eventId;
  }

  private function updateMapping(int $eventId): void
  {
    $query = $this->db->getQuery(true);

    // Does this entry already exist?
    $query->select('eventid')
      ->from('#__claw_eventid_mapping')
      ->where('eventid = :eventid')
      ->bind(':eventid', $eventId);
    $this->db->setQuery($query);
    $result = $this->db->loadResult();

    if ( $result != null ) return;

    $query = $this->db->getQuery(true);
    $query
        ->insert($this->db->quoteName('#__claw_eventid_mapping'))
        ->columns($this->db->quoteName(['eventid','alias']))
        ->values(implode(',', $this->db->quote([$eventId, $this->eventAlias])));
    $this->db->setQuery($query);
    $this->db->execute();
  }


  /**
   * Sets a database column value, defaults to quoting value
   * @param $key Column name
   * @param $value Value to set
   * @param $quoted (optional) Default: true
   */
  public function set(string $key, $value, bool $quoted = true): void
  {
    if ( !array_key_exists($key, $this->defaults))
    {
      die('Unknown column name: '.$key);
    }

    $this->defaults[$key] = $quoted ? $this->db->quote($value) : $value;
  }

  public function addAdditionalCategoryId(int $categoryId)
  {
    $this->additionalCategoryIds[] = $categoryId;
  }

  /**
   * Gets a database column value
   * @param $key Column name
   * @return string Column Value (quoted if called with set() quoted)
   */
  public function get(string $key): string
  {
    if ( !array_key_exists($key, $this->defaults))
    {
      die('Unknown column name: '.$key);
    }

    return $this->defaults[$key];
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

    $this->defaults = [
      'access'=>	1,
      'activate_certificate_feature'=>	0,
      'activate_tickets_pdf'=>	0,
      'activate_waiting_list'=>	0,
      'admin_email_body'=>	"''",
      'alias'=>	"''",
      'api_login'=>	"''",
      'article_id'=>	0,
      'attachment'=>	"''",
      'cancel_before_date'=> "'0000-00-00 00:00:00'",
      'category_id'=>	0,
      'certificate_bg_height'=>	0,
      'certificate_bg_image'=>	'NULL',
      'certificate_bg_left'=>	0,
      'certificate_bg_top'=>	0,
      'certificate_bg_width'=>	0,
      'certificate_layout'=>	'NULL',
      'collect_member_information'=>	"''",
      'created_by'=>	224,
      'created_date' => "'0000-00-00 00:00:00'",
      'created_language'=>"'*'",
      'currency_code'=>	"''",
      'currency_symbol'=>	"''",
      'custom_field_ids'=>	'NULL',
      'custom_fields'=>	'NULL',
      'cut_off_date'=>	"'0000-00-00 00:00:00'",
      'deposit_amount'=>	'0.00',
      'deposit_type'=>	0,
      'deposit_until_date' => "'0000-00-00 00:00:00'",
      'description'=>	"''",
      'discount_amounts'=>	"''",
      'discount_groups'=>	"''",
      'discount_type'=>	1,
      'discount'=>	'0.00',
      'early_bird_discount_amount'=>	'0.00',
      'early_bird_discount_date'=>	"'0000-00-00 00:00:00'",
      'early_bird_discount_type'=>	1,
      'enable_auto_reminder'=>	'NULL',
      'enable_cancel_registration'=>	1,
      'enable_coupon'=>	0,
      'enable_sms_reminder' => 0,
      'enable_terms_and_conditions'=>	2,
      'event_capacity'=> 0,
      'event_date'=>	"'0000-00-00 00:00:00'",
      'event_end_date'=>	"'0000-00-00 00:00:00'",
      'event_detail_url' => "''",
      'event_password'=>	"''",
      'event_type'=>	0,
      'featured'=>	0,
      'first_reminder_frequency' => "'d'",
      'fixed_daylight_saving_time'=>	0,
      'fixed_group_price'=>	'0.00',
      'free_event_registration_status'=>	1,
      'from_email'=>	"''",
      'from_name'=>	"''",
      'group_member_email_body' => "''",
      'has_multiple_ticket_types'=>	0,
      'hidden'=>	0,
      'hits'=>	0,
      'id'=>	0,
      'image'=>	"''",
      'image_alt' => "''",
      'individual_price'=>	'0.00',
      'invoice_format'=>	"''",
      'is_additional_date'=>	0,
      'language'=>	"'*'",
      'late_fee_amount'=>	'0.00',
      'late_fee_date'=>	"'0000-00-00 00:00:00'",
      'late_fee_type'=>	1,
      'location_id'=>	0,
      'main_category_id'=> 0,
      'max_end_date'=>	"'0000-00-00 00:00:00'",
      'max_group_number'=>	0,
      'members_discount_apply_for'=>	0,
      'meta_description'=>	"''",
      'meta_keywords'=>	"''",
      'min_group_number'=>	0,
      'monthdays'=>	"''",
      'notification_emails'=>	"''",
      'offline_payment_registration_complete_url'=>	"''",
      'ordering'=>	0,
      'page_heading'=>	"''",
      'page_title'=>	"''",
      'params'=> 'NULL',
      'parent_id'=>	0,
      'payment_methods'=>	"''",
      'paypal_email'=>	"''",
      'prevent_duplicate_registration'=>	"''",
      'price_text'=>	"''",
      'private_booking_count' => 0,
      'publish_down'=>	"'0000-00-00 00:00:00'",
      'publish_up'=>	"'0000-00-00 00:00:00'",
      'published'=>	1,
      'recurring_end_date'=>	"'0000-00-00 00:00:00'",
      'recurring_frequency'=>	'NULL',
      'recurring_occurrencies'=>	0,
      'recurring_type'=>	'NULL',
      'registrant_edit_close_date'=>	"'0000-00-00 00:00:00'",
      'registrants_emailed' => 0,
      'registration_access'=>	14,
      'registration_approved_email_body'=>	"''",
      'registration_complete_url'=>	"''",
      'registration_form_message_group'=>	"''",
      'registration_form_message'=>	"''",
      'registration_handle_url'=>	"''",
      'registration_start_date'=>	"'0000-00-00 00:00:00'",
      'registration_type'=>	1,
      'remind_before_x_days'=>	'NULL',
      'reminder_email_body'=>	"''",
      'reminder_email_subject' => "''",
      'reply_to_email' => "''",
      'second_reminder_email_body'=>	"''",
      'second_reminder_email_subject' => "''",
      'second_reminder_frequency' => "'d'",
      'send_emails'=>	-1,
      'send_first_reminder'=>	0,
      'send_second_reminder'=>	0,
      'send_third_reminder' => 0,
      'short_description'=>	"''",
      'tax_rate'=>	'0.00',
      'thanks_message_offline'=>	"''",
      'thanks_message'=>	"''",
      'third_reminder_email_body' => "''",
      'third_reminder_email_subject' => "''",
      'third_reminder_frequency' => "'d'",
      'thumb'=>	"''",
      'ticket_bg_height'=>	0,
      'ticket_bg_image'=>	'NULL',
      'ticket_bg_left'=>	0,
      'ticket_bg_top'=>	0,
      'ticket_bg_width'=>	0,
      'ticket_layout'=>	'NULL',
      'ticket_prefix'=>	'NULL',
      'ticket_start_number'=>	1,
      'title'=>	"''",
      'transaction_key'=>	"''",
      'user_email_body_offline'=>	"''",
      'user_email_body'=>	"''",
      'user_email_subject' => "''",
      'waiting_list_capacity' => 0,
      'weekdays'=>	'NULL',
    ];

    // Check defaults and database columns match keys
    $defaultKeys = array_keys($this->defaults);
    sort($defaultKeys);
    sort($this->ebEventColumns);

    if ( $defaultKeys != $this->ebEventColumns ) {
    ?>
      <table>
        <tr>
          <td style="vertical-align:top;"><pre><?php print_r($defaultKeys) ?></pre></td>
          <td style="vertical-align:top;"><pre><?php print_r($this->ebEventColumns) ?></pre></td>
        </tr>
      </table>
    <?php
      die('Database schema out of sync with default event column values.');
    }
  }

  /**
   * Helper function for creating discount bundles among events by $ amount
   * @param array The Event IDs
   * @param int Dollar amount
   * @return True if added, False on error or duplicate (by title)
   */
  public static function addDiscountBundle(array $eventIds, int $dollarAmount, string $startDate = '0000-00-00 00:00:00', string $endDate = '0000-00-00 00:00:00'): string
  {
    $db = Factory::getContainer()->get('DatabaseDriver');
    // Create Title
    $titles = [];

    foreach ( $eventIds as $eventId ) {
        $row = ClawEvents::loadEventRow($eventId);
        $titles[] = $row->title;
    }

    $title = implode('-',$titles);

    // Does this title exist? If so, do nothing
    $query = $db->getQuery(true)
        ->select('title')
        ->from('#__eb_discounts')
        ->where($db->qn('title'). " = " . $db->q($title));
    $db->setQuery($query);
    $result = $db->loadResult();

    if ( $result != null ) return "Skipping duplicate discount: $title";

    $query = $db->getQuery(true);

    $data = [
      'id' => 0,
      'title' => $title,
      'event_ids' => implode(',',$eventIds),
      'discount_amount' => $dollarAmount,
      'from_date' => $startDate,
      'to_date' => $endDate,
      'times' => 0,
      'used' => 0,
      'published' => 1,
      'number_events' => 0,
      'discount_type' => 1
    ];

    $query
    ->insert($db->quoteName('#__eb_discounts'))
    ->columns($db->quoteName(array_keys($data)))
    ->values(implode(',', $db->quote($data)));
    $db->setQuery($query);
    $result = $db->execute();
    $rowId = $db->insertid();

    if ( $result === false ) return "Error adding discount: $title";

    // Add discount indexes
    $columns = ['id', 'discount_id', 'event_id'];

    foreach ( $eventIds AS $eventId ) {
        $values = [0, $rowId, $eventId];
        $query = $db->getQuery(true);
        $query->insert('#__eb_discount_events')
            ->columns($db->qn($columns))
            ->values(implode(',', $values));
        $db->setQuery($query);
        $db->execute();
    }

    return "Added discount: $title";
  }

  /**
   * cron task to hide/show shifts when they are full or become available
   */
  static function autoHideShowShifts(): void {
    $db = Factory::getContainer()->get('DatabaseDriver');

    $eventList = ClawEvents::getEventList();

    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ( $eventList AS $eventInfo ) {
      if ( ! $eventInfo->mainAllowed ) continue;

      // Skip past events
      if ( $eventInfo->end_date < date('Y-m-d H:i:s') ) continue;
      
      echo 'Event: '.$eventInfo->description.PHP_EOL;

      $prefix = $eventInfo->shiftPrefix;

      // HIDE 
      $query = <<< SQL
        SELECT e.id
        FROM #__eb_events e
        JOIN (select r.id, r.event_id, count(r.id) as mycount
          from #__eb_registrants r WHERE r.published = 1
          group by r.event_id ) tsum ON tsum.event_id = e.id
        WHERE e.published =1 AND e.hidden != 1 AND e.event_capacity > 0 AND tsum.mycount >= e.event_capacity AND e.alias LIKE '{$prefix}%'
  SQL;

      $db->setQuery($query);
      $fullShifts = $db->loadColumn();

      if ( sizeof($fullShifts) > 0 )
      {
        $eventIds = join(',',$fullShifts);

        $q = 'UPDATE #__eb_events SET hidden=1 WHERE id IN ('.$eventIds.')';
        $db->setQuery($q);
        $db->execute();
      }

      echo "Shifts hidden: ".sizeof($fullShifts).PHP_EOL;

      // SHOW
      
      $query = <<< SQL
      select e.id, e.title, tsum.mycount, e.event_capacity, e.hidden
      FROM #__eb_events e
      JOIN (select r.id, r.event_id, count(r.id) as mycount
        from #__eb_registrants r WHERE r.published = 1
        group by r.event_id ) tsum ON tsum.event_id = e.id
      WHERE e.published =1 AND e.hidden = 1 AND e.event_capacity > 0 AND tsum.mycount < e.event_capacity AND e.alias LIKE '{$prefix}%'
  SQL;

      $db->setQuery($query);
      $fullShifts = $db->loadColumn();

      if ( sizeof($fullShifts) > 0 )
      {
        $eventIds = join(',',$fullShifts);

        $q = 'UPDATE #__eb_events SET hidden=0 WHERE id IN ('.$eventIds.')';
        $db->setQuery($q);
        $db->execute();
      }

      echo "Shifts shown: ".sizeof($fullShifts);
    }
  }

  public static function rebuildEventIdMapping()
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $aliases = Config::getActiveEventAliases();
    $dates = [];

    foreach ( $aliases AS $alias ) {
      $events = new ClawEvents($alias);

      $info = $events->getClawEventInfo();

      if ( 'refunds' == $info->description ) continue;

      $dates[$alias] = (object)[
        'start' => $info->start_date,
        'end' => $info->end_date
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

      foreach ( $ebEvents AS $e ) {
        if ( $e->event_date == '0000-00-00 00:00:00' ) continue;

        foreach ( $dates AS $alias => $date ) {
          if ( $e->event_date >= $date->start && $e->event_date <= $date->end ) {
            $query = $db->getQuery(true);
            $query
              ->insert($db->quoteName('#__claw_eventid_mapping'))
              ->columns($db->quoteName(['eventid','alias']))
              ->values(implode(',', $db->q([$e->id, $alias])));
            $db->setQuery($query);
            $db->execute();

            break;
          }
        }
      }

      $db->transactionCommit();
    } catch ( \Exception $e ) {
      $db->transactionRollback();
      throw $e;
    }
  }
}
