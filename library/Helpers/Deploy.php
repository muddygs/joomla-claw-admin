<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Ebmgmt;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\EventInfo;
use DateTimeImmutable;
use Joomla\CMS\Factory;

class Deploy
{
  public static function Packages(string $eventAlias): string
  {
    $log = [];

    // Validate events are valid
    if (!EventInfo::isValidEventAlias($eventAlias)) {
      return 'Invalid to event: ' . $eventAlias;
    }

    // Ignore server-specific timezone information
    date_default_timezone_set('etc/UTC');

    $eventConfig = new EventConfig($eventAlias, []);
    $info = $eventConfig->eventInfo;
    $packageInfos = $eventConfig->packageInfos;

    // Base times to offset by "time" parameter for each event
    $cancel_before_date = $info->cancelBy;
    $startDate = $info->modify('Wed 9AM');
    $endDate = $info->modify('next Monday midnight');;

    // start and ending usability of these events
    $registration_start_date = Factory::getDate()->toSql();
    $publish_down = $info->modify('+8 days');

    $article_id = $info->termsArticleId;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($packageInfos as $packageInfo) {
      if ( $packageInfo->eventId > 0 ) {
        $log[] =  "<p>Already deployed: $packageInfo->title @ $packageInfo->eventId</p>";
        continue;
      }

      $name = str_replace('_', '-', $packageInfo->eventPackageType->name);
      $packageInfo->alias = strtolower($info->prefix . '-' . $name);

      $start = $startDate;
      $end = $endDate;
      $cutoff = $endDate;

      switch ( $packageInfo->packageInfoType ) {
        case PackageInfoTypes::main:
          $packageInfo->start = $startDate;
          $packageInfo->end = $endDate;
        break;
        
        case PackageInfoTypes::addon:
          $start = Factory::getDate($packageInfo->start)->toSql();
          $end = Factory::getDate($packageInfo->end)->toSql();
  
          $origin = new DateTimeImmutable($start);
          $target = new DateTimeImmutable($end);
          $interval = $origin->diff($target);
  
          // If the event is less than 8 hours, then the cutoff is 3 hours before the event
          if ($interval->h <= 8) {
            $cutoff = Factory::getDate($packageInfo->start);
            $cutoff = $cutoff->modify('-3 hours')->toSql();
          }
        break;

        case PackageInfoTypes::daypass:
          $start = Factory::getDate($packageInfo->start)->toSql();
          $end = Factory::getDate($packageInfo->end)->toSql();
        break;

        case PackageInfoTypes::coupononly:
          continue 2;
        break;

        // case PackageInfoTypes::speeddating:
        //   $start = Factory::getDate($packageInfo->start);
        //   $end = clone $start;
        //   $end = $end->modify('+45 minutes')->toSql();
        //   $start = $start->toSql();
        //   $packageInfo->alias = strtolower($info->prefix . '-sd-' . $name);
        // break;

        default:
          continue 2;
        break;
      }

      $insert = new ebMgmt($eventAlias, $packageInfo->category, $packageInfo->alias, $info->prefix . ' ' . $packageInfo->title, $packageInfo->title);
      $insert->set('article_id', $article_id, false);
      $insert->set('cancel_before_date', $cancel_before_date->toSql());
      $insert->set('cut_off_date', $cutoff);
      $insert->set('event_date', $start);
      $insert->set('event_end_date', $end);
      $insert->set('publish_down', $publish_down);

      $insert->set('individual_price', $packageInfo->fee);
      $insert->set('registration_start_date', $registration_start_date);
      $insert->set('payment_methods', 2); // Credit Cart

      $eventId = $insert->insert();
      if ($eventId == 0) {
        $log[] =  "<p>Skipping existing: $packageInfo->title</p>";

        // So the alias exists, let's pull the event id from the database
        $eventId = ClawEvents::getEventId($packageInfo->alias, true);
        if ( $eventId != 0) {
          $packageInfo->eventId = $eventId;
          $packageInfo->save();
          $log[] = "<p>Updated: $packageInfo->title at event id $eventId</p>";
        }

      } else {
        $log[] =  "<p>Added: $packageInfo->title at event id $eventId</p>";
        $packageInfo->eventId = $eventId;
        $packageInfo->save();
      }
    }
    return implode("\n", $log);
  }

}