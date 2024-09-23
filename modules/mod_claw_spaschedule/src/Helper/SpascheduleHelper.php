<?php

/**
 * @package     CLAW.Schedule
 * @subpackage  mod_claw_spaschedule
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Module\Spaschedule\Site\Helper;

use ClawCorpLib\Lib\Aliases;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Helpers\EventBooking;

\defined('_JEXEC') or die;

/**
 * Helper for mod_claw_spaschedule
 */
class SpascheduleHelper implements DatabaseAwareInterface
{
  use DatabaseAwareTrait;

  public function loadSchedule(): EventConfig
  {
    $alias = Aliases::current(true);

    $eventConfig = new EventConfig($alias, [PackageInfoTypes::spa]);

    // Find full events and remove from $packageInfos
    $eventIds = [];

    /*
 * {"meta0":{"userid":"287","services":["therapeutic","sensual"],"eventId":188},"meta1":{"userid":"9548","services":["sensual","tie"],"eventId":189}}
 */

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($eventConfig->packageInfos as $packageInfo) {
      foreach ($packageInfo->meta as $meta) {
        $eventIds[] = $meta->eventId;
      }
    }

    $capacityInfo = EventBooking::getEventsCapacityInfo($eventConfig->eventInfo, $eventIds);

    // Remove item from meta if missing or at capacity

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($eventConfig->packageInfos as $pKey => $packageInfo) {
      foreach ($packageInfo->meta as $key => $meta) {
        if (
          !array_key_exists($meta->eventId, $capacityInfo) ||
          $capacityInfo[$meta->eventId]->total_registrants >= $capacityInfo[$meta->eventId]->event_capacity
        ) {
          unset($packageInfo->meta->$key);
        }
      }

      if (count((array)$packageInfo->meta) == 0) {
        unset($eventConfig->packageInfos[$pKey]);
      }
    }

    return $eventConfig;
  }
}
