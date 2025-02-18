<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

use Joomla\CMS\Factory;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EbRecordIndexType;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\EventConfig;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\UserFactoryInterface;

class Registrant
{
  private array $_records = [];
  private $indexType = '';

  var $badgeId = '';

  public EventConfig $eventConfig;
  public int $count = 0;

  public function __construct(
    private string $clawEventAlias,
    private int $uid,
    private array $eventIdFilter = [],
    private bool $enablePastEvents = false
  ) {
    if (0 == $uid) {
      throw (new \UnexpectedValueException('User ID cannot be zero when retrieving registrant record'));
    }

    $this->eventConfig = new EventConfig($clawEventAlias, []);
    $this->badgeId = $this->eventConfig->eventInfo->prefix . '-' . str_pad($uid, 5, '0', STR_PAD_LEFT);
  }

  /**
   * Returns the registration record (or null) of the main event. Extends
   * registrant properties for additional details specific to the main event
   * @return RegistrantRecord|null Registrant record
   */
  public function getMainEvent(): ?RegistrantRecord
  {
    if ($this->eventConfig->eventInfo->eventType != EventTypes::main) {
      die(__FILE__ . ': cannot request Main Event for "any"');
    }
    if (!count($this->_records)) $this->loadCurrentEvents();

    $mainEventIds = $this->eventConfig->getMainEventIds();

    /** @var \ClawCorpLib\Lib\RegistrantRecord $r */
    foreach ($this->_records as $r) {
      if (EbPublishedState::published->value == $r->registrant->published) {
        if (in_array($r->event->eventId, $mainEventIds)) {
          $r->registrant->badgeId = $this->badgeId;
          /** @var \ClawCorpLib\Lib\PackageInfo */
          $e = $this->eventConfig->getPackageInfoByProperty('eventId', $r->event->eventId);
          if (!is_null($e)) {
            $r->registrant->eventPackageType = $e->eventPackageType;
            return $r;
          }
        }
      }
    }

    return null;
  }

  /**
   * Finds all the active main events (across all event aliases) for a registrant
   * @see components/com_eventbooking/themes/default/history/default.php
   * @param int $uid 
   * @return array eventId => RegistrantRecord
   */
  public static function getCurrentRegistrantEvents(int $uid): array
  {
    // Array of registrant records
    $result = [];

    // Need to check if we're past end date
    $eventInfos = new EventInfos();

    $now = Factory::getDate();

    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ($eventInfos as $eventInfo) {
      if ($now > $eventInfo->end_date) continue;
      if (EventTypes::refunds == $eventInfo->eventType) continue;

      $r = new Registrant($eventInfo->alias, $uid);
      /** @var \ClawCorpLib\Lib\RegistrantRecord */
      $main = $r->getMainEvent();
      if (!is_null($main)) $result[$main->event->eventId] = $main;
    }

    return $result;
  }

  /**
   * Returns all the loaded records
   * @param bool $single (default=false) If a singular record is expected, set to
   *  true. If multiple records are loaded, badness will occur so code can be fixed
   * @return array Records array indexed by recordIndexType (unless $single is true)
   */
  public function records(bool $single = false): array
  {
    if ($single && (sizeof($this->_records) > 1 || sizeof($this->_records) == 0)) die(__FILE__ . ': Single records expected, none/multiple available');
    if (!$single) return $this->_records;

    $result = [];
    reset($this->_records);
    $result[] = current($this->_records);
    return $result;
  }

  /**
   * Returns the raw database row for a registrant
   * @param string $value Search value
   * @param string $key Lookup key (default #__eb_registrants.id)
   * @return object Database row as object or null on error; currently supports only
   *  '=' operator
   */
  public static function loadRegistrantRow(string $value, string $key = 'id'): ?object
  {
    $db = Factory::getContainer()->get('DatabaseDriver');

    $q = $db->getQuery(true);

    $q->select('*')
      ->from('#__eb_registrants')
      ->where($db->quotename($key) . '=' . $db->quote($value));
    $db->setQuery($q);
    return $db->loadObject();
  }

  public function uid()
  {
    return $this->uid;
  }

