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
   * @since  1.6
   */
  protected $text_prefix = 'COM_CLAW_EVENTCOPY';

  /**
   * Method to get the record form.
   *
   * @param   array    $data      Data for the form.
   * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
   *
   * @return  Form|boolean  A Form object on success, false on failure
   *
   * @since   1.6
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
      // Delete existing
      // $query = $db->getQuery(true);
      // $query->delete($db->quoteName($table))
      //   ->where($db->quoteName('event') . ' = ' . $db->quote($to));
      // $db->setQuery($query);
      // $db->execute();
      //
      $eventColumn = $table == '#__claw_packages' ? 'eventAlias' : 'event';

      // Copy from older event
      $query = $db->getQuery(true);
      $query->select('*')
        ->from($db->quoteName($table))
        ->where($db->quoteName($eventColumn) . ' = ' . $db->quote($from));
      $db->setQuery($query);
      $rows = $db->loadObjectList();

      foreach ($rows as $row) {
        $row->$eventColumn = $to;
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
            $row->alias = 'Assigned during publication';
            $row->meta = '[]';
            break;

          case '#__claw_shifts':
            $row->grid = $this->resetGrid($row->grid);
            break;
        }

        $row->mtime = Helpers::mtime();

        $db->insertObject($table, $row);
        #$results[$table] .= print_r($row, true);
      }

      $results[$table] .= "$table: " . count($rows) . ' rows copied';
    }

    return implode("<br/>", $results);
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

  private function resetGrid(string $grid): string
  {
    $grid = json_decode($grid);

    foreach ($grid as $k => $v) {
      foreach (array_keys(get_object_vars($v)) as $kk) {
        if (str_contains($kk, 'eventid') !== false) {
          $grid->$k->$kk = 0;
        }
      }
    }

    return json_encode($grid);
  }
}
