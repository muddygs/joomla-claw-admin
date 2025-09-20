<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

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
    try {
      $srcEventConfig = new EventConfig($from);
    } catch (\Exception) {
      return 'Invalid from event: ' . $from;
    }

    try {
      $dstEventConfig = new EventConfig($to);
    } catch (\Exception) {
      return 'Invalid from event: ' . $to;
    }

    if ($srcEventConfig->eventInfo->alias === $dstEventConfig->eventInfo->alias) {
      return 'Cannot copy to the same event';
    }

    if (count($tableNames) == 0) {
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

    foreach ($tables as $table) {
      $results[$table] = '';

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
        $results[] = "<b>Database for $to deleted.</b>";
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
            if (!is_null($msg)) $results[$table] .= $msg;
            continue 2;

          case PackageInfo::TABLE_NAME:
            $msg = $this->copyPackageInfo($srcEventConfig, $dstEventConfig, (int)$row->id);
            if (!is_null($msg)) $results[$table] .= $msg;
            continue 2;

          case GridShift::SHIFTS_TABLE:
            $msg = $this->copyGridShift($dstEventConfig, (int)$row->id);
            if (!is_null($msg)) $results[$table] .= $msg;
            continue 2;
        }

        // TODO: Other tables should have library handling
        $row->$eventColumn = $to;
        $row->id = null;

        $row->mtime = Helpers::mtime();

        $db->insertObject($table, $row, 'id');
      }

      $results[$table] .= "$table: " . count($rows) . ' rows processed';
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
    $packageInfo->eventAlias = $dst->eventInfo->alias;

    // Some packages are all weekend and don't have specific start/end
    if (!is_null($packageInfo->start)) {
      try {
        $packageInfo->start = $this->translateDate(
          $src->eventInfo->start_date,
          $packageInfo->start,
          $dst->eventInfo->start_date
        );
      } catch (\Exception $e) {
        return $e->getMessage() . ": PackageInfo@$srcId";
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
    $packageInfo->alias = 'Assigned when deployed';
    $packageInfo->meta = [];
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

  private function translateDate(Date $srcBase, Date $srcOffset, Date $dstBase): Date|bool
  {
    $srcBase = Factory::getDate($srcBase);
    $srcOffset = Factory::getDate($srcOffset);
    $dstBase = Factory::getDate($dstBase);

    if ($srcOffset < $srcBase) {
      throw new \Exception("Offset time cannot be before source time.");
    }

    $diff = $srcBase->diff($srcOffset);

    if ($diff === false) {
      throw new \Exception("Invalid date diff");
    }

    $newtime = $dstBase->modify($diff->format('%d days %H hours %i minutes'));

    $result = clone $newtime;

    return $result;
  }
}
