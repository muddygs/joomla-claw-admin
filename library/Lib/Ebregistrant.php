<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;

\defined('_JEXEC') or die;

class Ebregistrant
{
  public $ebRegistrantsColumns = [];
  private $defaults;
  /** @var \Joomla\Database\DatabaseDriver */
  private $db;

  function __construct(
    public int $eventId,
    public int $uid,
    public array $field_values = []
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');

    $this->setDefaults();

    // $this->set('alias', $itemAlias);
  }

  public function copyFrom(int $registrantId): void
  {
    $query = $this->db->getQuery(true);
    $query
      ->select('*')
      ->from('#__eb_registrants')
      ->where($this->db->quoteName('id') . ' = ' . $this->db->quote($registrantId));
    $this->db->setQuery($query);
    $registrant = $this->db->loadAssoc();

    $coreFields = ['first_name', 'last_name', 'address', 'address2', 'city', 'zip', 'country', 'state', 'phone', 'email'];

    foreach ($coreFields as $field) {
      $this->set($field, $registrant[$field]);
    }
  }

  public function insert(): int
  {
    // Get next invoice # as expected by getInvoiceNumber, which typically uses registration row
    $row = (object)[
      'event_id' => $this->get('event_id'),
      'user_id' => $this->get('user_id')
    ];
    $invoiceNumber = Registrant::getInvoiceNumber($row);
    $this->set('invoice_number', $invoiceNumber);
    $this->set('formatted_invoice_number', $invoiceNumber);

    $query = $this->db->getQuery(true);
    $columns = array_keys($this->defaults);
    $values = array_values($this->defaults);
    $query
      ->insert($this->db->quoteName('#__eb_registrants'))
      ->columns($this->db->quoteName($columns))
      ->values(implode(',', $values));
    $this->db->setQuery($query);
    $this->db->execute();
    $registrantId = $this->db->insertid();

    return $registrantId;
  }

  /**
   * Sets a database column value, defaults to quoting value
   * @param $key Column name
   * @param $value Value to set
   * @param $quoted (optional) Default: true
   */
  public function set(string $key, $value, bool $quoted = true): void
  {
    if (!array_key_exists($key, $this->defaults)) {
      die('Unknown column name: ' . $key);
    }

    $this->defaults[$key] = $quoted ? $this->db->quote($value) : $value;
  }

  /**
   * Gets a database column value
   * @param $key Column name
   * @return string Column Value (quoted if called with set() quoted)
   */
  public function get(string $key): string
  {
    if (!array_key_exists($key, $this->defaults)) {
      die('Unknown column name: ' . $key);
    }

    return $this->defaults[$key];
  }

  /**
   * Establishes default values for a new event row. Will die if schema for #__eb_registrants
   * is not met. This is to protect against future updates to the events schema. Call
   * set() to provide values prior to insert().
   */
  private function setDefaults(): void
  {
    $this->ebRegistrantsColumns = array_keys($this->db->getTableColumns('#__eb_registrants'));

    $this->defaults = [
      'address' => "''",
      'address2' => "''",
      'agree_privacy_policy' => '1',
      'amount' => '0.00',
      'auto_coupon_coupon_id' => '0',
      'cart_id' => '0',
      'certificate_sent' => '0',
      'check_coupon' => '0',
      'checked_in_at' => $this->db->q($this->db->getNullDate()),
      'checked_in_count' => '0',
      'checked_in' => '0',
      'checked_out_at' =>  $this->db->q($this->db->getNullDate()),
      'city' => "''",
      'comment' => "''",
      'country' => "''",
      'coupon_discount_amount' => '0.000000',
      'coupon_id' => '0',
      'coupon_usage_calculated' => '0',
      'coupon_usage_restored' => '0',
      'coupon_usage_times' => '1',
      'created_by' => 0,
      'deposit_amount' => '0.00',
      'deposit_payment_method' => "''",
      'deposit_payment_processing_fee' => '0.000000',
      'deposit_payment_transaction_id' => "''",
      'discount_amount' => '0.00',
      'email' => "''",
      'event_id' => $this->eventId,
      'fax' => "''",
      'first_name' => "''",
      'first_sms_reminder_sent' => '0',
      'formatted_invoice_number' => "''",
      'group_id' => '0',
      'id' => '0',
      'invoice_number' => "''",
      'invoice_year' => '0',
      'is_deposit_payment_reminder_sent' => '0',
      'is_group_billing' => '0',
      'is_offline_payment_reminder_sent' => '0',
      'is_reminder_sent' => '0',
      'is_second_reminder_sent' => '0',
      'is_third_reminder_sent' => '0',
      'language' => $this->db->q('*'),
      'last_name' => "''",
      'late_fee' => '0.000000',
      'notified' => '0',
      'number_registrants' => '1',
      'organization' => $this->db->q('NULL'),
      'params' => '\'{"fields_fee_amount":[]}\'',
      'payment_amount' => '0.000000',
      'payment_currency' => "''",
      'payment_date' => $this->db->q(Helpers::mtime()),
      'payment_method' => $this->db->q('os_offline'),
      'payment_processing_fee' => '0.000000',
      'payment_status' => '1',
      'phone' => "''",
      'process_deposit_payment' => '0',
      'published' => '1',
      'refunded' => '0',
      'register_date' => $this->db->q(Helpers::mtime()),
      'registration_cancel_date' => $this->db->q($this->db->getNullDate()),
      'registration_code' => $this->getUniqueCodeForRegistrationRecord(),
      'second_sms_reminder_sent' => '0',
      'state' => 'Washington',
      'subscribe_newsletter' => '0',
      'tax_amount' => '0.00',
      'tax_rate' => '0.00',
      'ticket_code' => "''",
      'ticket_number' => '0',
      'ticket_qrcode' => $this->getUniqueCodeForRegistrationRecord('ticket_qrcode', 16),
      'total_amount' => '0.00',
      'transaction_id' => $this->db->q(strtoupper(UserHelper::genRandomPassword())),
      'ts_modified' => $this->db->q(Helpers::mtime()),
      'user_id' => $this->uid,
      'user_ip' => "''",
      'zip' => "''",
    ];


    // Check defaults and database columns match keys
    $defaultKeys = array_keys($this->defaults);
    sort($defaultKeys);
    sort($this->ebRegistrantsColumns);

    if ($defaultKeys != $this->ebRegistrantsColumns) {
?>
      <table>
        <tr>
          <td style="vertical-align:top;">
            <pre><?php print_r($defaultKeys) ?></pre>
          </td>
          <td style="vertical-align:top;">
            <pre><?php print_r($this->ebRegistrantsColumns) ?></pre>
          </td>
        </tr>
      </table>
<?php
      die('Database schema out of sync with default event column values.');
    }
  }

  private function getUniqueCodeForRegistrationRecord(string $fieldName = 'registration_code', int $length = 32): string
  {
    while (true) {
      $uniqueCode = UserHelper::genRandomPassword($length);

      $query = $this->db->getQuery(true);

      $query->clear()
        ->select('COUNT(*)')
        ->from('#__eb_registrants')
        ->where($this->db->quoteName($fieldName) . ' = ' . $this->db->quote($uniqueCode));
      $this->db->setQuery($query);
      $total = $this->db->loadResult();

      if (!$total) {
        break;
      }
    }

    return $this->db->q($uniqueCode);
  }
}
