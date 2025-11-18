<?php

/**
 * @package     ClawCorp.Module.Spaschedule
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
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Registrants;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\Factory;

\defined('_JEXEC') or die;

/**
 * Helper for mod_claw_spaschedule
 */
class SpascheduleHelper implements DatabaseAwareInterface
{
  use DatabaseAwareTrait;

  public \Joomla\CMS\User\UserFactoryInterface $userFactory;
  public int $publicNameFieldId = 0;
  public int $userId = 0;

  public function __construct()
  {
    $this->userFactory = Factory::getContainer()->get(UserFactoryInterface::class);

    $params = ComponentHelper::getParams('com_claw');
    $this->publicNameFieldId = $params->get('public_name_field', 0);

    // If someone is signed in, it might be a therapist...collect bookings if it is
    // Only displayed for that particular therapist as a way to get at bookings
    $this->userId = Factory::getApplication()->getIdentity()->id;
  }

  public function loadSchedule(): array
  {

    $alias = Aliases::current(true);

    $eventConfig = new EventConfig($alias, [PackageInfoTypes::spa]);

    // Find full events and remove from $packageInfos
    $eventIds = [];
    $publicNames = [];

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($eventConfig->packageInfos as $packageInfo) {
      foreach ($packageInfo->meta as $meta) {
        $eventIds[] = $meta->eventId;
      }
    }

    $capacityInfo = EventBooking::getEventsCapacityInfo($eventConfig->eventInfo, $eventIds);

    $days = [];
    $bookings = [];

    // Remove item from meta if missing or at capacity

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($eventConfig->packageInfos as $pKey => $packageInfo) {
      foreach ($packageInfo->meta as $key => $meta) {
        if (!array_key_exists($meta->userid, $publicNames)) {
          $publicName = $this->getPublicName($meta->userid);
          if (!is_null($publicName)) $publicNames[$meta->userid] = $publicName;
        }

        if (
          !array_key_exists($meta->eventId, $capacityInfo) ||
          $capacityInfo[$meta->eventId]->total_registrants >= $capacityInfo[$meta->eventId]->event_capacity
        ) {
          if ($this->userId != 0 && $meta->userid == $this->userId) {
            $registrants = Registrants::byEventId($meta->eventId);
            if (count($registrants)) {

              /** @var \ClawCorpLib\Lib\Registrant **/
              $r = $registrants[0];
              $records = $r->records(true);
              reset($records);
              /** @var \ClawCorpLib\Lib\RegistrantRecord **/
              $record = current($records);

              $bookings[$meta->eventId] = [
                'title' => $packageInfo->title,
                'fname' => $record->registrant->first_name,
                'email' => $record->registrant->email,
              ];
            }
          }
          unset($packageInfo->meta->$key);
        }
      }

      if (count((array)($packageInfo->meta)) == 0) {
        ($eventConfig->packageInfos)->offsetUnset($pKey);
      }

      if ($eventConfig->packageInfos->offsetExists($pKey)) {
        $day = $eventConfig->packageInfos[$pKey]->start->format('D');
        if (!array_key_exists($day, $days)) {
          $days[$day] = 0;
        }
        $days[$day]++;
      }
    }

    //var_dump($publicNames);
    //dd($bookings);
    return [$eventConfig, $publicNames, $days, $bookings];
  }

  public function getPublicName($userId): ?string
  {
    $user = $this->userFactory->loadUserById($userId);
    if (is_null($user)) return null;

    return Helpers::getUserField($userId, $this->publicNameFieldId) ?? htmlspecialchars($user->name);
  }
}
