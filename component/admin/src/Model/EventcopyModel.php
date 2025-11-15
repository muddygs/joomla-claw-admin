<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Grid\GridShift;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Locations;
use ClawCorpLib\Helpers\Vendors;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\PackageInfo;
use ClawCorpLib\Lib\ScheduleRecord;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Date\Date;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class EventcopyModel extends FormModel
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   */
  protected $text_prefix = 'COM_CLAW';

  /**
   * Method to get the record form.
   *
   * @param   array    $data      Data for the form.
   * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
   *
   * @return  Form|boolean  A Form object on success, false on failure
   */
  public function getForm($data = [], $loadData = false)
  {
    // Get the form.
    $form = $this->loadForm('com_claw.eventcopy', 'eventcopy', ['control' => 'jform', 'load_data' => $loadData]);

    if (empty($form)) {
      return false;
    }

    return $form;
  }

  public function doCopyEvent(string $from, string $to, array $tableNames, bool $delete = false): string
  {
    $user = Factory::getApplication()->getIdentity();
    if (!$user || !$user->authorise('core.create', 'com_claw')) {
      return '<span style="color:red">Not authorised to copy.</span>';
    }

    try {
      $srcEventConfig = new EventConfig(alias: $from, publishedOnly: false);
    } catch (\Throwable) {
      return 'Invalid from event: ' . $from;
    }

    try {
      $dstEventConfig = new EventConfig(alias: $to, publishedOnly: true);
    } catch (\Throwable) {
      return 'Invalid to event: ' . $to;
    }

    if ($srcEventConfig->alias === $dstEventConfig->alias) {
      return 'Cannot copy to the same event';
    }

    if (count($tableNames) === 0) {
      return 'No tables selected to copy';
    }

    // Do some database magic!

    $db = $this->getDatabase();

    $tables = [];
    foreach ($tableNames as $name) {
      $tables[] = match ($name) {
        "Schedule" => ScheduleRecord::TABLE_NAME,
        "Vendors" => Vendors::TABLE_NAME,
        "Shifts" => GridShift::SHIFTS_TABLE,
        "Locations" => Locations::TABLE_NAME,
        "FieldValues" => '#__claw_field_values',
        "Packages" => PackageInfo::TABLE_NAME,
      };
    }

    $results = [];

    $db->transactionStart();

    try {
      foreach ($tables as $table) {
        // TODO: this inconsistency is boring. Update the table columns and fix this.
        $eventColumn = match ($table) {
          PackageInfo::TABLE_NAME => 'eventAlias',
          ScheduleRecord::TABLE_NAME => 'event_alias',
          default =>  'event'
        };

        // Delete existing
        if ($delete) {
          $query = $db->getQuery(true);
          $query->delete($db->quoteName($table))
            ->where($db->quoteName($eventColumn) . ' = ' . $db->quote($to));
          $db->setQuery($query);
          $db->execute();
          $results[] = "<b>Entries in $table database for $to deleted.</b>";
        }

        // Copy from older event
        $query = $db->getQuery(true);
        $query->select('*')
          ->from($db->quoteName($table))
          ->where($db->quoteName($eventColumn) . ' = ' . $db->quote($from));
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        foreach ($rows as $row) {
          switch ($table) {
            case ScheduleRecord::TABLE_NAME:
              $msg = $this->copyScheduleRecord($srcEventConfig, $dstEventConfig, (int)$row->id);
              if (!is_null($msg)) $results[] = $msg;
              continue 2;

            case PackageInfo::TABLE_NAME:
              $msg = $this->copyPackageInfo($srcEventConfig, $dstEventConfig, (int)$row->id);
              if (!is_null($msg)) $results[] = $msg;
              continue 2;

            case GridShift::SHIFTS_TABLE:
              $msg = $this->copyGridShift($dstEventConfig, (int)$row->id);
              if (!is_null($msg)) $results[] = $msg;
              continue 2;
          }

          // TODO: Other tables should have library handling
          $row->$eventColumn = $to;
          $row->id = null;

          $row->mtime = Helpers::mtime();

          $db->insertObject($table, $row, 'id');
        }

        $results[] = "$table: " . count($rows) . ' rows processed';
      }

      $db->transactionCommit();
    } catch (\Throwable $e) {
      $db->transactionRollback();
      return 'Copy aborted: ' . $e->getMessage();
    }

    return implode("<br/>", $results);
  }

  private function copyScheduleRecord(EventConfig $src, EventConfig $dst, int $srcId): ?string
  {
    if (0 == $srcId) {
      return 'Schedule copy requires a source id';
    }

    $scheduleItem = new ScheduleRecord($srcId);
    $scheduleItem->id = 0;
    $scheduleItem->event_alias = $dst->alias;

    try {
      $scheduleItem->datetime_start = $this->translateDate(
        $src->eventInfo->start_date,
        $scheduleItem->datetime_start,
        $dst->eventInfo->start_date
      );
    } catch (\Exception $e) {
      return $e->getMessage() . ": ScheduleRecord@$srcId";
    }

    try {
      $scheduleItem->datetime_end = $this->translateDate(
        $src->eventInfo->start_date,
        $scheduleItem->datetime_end,
        $dst->eventInfo->start_date
      );
    } catch (\Exception $e) {
      return $e->getMessage() . ": ScheduleRecord@$srcId";
    }

    $scheduleItem->poster = '';
    $scheduleItem->event_id = 0;
    $scheduleItem->save();

    return null;
  }

  private function copyPackageInfo(EventConfig $src, EventConfig $dst, int $srcId): ?string
  {
    if (0 == $srcId) {
      return 'PackageInfo copy requires a source id';
    }

    $packageInfo = new PackageInfo($srcId);
    $packageInfo->id = 0;
    $packageInfo->eventAlias = $dst->alias;

    $noDateTranslation = [
      PackageInfoTypes::main,
      PackageInfoTypes::sponsorship
    ];

    // Main packages are all weekend and don't have specific recorded start/end
    // These are set in EventBooking's database upon deployment
    if (!in_array($packageInfo->packageInfoType, $noDateTranslation)) {
      try {
        $packageInfo->start = $this->translateDate(
          $src->eventInfo->start_date,
          $packageInfo->start,
          $dst->eventInfo->start_date
        );
      } catch (\Exception $e) {
        $dump = print_r($packageInfo, true);
        return $e->getMessage() . ": PackageInfo@$srcId<br/>$dump";
      }

      try {
        $packageInfo->end = $this->translateDate(
          $src->eventInfo->start_date,
          $packageInfo->end,
          $dst->eventInfo->start_date
        );
      } catch (\Exception $e) {
        return $e->getMessage() . ": PackageInfo@$srcId";
      }
    }

    $packageInfo->eventId = 0;
    $packageInfo->alias = '';

    if ($packageInfo->packageInfoType == PackageInfoTypes::speeddating) {
      if (is_object($packageInfo->meta)) {
        foreach (array_keys((array)$packageInfo->meta) as $key) {
          $packageInfo->meta->$key->eventId = 0;
        }
      } else {
        $packageInfo->meta = [];
      }
    } else {
      $packageInfo->meta = [];
    }

    $packageInfo->save();

    return null;
  }

  private function copyGridShift(EventConfig $dst, int $srcId): ?string
  {
    if (0 == $srcId) {
      return 'Shift copy requires a source id';
    }

    $gridShift = new GridShift($srcId);
    $gridShift->id = 0; // force new
    $gridShift->event = $dst->alias;

    /** @var \ClawCorpLib\Grid\GridTime */
    foreach ($gridShift->getTimes() as $time) {
      $time->id = 0; // force new
      $keys = $time->getKeys();
      $time->setEventIds(...array_fill(0, count($keys), 0));
    }

    $gridShift->save(); // updates sid on times for us

    return null;
  }

  private function translateDate(Date $srcBase, ?Date $srcOffset, Date $dstBase): Date
  {
    if (is_null($srcOffset)) {
      return clone $srcBase;
    }

    $srcBase   = Factory::getDate($srcBase);
    $srcOffset = Factory::getDate($srcOffset);
    $dstBase   = Factory::getDate($dstBase);

    if ($srcOffset->toUnix() < $srcBase->toUnix()) {
      throw new \Exception('Offset time cannot be before source time.');
    }

    $delta = $srcOffset->toUnix() - $srcBase->toUnix();
    $copy  = clone $dstBase;
    $ok    = $copy->modify("+$delta seconds");

    if ($ok === false) {
      throw new \Exception('Invalid date math');
    }
    return $copy;
  }
}