  public function loadCurrentEvents(EbRecordIndexType $index = EbRecordIndexType::default): void
  {
    if ($this->count > 0) return;

    $this->indexType = $index;

    $db = Factory::getContainer()->get('DatabaseDriver');

    $startDate = $this->eventConfig->eventInfo->start_date->toSql();
    $endDate = $this->eventConfig->eventInfo->end_date->toSql();

    $columns = [
      'e.id as eventId',
      'e.alias',
      'e.title',
      'e.event_date',
      'e.event_end_date',
      'c.category_id',
      'r.id',
      'r.published',
      'r.user_id',
      'r.first_name',
      'r.last_name',
      'r.invoice_number',
      'r.email',
      'r.address',
      'r.address2',
      'r.city',
      'r.state',
      'r.zip',
      'r.country',
      'r.ts_modified',
      'r.register_date',
      'r.payment_status',
      'r.deposit_amount',
      'r.payment_amount',
      'r.total_amount',
      'r.discount_amount',
      'r.deposit_payment_transaction_id',
      'r.deposit_payment_method',
      'r.amount',
      'r.payment_method',
      'r.transaction_id',
      'r.ts_modified',
      'r.registration_code'
    ];

    $q = $db->getQuery(true);

    $q->select($columns)
      ->from($db->qn('#__eb_registrants', 'r'))
      ->join('LEFT OUTER', $db->qn('#__eb_events', 'e') . ' ON ' . $db->qn('e.id') . ' = ' . $db->qn('r.event_id'))
      ->join('LEFT OUTER', $db->qn('#__eb_event_categories', 'c') . ' ON ' . $db->qn('c.event_id') . ' = ' . $db->qn('r.event_id'))
      ->where($db->qn('r.user_id') . ' = ' . $db->quote($this->uid))
      ->where($db->qn('r.invoice_number') . '!=' . $db->q('0'));

    if ($this->eventConfig->eventInfo->eventType == EventTypes::main && !$this->enablePastEvents) {
      $q->where($db->qn('e.event_end_date') . '<=' . $db->q($endDate))
        ->where($db->qn('e.event_date') . '>=' . $db->q($startDate))
        ->where($db->qn('r.published') . '=' . EbPublishedState::published->value);
    }

    if (count($this->eventIdFilter)) {
      $in = implode(',', $db->quote($this->eventIdFilter));
      $q->where($db->qn('r.event_id') . ' IN (' . $in . ')');
    } else {
      $q->where($db->qn('e.id') . ' IS NOT NULL ');
    }

    if (EventTypes::refunds == $this->eventConfig->eventInfo->eventType) {
      $q->where('(' . $db->qn('r.published') . '=' . EbPublishedState::published->value . ' OR ' . $db->qn('r.published') . '=' . EbPublishedState::cancelled->value . ')');
      $q->order($db->qn(['r.transaction_id']));
    }

    $db->setQuery($q);
    $records = $db->loadObjectList($index->value);

    // Refunds can only retrieve main events
    $mainOnly = ('refunds' == $this->clawEventAlias);

    foreach ($records as $index => $record) {
      /** @var \ClawCorpLib\Lib\PackageInfo */
      $event = $this->eventConfig->getPackageInfoByProperty('eventId', $record->eventId);

      if (null != $event) {
        if ($event->packageInfoType == PackageInfoTypes::main || $event->packageInfoType == PackageInfoTypes::daypass) $record->couponKey = $event->couponKey;
        $record->eventPackageType = $event->eventPackageType;
      }

      $this->_records[$index] = new RegistrantRecord($this->clawEventAlias, $record);
    }

    $this->count = count($this->_records);
  }

  /**
   * Appends $this->_records to include custom field values object
   * by fieldValue->fieldName; records must be loaded by record id
   * 
   * @param array $field_ids String array of custom field aliases
   */
  function mergeFieldValues(array $fieldNames): void
  {
    if (count($this->_records) < 1) {
      if (!$this->enablePastEvents) {
        throw (new \Exception('Cannot merge until records loaded.'));
      }
    }

    if (!count($fieldNames)) return;

    if ($this->indexType != EbRecordIndexType::default) die('Cannot merge on non-id index.');

    /** @var \Joomla\Database\DatabaseDriver  */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $registrantIds = implode(',', array_keys($this->_records));

    $query = $db->getQuery(true);
    $query->select(['v.*', 'f.name'])
      ->from('#__eb_field_values v')
      ->join('LEFT OUTER', '#__eb_fields f', 'f.id = v.field_id')
      ->where($db->qn('registrant_id') . ' IN (' . $registrantIds . ')');

    if (count($fieldNames) > 0) {
      $fields = implode(',', (array)$db->q($fieldNames));
      $query->where($db->qn('f.name') . " IN ($fields)");
    }

    /** @var \ClawCorpLib\Lib\RegistrantRecord */
    foreach ($this->_records as $r) {
      foreach ($fieldNames as $f) $r->fieldValue->{$f} = '';
    }

    $db->setQuery($query);
    $rows = $db->loadObjectList();

    foreach ($rows as $r) {
      $e = $r->registrant_id;
      $name = $r->name;

      $this->_records[$e]->fieldValue->$name = $r->field_value;
    }
  }

