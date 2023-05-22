<?php

namespace ClawCorpLib\Lib;

use Joomla\CMS\Factory;

use ClawCorpLib\Events\AbstractEvent;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Lib\EventInfo;;
use ClawCorpLib\Lib\Aliases;
use UnexpectedValueException;

class Ebmgmt
{
  public $ebEventColumns = [];
  private $defaults;
  private $additionalCategoryIds = [];

  function __construct(int $mainCategoryId, string $alias, string $title, string $description = "''")
  {
    $this->setDefaults();

    $this->set('alias', $alias);
    $this->set('description', $description);
    $this->set('short_description', $description);
    $this->set('main_category_id', $mainCategoryId, false);
    $this->set('ordering', $this->getOrdering());
    $this->set('title', $title);
  }

  function insert($force = false): int
  {
    $db = Factory::getDbo();

    if ( $force == false )
    {
      $query = 'SELECT id FROM #__eb_events WHERE alias = '.$this->get('alias');
      $db->setQuery($query);
      $row = $db->loadResult();

      if ( $row != null ) return 0;
    }

    $query = $db->getQuery(true);
    $columns = array_keys($this->defaults);
    $values = array_values($this->defaults);
    $query
        ->insert($db->quoteName('#__eb_events'))
        ->columns($db->quoteName($columns))
        ->values(implode(',', $values));
    $db->setQuery($query);
    //echo '<pre>'.$query->__toString().'</pre>'; return 0;
    $db->execute();
    $eventId = $db->insertid();

    $eventCategory = [
      'id' => 0,
      'event_id' => $eventId,
      'category_id' => $this->defaults['main_category_id'],
      'main_category' => 1
    ];
    $query = $db->getQuery(true);
    $columns = array_keys($eventCategory);
    $values = array_values($eventCategory);
    $query
        ->insert($db->quoteName('#__eb_event_categories'))
        ->columns($db->quoteName($columns))
        ->values(implode(',', $values));
    $db->setQuery($query);
    $db->execute();

    foreach ( $this->additionalCategoryIds AS $categoryId )
    {
      $eventCategory = [
        'id' => 0,
        'event_id' => $eventId,
        'category_id' => $categoryId,
        'main_category' => 0
      ];
      $query = $db->getQuery(true);
      $columns = array_keys($eventCategory);
      $values = array_values($eventCategory);
      $query
          ->insert($db->quoteName('#__eb_event_categories'))
          ->columns($db->quoteName($columns))
          ->values(implode(',', $values));
      $db->setQuery($query);
      $db->execute();
    }

    return $eventId;
  }

  /**
   * Sets a database column value, defaults to quoting value
   * @param $key Column name
   * @param $value Value to set
   * @param $q (optional) Default: true, set to false to NOT quote
   */
  function set(string $key, $value, bool $q = true): void
  {
    $db = Factory::getDbo();

    if ( !array_key_exists($key, $this->defaults))
    {
      die('Unknown column name: '.$key);
    }

    $this->defaults[$key] = $q ? $db->q($value) : $value;
  }

  function addAdditionalCategoryId(int $categoryId)
  {
    $this->additionalCategoryIds[] = $categoryId;
  }

  /**
   * Gets a database column value
   * @param $key Column name
   * @return string Column Value (quoted if called with set() quoted)
   */
  function get(string $key): string
  {
    if ( !array_key_exists($key, $this->defaults))
    {
      die('Unknown column name: '.$key);
    }

    return $this->defaults[$key];
  }

  private function getOrdering(): int
  {
    $db = Factory::getDbo();
    $query = "SELECT MAX(ordering) FROM `#__eb_events` WHERE 1";
    $db->setQuery($query);
    return $db->loadResult() + 1;

  }

