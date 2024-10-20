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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\CMS\Factory;
use Joomla\Component\Fields\Administrator\Model\FieldsModel;
use Joomla\Component\Privacy\Administrator\Export\Field;

\defined('_JEXEC') or die;

/**
 * Helper for mod_claw_spaschedule
 */
class SpascheduleHelper implements DatabaseAwareInterface
{
  use DatabaseAwareTrait;
  public \Joomla\CMS\User\UserFactoryInterface $userFactory;
  public int $publicNameFieldId = 0;

  public function __construct()
  {
    $this->userFactory = Factory::getContainer()->get(UserFactoryInterface::class);

    $params = ComponentHelper::getParams('com_claw');
    $this->publicNameFieldId = $params->get('public_name_field', 0);
  }

  public function loadSchedule(): array
  {
    $alias = Aliases::current(true);

    $eventConfig = new EventConfig($alias, [PackageInfoTypes::spa]);

    // Find full events and remove from $packageInfos
    $eventIds = [];
    $publicNames = [];

    /*
     * {"meta0":{"userid":"123","services":["therapeutic","sensual"],"eventId":188},"meta1":{"userid":"124","services":["sensual","tie"],"eventId":189}}
     */

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($eventConfig->packageInfos as $packageInfo) {
      foreach ($packageInfo->meta as $meta) {
        $eventIds[] = $meta->eventId;
      }
    }

    $capacityInfo = EventBooking::getEventsCapacityInfo($eventConfig->eventInfo, $eventIds);

    $days = [];

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
          unset($packageInfo->meta->$key);
        }
      }

      if (count((array)$packageInfo->meta) == 0) {
        unset($eventConfig->packageInfos[$pKey]);
      }

      if (!is_null($eventConfig->packageInfos[$pKey])) {
        $day = $eventConfig->packageInfos[$pKey]->start->format('D');
        if (!array_key_exists($day, $days)) {
          $days[$day] = 0;
        }
        $days[$day]++;
      }
    }

    return [$eventConfig, $publicNames, $days];
  }

  public function getPublicName($userId): ?string
  {
    $user = $this->userFactory->loadUserById($userId);
    if (is_null($user)) return null;

    $result = htmlspecialchars($user->name);

    if ($this->publicNameFieldId != 0) {
      $fields = FieldsHelper::getFields('com_users.user', ['id' => $user->id], true);
      foreach ($fields as $field) {
        if ($field->id == $this->publicNameFieldId) {
          if (!empty($field->value)) $result = $field->value;
        }
      }
    }

    return $result;
  }
}