  /**
   * Create or update the values of custom fields
   * @param int $registrant_id The registration row id
   * @param array $values Custom Field Name to value mapping
   * @param bool $merge Merge with existing value if true, otherwise overwrite
   */
  function updateFieldValues(int $registrant_id, array $values, bool $merge = false): void
  {
    if ($this->indexType != EbRecordIndexType::default) die('Cannot update on non-id index.');

    $db = Factory::getContainer()->get('DatabaseDriver');

    if (!array_key_exists($registrant_id, $this->_records)) {
      die(__FILE__ . ': invalid registrant_id (' . $registrant_id . ') in ' . __FUNCTION__);
    }

    foreach ($values as $k => $v) {
      $fieldId = ClawEvents::getFieldId($k);

      $q = $db->getQuery(true);
      $q->select(['id', 'field_value'])
        ->from('#__eb_field_values')
        ->where($db->qn('field_id') . '=' . $db->q($fieldId))
        ->where($db->qn('registrant_id') . '=' . $db->q($registrant_id));


      $db->setQuery($q);
      $row = $db->loadObject();

      $newValue = $v;

      $id = 0;

      if (!is_null($row)) {
        $id = $row->id;

        if (true == $merge) {
          $newValue = $row->field_value . ',' . $v;
        }
      }

      $fieldId = $db->quote($fieldId);
      $value = $db->quote($newValue);

      $query = "REPLACE INTO #__eb_field_values (`id`,`field_id`,`registrant_id`,`field_value`) VALUES ($id,$fieldId, $registrant_id, $value)";
      $db->setQuery($query);
      $db->execute();
    }
  }

  public static function invoiceToUid(string $invoice): int
  {
    $exp = "/([cl]\d\d-)(\d{4,})-?\d*$/i";

    if (preg_match($exp, $invoice, $matches) > 0) {
      return $matches[2];
    }

    if (is_numeric($invoice) && strlen($invoice) == 5) {
      return $invoice;
    }

    return 0;
  }

  /**
   * Returns the user id (or 0 if not found) for an invoice # (alternately, the
   * EB-generated registration_code)
   * @param string $regid Invoice # or Registration Code or UID (main part of invoice #)
   * @param bool $any Default false, if true, does not need to be published
   * @return int User ID (or 0 on not found)
   */
  public static function getUserIdFromInvoice(string $regid, bool $any = false): int
  {
    $db = Factory::getContainer()->get('DatabaseDriver');

    $uidCandidate = registrant::invoiceToUid($regid);

    $invoiceWhereOr = '';
    if (is_numeric($uidCandidate) && $uidCandidate > 0) {
      $uidCandidate = trim($uidCandidate);
      $l = $db->q('%-' . str_pad($uidCandidate, 5, '0', STR_PAD_LEFT) . '-%');
      $invoiceWhereOr .= 'invoice_number LIKE ' . $l . ' OR ';
    }
    $invoiceWhereOr .= 'BINARY `registration_code` = ' . $db->q($regid);

    $q = $db->getQuery(true);
    $q->select('user_id')
      ->from('#__eb_registrants')
      ->where('(' . $invoiceWhereOr . ')');

    if (!$any) {
      $q->where('published = ' . EbPublishedState::published->value);
    }

    $db->setQuery($q);
    $uid = $db->loadResult() ?? 0;

    if ($uid) {
      $userFactory = Factory::getContainer()->get(UserFactoryInterface::class);
      $user = $userFactory->loadUserById($uid);
      if (is_null($user) || $user->id == 0 || $user->block != 0) {
        $uid = 0;
      }
    }

    return $uid;
  }