  /**
   * Establishes default values for a new event row. Will die if schema for #__eb_events
   * is not met. This is to protect against future updates to the events schema. Call
   * set() to provide values prior to insert().
   */
  private function setDefaults(): void
  {
    $db = Factory::getDbo();

    $q = <<<SQL
    SELECT `COLUMN_NAME`
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 's1fi8_eb_events' AND TABLE_SCHEMA = 'clawinfo_td7iAz07zZAglPSe'
    ORDER BY `COLUMNS`.`COLUMN_NAME` ASC
SQL;
    $db->setQuery($q);
    $this->ebEventColumns = $db->loadColumn();

    $this->defaults = [
      'access'=>	1,
      'activate_certificate_feature'=>	0,
      'activate_tickets_pdf'=>	0,
      'activate_waiting_list'=>	0,
      'admin_email_body'=>	"''",
      'alias'=>	"''",
      'image_alt' => "''",
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

    if ( $defaultKeys != $this->ebEventColumns )
    {?>
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
  public static function addDiscountBundle(array $eventIds, int $dollarAmount, string $startDate = '0000-00-00 00:00:00', string $endDate = '0000-00-00 00:00:00'): bool {
    $db = Factory::getDbo();
    // Create Title
    $titles = [];

    foreach ( $eventIds as $eventId ) {
        $row = clawEvents::loadEventRow($eventId);
        $titles[] = $row->title;
    }

    $title = $db->q(implode('-',$titles));

    // Does this title exist? If so, do nothing
    $query = $db->getQuery(true)
        ->select('title')
        ->from('#__eb_discounts')
        ->where($db->qn('title'). " = " . $title);
    $db->setQuery($query);
    $result = $db->loadResult();

    if ( $result != null ) return false;

    $query = $db->getQuery(true);
    $columns = ['id', 'title', 'event_ids', 'discount_amount', 'from_date', 'to_date', 'times', 'used', 'published', 'number_events', 'discount_type'];
    $values = [0, $title, $db->q(implode(',',$eventIds)), $db->q($dollarAmount), $db->q($startDate), $db->q($endDate), 0, 0, 1, 0, 1];
    $query
    ->insert($db->quoteName('#__eb_discounts'))
    ->columns($db->quoteName($columns))
    ->values(implode(',', $values));
    $db->setQuery($query);
    $result = $db->execute();
    $rowId = $db->insertid();


    if ( $result === false ) return false;

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

    return true;
  }

  /**
   * cron task to hide/show shifts when they are full or become available
   */
  static function autoHideShowShifts(): void {
    $db = Factory::getDbo();

    $events = new clawEvents(Aliases::current);
    $eventInfo = $events->getClawEventInfo();
    $prefix = $eventInfo->shiftPrefix;

    // HIDE 

    $query = <<< SQL
      select e.id, e.title, tsum.mycount, e.event_capacity, e.hidden
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

    echo "Shifts hidden: ".sizeof($fullShifts);

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

    echo "; Shifts shown: ".sizeof($fullShifts);
  }

  /**
   * Warning: This will overwrite any good standing settings for the selected
   * event. 
   * @param string $eventAlias 
   * @return void 
   * @throws RuntimeException 
   * @throws UnexpectedValueException 
   */
  public static function setGoodStanding(string $eventAlias): int
  {
    $db = Factory::getDbo();

    $eventId = clawEvents::getEventId($eventAlias, true);
    
    if ( 0 == $eventId ) 
    {
      echo "Unknown event alias: $eventAlias";
      return 0;
    }

    $insertQuery = [];

    $registrants = registrants::byEventId($eventId);

    $fieldId = clawEvents::getFieldId('Z_VOL_GOODSTANDING');
    $yes = $db->q('["Yes"]');
    $registrantIds = [];

    foreach ( $registrants AS $registrant ) 
    {
      $registrant = registrants::castRegistrant($registrant);
      $records = $registrant->records();

      if ( count($records) == 0 ) continue;

      $record = reset($records);

      if ( $record === false ) continue;

      $record = registrants::castRecord($record);

      $insertQuery[] = <<< SQL
INSERT INTO `#__eb_field_values`(`id`, `registrant_id`, `field_id`, `field_value`)
VALUES (0, {$record->registrant->id}, $fieldId, $yes);

SQL;
      $registrantIds[] = $record->registrant->id;
    }

    // Wipe existing entries for these registrants, and then
    // Insert all the new values

    $query = "DELETE FROM #__eb_field_values WHERE field_id=$fieldId AND registrant_id IN (".implode(',',$registrantIds).')';
    $db->setQuery($query);
    $db->execute();

    foreach ( $insertQuery AS $q )
    {
      $db->setQuery($q);
      $db->execute();
    }

    return count($registrantIds);
  }
}
