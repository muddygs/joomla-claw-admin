<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Exporters;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\FeeEvents;
use Joomla\CMS\Factory;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Lib\Schedule;
use ClawCorpLib\Helpers\Locations;
use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Uri\Uri;

class ScheduleExport
{
  public static function toCSV(EventInfo $eventInfo, string $filename)
  {
    $root = Uri::getInstance();
    $root->setPath('/');
    $photoRoot = $root->root();

    $db = Factory::getContainer()->get('DatabaseDriver');
    $locations = Locations::get($eventInfo->alias);
    $schedule = Schedule::get($eventInfo); // returns only published items

    // Load database columns
    $columnNames = array_keys($db->getTableColumns('#__claw_schedule'));
    $columnNames[] = 'track';
    $columnNames[] = 'date';

    $preferred = [
      'id',
      'track',
      'date',
      'datetime_start',
      'datetime_end',
      'event_title',
      'event_description',
      'location',
    ];

    $ordering = Helpers::combineArrays($preferred, $columnNames);

    $remapping = [
      'id' => 'Unique ID',
      'track' => 'Track',
      'date' => 'Day',
      'datetime_start' => 'Start Time',
      'datetime_end' => 'End Time',
      'event_title' => 'Title',
      'event_description' => 'Description',
      'location' => 'Location',
    ];

    $headers = array_map(function ($x) use ($remapping) {
      return array_key_exists($x, $remapping) ? $remapping[$x] : $x;
    }, $ordering);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $headers);

    /** @var \ClawCorpLib\Lib\ScheduleRecord */
    foreach ($schedule as $c) {
      $row = [];
      foreach ($ordering as $col) {
        switch ($col) {
          case 'id':
            $row[] = 'schedule_' . $c->$col;
            break;
          case 'date':
            $row[] = $c->datetime_start->format(SkillsExport::YAPP_DATE_FORMAT);
            break;
          case 'datetime_start':
            $row[] = $c->datetime_start->format(SkillsExport::YAPP_TIME_FORMAT);
            break;
          case 'datetime_end':
            $row[] = $c->datetime_end->format(SkillsExport::YAPP_TIME_FORMAT);
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
          case 'published':
            $row[] = match ($c->published) {
              EbPublishedState::any => 'Unpublished',
              EbPublishedState::published => 'Published',
              default => 'Unknown',
            };
            break;
          case 'fee_event':
            $row[] = implode(',', array_map(function ($f) {
              FeeEvents::tryFrom($f)->value ?? "Unknown";
            }, $c->fee_event));
            break;
          case 'mtime':
            $row[] = $c->mtime->toSql();
            break;
          case 'featured':
            $row[] = $c->featured ? '1' : '0';
            break;
          case 'poster':
            $row[] = $c->poster ? $photoRoot . '/' . $c->poster : '';
            break;

          default:
            try {
              if (property_exists($c, $col)) {
                $row[] = $c->$col;
              }
            } catch (\Exception $e) {
              echo "Generic error processing during export $e";
              die();
            }
            break;
        }
      }

      fputcsv($fp, $row);
    }

    fclose($fp);
  }
}
