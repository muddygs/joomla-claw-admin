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

use ClawCorpLib\Grid\GridTime;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\EventConfig;
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

  public function doCopyEvent(string $from, string $to, array $tableNames): string
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
        "Schedule" => '#__claw_schedule',
        "Vendors" => '#__claw_vendors',
        "Shifts" => '#__claw_shifts',
        "Locations" => '#__claw_locations',
        "FieldValues" => '#__claw_field_values',
        "Packages" => '#__claw_packages',
      };
    }

    $results = [];

    foreach ($tables as $table) {
      $results[$table] = '';
      $eventColumn = $table == '#__claw_packages' ? 'eventAlias' : 'event';

      // Delete existing
      // $query = $db->getQuery(true);
      // $query->delete($db->quoteName($table))
      //   ->where($db->quoteName($eventColumn) . ' = ' . $db->quote($to));
      // $db->setQuery($query);
      // $db->execute();
      //

      // Copy from older event
      $query = $db->getQuery(true);
      $query->select('*')
        ->from($db->quoteName($table))
        ->where($db->quoteName($eventColumn) . ' = ' . $db->quote($from));
      $db->setQuery($query);
      $rows = $db->loadObjectList();

      foreach ($rows as $row) {
        $row->$eventColumn = $to;
        $oldId = $row->id;
        $row->id = null;

        switch ($table) {
          case '#__claw_schedule':
            $targetDay = new Date($row->day);
            $dstDate = $this->translateDate($srcEventConfig->eventInfo->start_date, $targetDay, $dstEventConfig->eventInfo->start_date);

            // TODO: Handle false here!
            $row->day = $dstDate->toSql();
            $row->poster = '';
            $row->poster_size = '';
            $row->event_id = 0;
            break;

          case '#__claw_packages':
            if ($row->start != $db->getNullDate()) {
              $targetDay = new Date($row->start);
              $startDate = $this->translateDate($srcEventConfig->eventInfo->start_date, $targetDay, $dstEventConfig->eventInfo->start_date);
              $row->start = $startDate->toSql();
              $targetDay = new Date($row->end);
              $endDate = $this->translateDate($srcEventConfig->eventInfo->start_date, $targetDay, $dstEventConfig->eventInfo->start_date);
              $row->end = $endDate->toSql();
            }

            // TODO: Handle false here!
            $row->eventId = 0;
            $row->alias = 'Assigned when deployed';
            $row->meta = '[]';
            break;
        }

        $row->mtime = Helpers::mtime();

        $db->insertObject($table, $row, 'id');

        if ('#__claw_shifts' == $table) {
          $this->migrateShiftTimes($oldId, $row->id);
        }
      }

      $results[$table] .= "$table: " . count($rows) . ' rows copied';
    }

    return implode("<br/>", $results);
  }

  private function migrateShiftTimes(int $oldSid, int $sid)
  {
    $db = $this->getDatabase();

    // Copy from older event
    $query = $db->getQuery(true);
    $query->select('id')
      ->from($db->quoteName('#__claw_shift_times'))
      ->where($db->quoteName('sid') . ' = ' . $db->quote($oldSid));
    $db->setQuery($query);
    $ids = $db->loadColumn();

    foreach ($ids as $id) {
      $gridTime = new GridTime($id, $oldSid);
      $gridTime->id = 0;
      $gridTime->sid = $sid;
      $keys = $gridTime->getKeys();
      $gridTime->setEventIds(...array_fill(0, count($keys), 0));
      $gridTime->save();
    }
  }

  private function translateDate(Date $srcBase, Date $srcOffset, Date $dstBase): Date|bool
  {
    $srcBase = Factory::getDate($srcBase);
    $srcOffset = Factory::getDate($srcOffset);
    $dstBase = Factory::getDate($dstBase);

    $diff = $srcBase->diff($srcOffset);

    if ($diff === false) {
      throw new \Exception("Invalid date diff");
    }

    $newtime = $dstBase->modify($diff->format('%d days %H hours %M minutes'));

    $result = clone $newtime;

    return $result;
  }
}
