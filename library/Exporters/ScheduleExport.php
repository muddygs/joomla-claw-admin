<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Helpers;

use Joomla\CMS\Factory;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Lib\Schedule;

class ScheduleExport
{
  public static function toCSV(EventInfo $eventInfo, string $filename)
  {
    $db = Factory::getContainer()->get('DatabaseDriver');
    $locations = Locations::get($eventInfo->alias);
    $schedule = Schedule::get($eventInfo);

    // Load database columns
    $columnNames = array_keys($db->getTableColumns('#__claw_schedule'));
    $columnNames[] = 'track';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $columnNames);

    /** @var \ClawCorpLib\Lib\ScheduleRecord */
    foreach ($schedule as $c) {
      $row = [];
      foreach ($columnNames as $col) {
        switch ($col) {
          case 'id':
            $row[] = 'schedule_' . $c->$col;
            break;
          case 'datetime_start':
          case 'datetime_end':
            $time = Helpers::formatDateTime($c->$col);
            if ($time == 'Midnight') $time = '12:00 AM';
            if ($time == 'Noon') $time = '12:00 PM';
            $row[] = $time;
            break;
          case 'sponsors':
            $s = array_map(function ($v) {
              return 'sponsor_' . $v;
            }, $c->sponsors);
            $row[] = implode(',', $s);
            break;
          case 'location':
            $row[] = $locations[$c->$col]->value ?? '';
            break;
          case 'track':
            // track is day converted to day of week
            $row[] = $c->datetime_start->format('l');
            break;
          case 'event_description':
            $row[] = Helpers::cleanHtmlForCsv($c->$col);
            break;
          default:
            $row[] = $c->$col;
            break;
        }
      }

      fputcsv($fp, $row);
    }

    fclose($fp);
  }
}