  /**
   * Returns the user id (or 0 if not found) for a registration row id
   * @param int $regid id from eb_registrants
   * @return int User ID (or 0 on not found)
   */
  public static function getUserIdFromRegId(int $regid): int
  {
    $db = Factory::getContainer()->get('DatabaseDriver');

    $r = $db->q($regid);

    $q = "SELECT user_id FROM #__eb_registrants WHERE id = $r";
    $db->setQuery($q);
    $uid = $db->loadResult() ?? 0;

    return $uid;
  }

  /**
   * Adds a record manually, should additional processing be necessary. Minimally
   * this is used, for example, to determine if there are event overlaps in cart.php
   * @param int $key Record key and should match recordIndexType
   * @param object $record The new record; does not need be complete
   */
  public function addRecord(int $key, object $record): void
  {
    $this->_records[$key] = new RegistrantRecord($this->clawEventAlias, $record);
  }

  public function checkOverlaps(array $categoryIds): array
  {
    $result = [];

    foreach ($this->_records as $l) {
      /** @var \ClawCorpLib\Lib\RegistrantRecord $l */
      if (!in_array($l->category->category_id, $categoryIds)) continue;

      foreach ($this->_records as $r) {
        /** @var \ClawCorpLib\Lib\RegistrantRecord $r */
        if (!in_array($r->category->category_id, $categoryIds)) continue;

        if ($l->event->eventId == $r->event->eventId) {
          continue;
        }

        // Make left alway start first
        if ($l->event->event_date > $r->event->event_date) {
          continue;
        }

        // no overlap
        if ($l->event->event_end_date <= $r->event->event_date) {
          continue;
        }

        // right starts before left end
        if ($r->event->event_date < $l->event->event_end_date) {
          $result[] = $l;
          $result[] = $r;
          break;
        }
      }
    }

    return $result;
  }

  /**
   * Gets a count of categoryIds that appear in the registrant records
   * @param array categoryIds
   * @return int Count of appearing categories
   */
  public function categoryCounts(array $categoryIds): int
  {
    $recordsCategoryIds = [];

    foreach ($this->_records as $r) {
      $recordsCategoryIds[] = $r->category->category_id;
    }

    $intersect = array_unique(array_intersect($categoryIds, $recordsCategoryIds));

    return count($intersect);
  }

  public static function getInvoiceNumber($row = null)
  {
    if (null == $row) die("Whoa! Something bad happened. Sorry. Please let us know how you got here: https://www.clawinfo.org/help");

    $uid = $row->user_id;
    $alias = ClawEvents::eventIdtoAlias($row->event_id);

    if (false === $alias) {
      // Load from global config, defaults to clean Joomla group install
      $componentParams = ComponentHelper::getParams('com_claw');
      $nonEventCategories = $componentParams->get('eb_cat_nonpackageinfo', []);

      /** @var \Joomla\Database\DatabaseDriver */
      $db     = Factory::getContainer()->get('DatabaseDriver');

      $query  = $db->getQuery(true);
      $query->select('main_category_id')
        ->from('#__eb_events')
        ->where('id = ' . $db->q($row->event_id));
      $db->setQuery($query);
      $mainCategory = $db->loadResult();

      if (!is_null($mainCategory) && in_array($mainCategory, $nonEventCategories)) {
        $alias = Aliases::current(true);
      }
    }

    $info = new EventInfo($alias);
    return Registrant::generateNextInvoiceNumber($info->prefix . '-', $uid);
  }

  public static function generateNextInvoiceNumber(string $prefix, int $uid): string
  {
    $uid = str_pad($uid, 5, '0', STR_PAD_LEFT);

    if (0 == $uid) {
      $referrer = Helpers::sessionGet('referrer');
      if ($referrer) {
        $uid = substr($referrer, 0, 5);
      }
    }

    /** @var \Joomla\Database\DatabaseDriver */
    $db     = Factory::getContainer()->get('DatabaseDriver');
    $query  = $db->getQuery(true);
    $query->select('invoice_number')
      ->from('#__eb_registrants')
      ->where('invoice_number LIKE "' . $prefix . $uid . '%"');

    $db->setQuery($query);
    $invoice_numbers = $db->loadColumn();
    $nextInvoice = 0;

    foreach ($invoice_numbers as $invoice) {
      $parts = explode('-', $invoice);

      if (count($parts) == 3) {
        $x = (int)$parts[2];
        if ($x > $nextInvoice) $nextInvoice = $x;
      }
    }

    $nextInvoice++;

    return $prefix . $uid . '-' . $nextInvoice;
  }
}
