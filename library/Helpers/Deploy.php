<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Enums\PackageInfoTypes;
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

    $eventConfig = new EventConfig($eventAlias);
    $info = $eventConfig->eventInfo;
    $events = $eventConfig->packageInfos;

    // Base times to offset by "time" parameter for each event
    $cancel_before_date = $info->cancelBy;
    $startDate = $info->modify('Thursday 9AM');
    $endDate = $info->modify('next Monday midnight');;

    // start and ending usability of these events
    $registration_start_date = Factory::getDate()->toSql();
    $publish_down = $info->modify('+8 days');

    $article_id = $info->termsArticleId;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($events as $event) {
      $title = $event->description;
      $event->alias = strtolower($info->prefix . '-' . $event->eventPackageType->name);

      $start = $startDate;
      $end = $endDate;
      $cutoff = $endDate;

      switch ( $event->packageInfoType ) {
        case PackageInfoTypes::main:
          $event->start = $startDate;
          $event->end = $endDate;
        break;
        
        case PackageInfoTypes::addon:
          $start = Factory::getDate($event->start)->toSql();
          $end = Factory::getDate($event->end)->toSql();
  
          $origin = new DateTimeImmutable($start);
          $target = new DateTimeImmutable($end);
          $interval = $origin->diff($target);
  
          // If the event is less than 8 hours, then the cutoff is 3 hours before the event
          if ($interval->h <= 8) {
            $cutoff = Factory::getDate($event->start);
            $cutoff = $cutoff->modify('-3 hours')->toSql();
          }
        break;

        default:
          die('Unhandled event type: ' . $event->packageInfoType->toString());
        break;
      }

      $insert = new ebMgmt($eventAlias, $event->category, $event->alias, $info->prefix . ' ' . $title, $event->description);
      $insert->set('article_id', $article_id, false);
      $insert->set('cancel_before_date', $cancel_before_date->toSql());
      $insert->set('cut_off_date', $cutoff);
      $insert->set('event_date', $start);
      $insert->set('event_end_date', $end);
      $insert->set('publish_down', $publish_down);

      $insert->set('individual_price', $event->fee);
      $insert->set('registration_start_date', $registration_start_date);
      $insert->set('payment_methods', 2); // Credit Cart

      $eventId = $insert->insert();
      if ($eventId == 0) {
        $log[] =  "<p>Skipping existing: $title</p>";
      } else {
        $log[] =  "<p>Added: $title at event id $eventId</p>";
        $event->eventId = $eventId;
        $event->save();
      }
    }
    return implode("\n", $log);
  }

}